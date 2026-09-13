<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use GuzzleHttp\Handler\StreamHandler;
use Lemonfiber\Sdk\Contract\Api;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Sender;
use Saloon\Http\Connector;
use Saloon\Http\Senders\GuzzleSender;

/**
 * The transport: one address, the pin the address arrived with, and a token
 * where there is one.
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
     * The peer check, where the address carries a pin.
     *
     * The digest is compared while the connection is being set up, so a peer that does
     * not match is dropped before a request carrying the token is written to it. It
     * decides the peer's identity in place of the platform trust store, which holds no
     * opinion about a certificate a stack signed itself.
     *
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        $pin = $this->baseUrl->pin();

        if (!$pin instanceof CertificatePin) {
            return [];
        }

        return [
            'verify' => false,
            'stream_context' => ['ssl' => ['peer_fingerprint' => ['sha256' => $pin->toString()]]],
        ];
    }

    /**
     * The handler that honours the peer check where the address carries a pin.
     *
     * The check is read by the stream handler and by no other, so a pinned address sent
     * through the handler chosen by default would travel unpinned. The sender is built
     * here rather than taken from the default, which a caller may have replaced.
     */
    protected function defaultSender(): Sender
    {
        if (!$this->baseUrl->pin() instanceof CertificatePin) {
            return parent::defaultSender();
        }

        $sender = new GuzzleSender();
        $sender->getHandlerStack()->setHandler(new StreamHandler());

        return $sender;
    }
}
