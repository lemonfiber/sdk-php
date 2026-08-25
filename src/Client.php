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
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\UnreadableResponse;
use Lemonfiber\Sdk\Http\ActionRequest;
use Lemonfiber\Sdk\Http\BaseUrl;
use Lemonfiber\Sdk\Http\LemonfiberConnector;
use Lemonfiber\Sdk\Http\ReadRequest;
use Lemonfiber\Sdk\Http\RunToken;
use Lemonfiber\Sdk\Http\StreamingEventSource;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;
use Saloon\Http\Request;

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
     * @param  array<string, mixed>  $body
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    public function act(string $endpoint, array $body = []): Envelope
    {
        return $this->envelopeFrom(new ActionRequest($endpoint, $body), $endpoint);
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
     * @return Envelope<mixed>
     *
     * @throws ApiVersionMismatch
     * @throws RequestFailed
     * @throws UnreadableResponse
     */
    private function envelopeFrom(Request $request, string $endpoint): Envelope
    {
        $response = $this->connector->send($request);

        if ($response->failed()) {
            throw RequestFailed::from($endpoint, $response->status(), $response->body());
        }

        return $this->reader->read($response->body());
    }
}
