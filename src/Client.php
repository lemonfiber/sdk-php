<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk;

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
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\ActRequest;
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
use Saloon\Http\Faking\MockClient;

/**
 * The client: reads, actions and live updates against one running lemonfiber.
 */
final readonly class Client
{
    private const int DEFAULT_RECONNECT_LIMIT = 5;

    private const int DEFAULT_WAIT_MILLISECONDS = 250;

    public function __construct(
        private LemonfiberConnector $connector,
        private EnvelopeReader $reader = new EnvelopeReader(),
    ) {}

    /**
     * The stack on this machine. Every call waits at most `$wait` for its answer, every attempt at it included.
     *
     * @throws ConfigurationProblem
     */
    public static function onPort(int $port, string $token, Duration $wait): self
    {
        return new self(new LemonfiberConnector(BaseUrl::onPort($port), $wait, RunToken::fromString($token)));
    }

    /**
     * A stack at this address. Every call waits at most `$wait` for its answer, every attempt at it included.
     *
     * @throws ConfigurationProblem
     */
    public static function at(string $address, string $token, Duration $wait): self
    {
        return new self(new LemonfiberConnector(BaseUrl::fromString($address), $wait, RunToken::fromString($token)));
    }

    /**
     * A stack reached anywhere, held to the one certificate whose digest pairing material carried.
     *
     * Every call waits at most `$wait` for its answer, every attempt at it included.
     *
     * @throws ConfigurationProblem
     */
    public static function pinnedAt(string $address, string $token, string $certificateDigest, Duration $wait): self
    {
        return new self(new LemonfiberConnector(
            BaseUrl::pinned($address, CertificatePin::fromSha256($certificateDigest)),
            $wait,
            RunToken::fromString($token),
        ));
    }

    /**
     * A list is the parameter repeated once for each value; an empty list or a null sends nothing.
     *
     * @param  array<string, scalar|list<scalar>|null>  $query
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function read(string $endpoint, array $query = []): Envelope
    {
        return $this->connector->answer(ReadRequest::envelope($endpoint, $query));
    }

    /**
     * Ask a stack to change something, as one attempt at it.
     *
     * Each action lemonfiber offers is a generated {@see ActionRequest}
     * taking its arguments by name.
     *
     * The key names that attempt; a caller acting again, or acting after the
     * connection came back, mints a new one. The action is sent once, key or no
     * key. A key is given per call and this client keeps none of them, so there
     * is no value here for a later request to pick up.
     *
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws ConfigurationProblem
     * @throws RequestFailed
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function act(ActionRequest $action, ?string $idempotencyKey = null): Envelope
    {
        $attempt = $idempotencyKey === null ? null : IdempotencyKey::fromString($idempotencyKey);

        return $this->connector->answer(new ActRequest($action, $attempt));
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
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function repair(Repair $asked, ?string $idempotencyKey = null): Envelope
    {
        return $this->act($asked->request(), $idempotencyKey);
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
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function whatBecameOf(string $job): JobStanding
    {
        return $this->connector->answer(ReadRequest::job($job));
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
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function letGoOf(string $job): JobStanding
    {
        return $this->connector->answer(new ReleaseRequest($job));
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
     * @throws Unreachable
     * @throws UnreadableResponse
     */
    public function logs(Logs $asked): LogWindow
    {
        return $this->connector->answer(ReadRequest::logs($asked));
    }

    /**
     * One support bundle this run wrote, fetched whole.
     *
     * The one read with a method of its own here whose answer is a file rather
     * than a document: lemonfiber hands the archive over as it is, so what comes
     * back is its bytes beside what the transport said about them, and nothing
     * here opens it. The name is the last segment of the `path` the `support`
     * action answered with once the bundle was written.
     *
     * @throws RequestFailed
     * @throws Unreachable
     */
    public function bundle(string $name): BundleFile
    {
        return $this->connector->answer(ReadRequest::bundle($name));
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
     * Where this client sends, and the certificate it holds that address to.
     */
    public function baseUrl(): BaseUrl
    {
        return $this->connector->baseUrl();
    }

    /**
     * Answer every request from the mock, so none reaches a stack at all.
     */
    public function withMockClient(MockClient $mock): self
    {
        $this->connector->withMockClient($mock);

        return $this;
    }
}
