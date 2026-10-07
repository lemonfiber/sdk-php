<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\PromiseInterface;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\CertificateWasRefused;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\Problem;
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;
use Lemonfiber\Sdk\Time\Clock;
use Lemonfiber\Sdk\Time\Duration;
use Lemonfiber\Sdk\Time\SystemClock;
use Override;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

/**
 * The transport: one address, the pin the address arrived with, and a token
 * where there is one.
 *
 * Every request leaves through {@see StackSender}, which sends it to this
 * address and nowhere else, holds it to the pin where there is one, and follows
 * no answer onward, whatever the request or anything that shaped it asked for.
 *
 * **The token is optional, for exactly one route.** `/api/session` is the only
 * door on the surface that answers a request carrying no token — a caller with
 * a password and nothing else is who it is for — so a connector that insisted
 * on one could not reach it. Every other request goes through
 * {@see \Lemonfiber\Sdk\Client}, which has no constructor that omits it.
 *
 * Null rather than an empty token, because the two are not the same thing on
 * the wire: an absent header is what the door expects, and a header carrying
 * nothing is a token that fails comparison.
 *
 * **Every call waits at most the wait it was given.** The wait is required:
 * a connector that fell back to its transport's own bound would wait thirty
 * seconds for an answer, three times over for a read asked again. One call is
 * every attempt at one request, so the attempts share the wait, each is given
 * what is left of it, and an attempt the pause before it would not leave room
 * for is not made.
 */
final class LemonfiberConnector extends Connector
{
    /**
     * How long before a request that may be asked again is asked again, in
     * milliseconds; each retry after the first waits twice as long. Only a
     * read may be asked again, and that is the read's to decide.
     */
    public const int FIRST_PAUSE_MS = 250;

    #[Override]
    public ?int $retryInterval;

    /**
     * @param  Duration|null  $firstPause  how long before a read is first asked again; {@see FIRST_PAUSE_MS} where none is given
     */
    public function __construct(
        private readonly BaseUrl $baseUrl,
        private readonly Duration $wait,
        private readonly ?RunToken $token = null,
        private readonly Clock $clock = new SystemClock(),
        ?Duration $firstPause = null,
    ) {
        $this->retryInterval = $firstPause->milliseconds ?? self::FIRST_PAUSE_MS;
    }

    /**
     * Send a request, and raise {@see Unreachable} where nothing answered it.
     *
     * Every request this package makes passes through here, so a connection that
     * could not be made, or broke before an answer arrived, reaches a caller as one
     * of this package's problems whichever door it was sent through. Saloon raises
     * its own exception for a connection it could not make, and hands on the HTTP
     * library's for one that broke before an answer; both are caught. An answer of
     * any status is still a response; only its absence is raised here.
     *
     * A pinned address whose peer presented a certificate the pin does not name
     * raises {@see CertificateWasRefused} instead: something answered, and it was
     * not the machine the pin was taken from.
     *
     * A request addressed anywhere but this address raises
     * {@see ConfigurationProblem} and is not sent.
     *
     * @param  callable(Throwable, Request): bool|null  $handleRetry
     *
     * @throws CertificateWasRefused
     * @throws ConfigurationProblem
     * @throws Unreachable
     */
    #[Override]
    public function send(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null): Response
    {
        $call = Call::on($request, $this->wait, $this->clock);
        $firstPause = $request->retryInterval ?? $this->retryInterval ?? self::FIRST_PAUSE_MS;

        // Reached only where the request itself would ask again, so this decides
        // nothing about which failures are worth another attempt; only whether
        // there is time for one.
        $askAgain = static fn(Throwable $failed, Request $asked): bool => ($handleRetry === null || $handleRetry($failed, $asked))
            && $call->hasRoomToAskAgain($firstPause);

        try {
            return parent::send($request, $mockClient, $askAgain);
        } catch (FatalRequestException|TransferException $nothingAnswered) {
            throw $this->whyNothingAnswered($request->resolveEndpoint(), $nothingAnswered, $call);
        }
    }

    /**
     * Send a request later, as one call with the wait every call is given.
     */
    #[Override]
    public function sendAsync(Request $request, ?MockClient $mockClient = null): PromiseInterface
    {
        Call::on($request, $this->wait, $this->clock);

        return parent::sendAsync($request, $mockClient);
    }

    /**
     * The answer to a request, read as the request reads it, or the refusal it
     * arrived as instead.
     *
     * @template T
     *
     * @param  Request&ReadsItsAnswer<T>  $request
     * @return T
     *
     * @throws CertificateWasRefused
     * @throws ConfigurationProblem
     * @throws Problem
     * @throws RequestFailed
     * @throws Unreachable
     */
    public function answer(Request&ReadsItsAnswer $request): mixed
    {
        $response = $this->send($request);

        if ($response->failed()) {
            throw RequestFailed::from($request->resolveEndpoint(), $response->status(), $response->body());
        }

        return $request->createDtoFromResponse($response);
    }

    /**
     * The address this connector sends to, with the pin it holds that address to.
     */
    public function baseUrl(): BaseUrl
    {
        return $this->baseUrl;
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl->toString();
    }

    protected function defaultAuth(): ?Authenticator
    {
        return $this->token;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return ['Accept' => Api::JSON_MEDIA_TYPE];
    }

    /**
     * The sender every request leaves through, which holds it to this address and its pin.
     */
    protected function defaultSender(): Sender
    {
        return new StackSender($this->baseUrl);
    }

    /**
     * A peer that presented a certificate other than the pinned one, or silence.
     *
     * Only a pinned address is asked what it presented, and a peer that presented
     * the pinned certificate, or presented none, is silence like any other.
     */
    private function whyNothingAnswered(string $endpoint, Throwable $nothingAnswered, Call $call): CertificateWasRefused|Unreachable
    {
        $pin = $this->baseUrl->pin();
        $unreachable = Unreachable::whenAsking($endpoint, $nothingAnswered->getMessage(), WhatTheTransportReported::in($nothingAnswered));

        if (! $pin instanceof CertificatePin) {
            return $unreachable;
        }

        $presented = PresentedCertificate::at($this->baseUrl, $call->secondsLeft());

        if ($presented === null || $presented === $pin->toString()) {
            return $unreachable;
        }

        return CertificateWasRefused::whenAsking($endpoint, $presented, $pin->toString());
    }
}
