<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use GuzzleHttp\Exception\TransferException;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Exception\CertificateWasRefused;
use Lemonfiber\Sdk\Exception\ConfigurationProblem;
use Lemonfiber\Sdk\Exception\Unreachable;
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
 */
final class LemonfiberConnector extends Connector
{
    public function __construct(
        private readonly BaseUrl $baseUrl,
        private readonly ?RunToken $token = null,
    ) {}

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
        try {
            return parent::send($request, $mockClient, $handleRetry);
        } catch (FatalRequestException|TransferException $nothingAnswered) {
            throw $this->whyNothingAnswered($request->resolveEndpoint(), $nothingAnswered->getMessage());
        }
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
    private function whyNothingAnswered(string $endpoint, string $reported): CertificateWasRefused|Unreachable
    {
        $pin = $this->baseUrl->pin();

        if (! $pin instanceof CertificatePin) {
            return Unreachable::whenAsking($endpoint, $reported);
        }

        $presented = PresentedCertificate::at($this->baseUrl);

        if ($presented === null || $presented === $pin->toString()) {
            return Unreachable::whenAsking($endpoint, $reported);
        }

        return CertificateWasRefused::whenAsking($endpoint, $presented, $pin->toString());
    }
}
