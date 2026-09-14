<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

use function is_string;

use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\EnvelopeReader;
use Lemonfiber\Sdk\Events\EventFeed;
use Lemonfiber\Sdk\Events\EventStream;
use Lemonfiber\Sdk\Events\HeldValues;
use Lemonfiber\Sdk\Exception\ApiVersionMismatch;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\NoSuchJob;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnexpectedKind;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\ActionRequest;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\CertificatePin;
use Lemonfiber\Sdk\Http\IdempotencyKey;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\ReleaseRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Http\StreamingEventSource;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;
use Saloon\Http\Request;
use Saloon\Http\Response;

use function str_contains;

/**
 * The client: reads, actions and live updates against one running lemonfiber.
 */
final readonly class Client
{
    private const int DEFAULT_RECONNECT_LIMIT = 5;

    private const int DEFAULT_WAIT_MILLISECONDS = 250;

    /**
     * What a name this run never handed out is answered with.
     */
    private const int NO_SUCH_NAME = 404;

    public function __construct(
        private LemonfiberConnector $connector,
        private EnvelopeReader $reader = new EnvelopeReader(),
    ) {}

    /**
     * @throws ConfigurationProblem
     */
    public static function onPort(int $port, string $token): self
    {
        return new self(new LemonfiberConnector(BaseUrl::onPort($port), RunToken::fromString($token)));
    }

    /**
     * @throws ConfigurationProblem
     */
    public static function at(string $address, string $token): self
    {
        return new self(new LemonfiberConnector(BaseUrl::fromString($address), RunToken::fromString($token)));
    }

    /**
     * A stack reached anywhere, held to the one certificate whose digest pairing material carried.
     *
     * @throws ConfigurationProblem
     */
    public static function pinnedAt(string $address, string $token, string $certificateDigest): self
    {
        return new self(new LemonfiberConnector(
            BaseUrl::pinned($address, CertificatePin::fromSha256($certificateDigest)),
            RunToken::fromString($token),
        ));
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function read(string $endpoint, array $query = []): Envelope
    {
        return $this->envelopeFrom(new ReadRequest($endpoint, $query), $endpoint);
    }

    /**
     * Ask a stack to change something, as one attempt at it.
     *
     * The key names that attempt. A caller that re-sends the same request
     * under the same key is telling the stack these are one act, so an answer
     * lost on the way back costs nothing; a caller acting again, or acting
     * after the connection came back, mints a new one and nothing earlier is
     * applied under it. A key is given per call and this client keeps none of
     * them, so there is no value here for a later request to pick up.
     *
     * @param  array<string, mixed>  $body
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function act(string $endpoint, array $body = [], ?string $idempotencyKey = null): Envelope
    {
        return $this->envelopeFrom(new ActionRequest($endpoint, $body, $this->naming($idempotencyKey)), $endpoint);
    }

    /**
     * Ask what could be put right, or carry out what was agreed to.
     *
     * The one action with a method of its own here, since it is the one whose
     * two halves are a single request read twice: a caller assembling that
     * body by hand can assemble a shape lemonfiber refuses, and {@see Repair}
     * is the shape it cannot.
     *
     * A repair reaches the services, so what comes back is a name for the work
     * rather than its outcome — the `job` envelope, with the `repair` envelope
     * arriving through it once the run is finished.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function repair(Repair $asked, ?string $idempotencyKey = null): Envelope
    {
        return $this->act($asked->endpoint(), $asked->arguments(), $idempotencyKey);
    }

    /**
     * What became of the work one name stands for.
     *
     * A name is answered with rather than an outcome wherever the work reaches
     * the services, so this is the other half of {@see self::repair()} and of
     * every other action that runs for minutes. The answer is one of three
     * standings and {@see JobStanding} is what tells them apart.
     *
     * **Redeem a name promptly.** A name does not outlive the run that minted
     * it, so one carried across a break in the connection may name nothing by
     * the time it is asked about — which arrives as {@see NoSuchJob} rather
     * than as a stack that could not be reached. Asking is a read and changes
     * nothing, which is what separates it from re-sending the action: the work
     * this asks after is work lemonfiber acknowledged, and asking again cannot
     * start a second one.
     *
     * @throws ApiVersionMismatch
     * @throws NoSuchJob
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function whatBecameOf(string $job): JobStanding
    {
        return $this->standingOf(new ReadRequest(Api::job($job)), $job);
    }

    /**
     * Let go of a name, ending the work it stands for.
     *
     * A terminal interrupts what it is running and a screen has nothing to
     * interrupt with, so the name is the handle. What was already asked of the
     * container engine goes on, exactly as it does when a terminal is closed.
     *
     * The answer is where the work now stands, which is the same answer asking
     * would have given — so a caller that released one need not ask again to
     * find out what it released. Work that had already finished answers with
     * what it finished as.
     *
     * @throws ApiVersionMismatch
     * @throws NoSuchJob
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function letGoOf(string $job): JobStanding
    {
        return $this->standingOf(new ReleaseRequest(Api::job($job)), $job);
    }

    /**
     * A look at what one service has been saying, of the size that was asked for.
     *
     * The one read with a method of its own here, since it is the one whose
     * answer is not an envelope: the scrollback comes back as a `log` envelope
     * per line, which {@see read()} would hand to a decoder expecting one
     * document and be told the answer is not readable as JSON.
     *
     * What comes back carries the size it was asked for beside the lines, so
     * whoever draws it can say which of the two it is looking at.
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnexpectedKind
     * @throws UnreadableResponse
     */
    public function logs(Logs $asked): LogWindow
    {
        $body = $this->bodyFrom(
            new ReadRequest(Api::LOGS_ENDPOINT, $asked->parameters()),
            Api::LOGS_ENDPOINT,
        );

        return LogWindow::of($asked, $this->reader->readEach($body));
    }

    /**
     * @throws ConfigurationProblem
     */
    public function events(
        Duration $heartbeat,
        ?Duration $wait = null,
        int $reconnectLimit = self::DEFAULT_RECONNECT_LIMIT,
    ): EventFeed {
        return new EventFeed(
            new EventStream($this->eventSource($wait), new SystemClock(), $heartbeat),
            $this->reader,
            new HeldValues(),
            $reconnectLimit,
        );
    }

    /**
     * The stream of live updates on its own, for a caller composing its own feed.
     *
     * @throws ConfigurationProblem
     */
    public function eventSource(?Duration $wait = null): StreamingEventSource
    {
        return new StreamingEventSource(
            $this->connector,
            $wait ?? Duration::ofMilliseconds(self::DEFAULT_WAIT_MILLISECONDS),
        );
    }

    /**
     * The transport underneath this client.
     */
    public function connector(): LemonfiberConnector
    {
        return $this->connector;
    }

    /**
     * The key a caller gave for this attempt, checked, or none.
     *
     * @throws ConfigurationProblem
     */
    private function naming(?string $idempotencyKey): ?IdempotencyKey
    {
        return $idempotencyKey === null ? null : IdempotencyKey::fromString($idempotencyKey);
    }

    /**
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    private function envelopeFrom(Request $request, string $endpoint): Envelope
    {
        return $this->reader->read($this->bodyFrom($request, $endpoint));
    }

    /**
     * The answer's text, or the refusal it arrived as instead.
     *
     * @throws RequestFailed
     */
    private function bodyFrom(Request $request, string $endpoint): string
    {
        $response = $this->connector->send($request);

        if ($response->failed()) {
            throw RequestFailed::from($endpoint, $response->status(), $response->body());
        }

        return $response->body();
    }

    /**
     * Where the work a name stands for got to, or why the name answered nothing.
     *
     * @throws ApiVersionMismatch
     * @throws NoSuchJob
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    private function standingOf(Request $request, string $job): JobStanding
    {
        $response = $this->connector->send($request);
        $status = $response->status();

        if ($status === self::NO_SUCH_NAME && $this->saidInProse($response)) {
            throw NoSuchJob::inThisRun($job);
        }

        if ($response->failed()) {
            throw RequestFailed::from($request->resolveEndpoint(), $status, $response->body());
        }

        return JobStanding::of($job, $status, $this->reader->read($response->body()));
    }

    /**
     * Whether the answer is this surface speaking in its own words.
     *
     * lemonfiber labels a sentence as prose and an envelope as JSON, so that a
     * caller parsing what it was told it was given is not handed a sentence to
     * parse as an envelope. That label is what separates the two `404`s this
     * endpoint has: a name nobody minted is said in prose, and work that
     * stopped on a problem is the `error` envelope at the status that problem
     * warrants.
     */
    private function saidInProse(Response $response): bool
    {
        /** @var array<mixed>|string|null $type */
        $type = $response->header('Content-Type');

        return ! is_string($type) || ! str_contains($type, Api::JSON_MEDIA_TYPE);
    }
}
