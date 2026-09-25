<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

use RuntimeException;

use function sprintf;

/**
 * Something answered at a pinned address with a certificate other than the pinned one.
 *
 * Told apart from {@see Unreachable}, which is no answer at all. Here a peer
 * was there and presented a certificate, and it was not the one the pin names,
 * so the connection was refused during the handshake and nothing was written
 * to it. Either another machine answers at that address, or something stands
 * between this client and the machine the pin was taken from.
 *
 * It carries the digest the peer presented and the one it was pinned to. Both
 * are digests of certificates, which are public; neither is a secret.
 */
final class CertificateWasRefused extends RuntimeException implements Problem
{
    private function __construct(
        private readonly string $endpoint,
        private readonly string $presented,
        private readonly string $pinned,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * The request for an endpoint, met by a peer presenting a certificate the pin does not name.
     */
    public static function whenAsking(string $endpoint, string $presented, string $pinned): self
    {
        return new self($endpoint, $presented, $pinned, sprintf(
            'Something answered the request for %s with a certificate other than the one this client is held to, so nothing was sent to it. It is not the machine the pin was taken from: another machine answers at that address, or something stands between the two.',
            $endpoint,
        ));
    }

    /**
     * The endpoint that was asked.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The SHA-256 of the certificate the peer presented, as a pin is written.
     */
    public function presented(): string
    {
        return $this->presented;
    }

    /**
     * The SHA-256 this client is held to.
     */
    public function pinned(): string
    {
        return $this->pinned;
    }
}
