<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Http;

use function is_string;
use function openssl_x509_fingerprint;

use OpenSSLCertificate;

use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function stream_context_create;
use function stream_context_get_params;
use function stream_socket_client;

/**
 * The certificate a peer presents, asked for after a pinned connection failed.
 *
 * An encrypted connection is set up with the peer and the certificate it
 * presents is read; nothing is written to it, so no request and no token
 * reaches a peer the pin did not vouch for. The pin itself is enforced by the
 * transport during the handshake, where a peer that fails it and a peer that is
 * not there end in the same failure; the certificate read here is what tells
 * the two apart.
 */
final readonly class PresentedCertificate
{
    private const string DIGEST = 'sha256';

    /**
     * Read the certificate whatever it is, and keep it once read.
     */
    private const array READ_ANY = ['ssl' => ['verify_peer' => false, 'capture_peer_cert' => true]];

    /**
     * The SHA-256 of the certificate presented at an address, in the form a pin
     * is written in, or nothing where no encrypted connection could be set up.
     */
    public static function at(BaseUrl $address): ?string
    {
        $connection = self::connectedTo($address);

        if ($connection === false) {
            return null;
        }

        /** @var array{ssl: array{peer_certificate: OpenSSLCertificate}} $options */
        $options = stream_context_get_params($connection)['options'];

        $digest = openssl_x509_fingerprint($options['ssl']['peer_certificate'], self::DIGEST);

        return is_string($digest) ? $digest : null;
    }

    /**
     * An encrypted connection to the address, or false where none could be set up.
     *
     * A connection that cannot be set up is reported by the platform as a
     * warning as well as by its answer. The answer is what is read, so the
     * warning is held back while the connection is attempted, and the handler
     * that was in place before is put back afterwards.
     *
     * @return resource|false
     */
    private static function connectedTo(BaseUrl $address): mixed
    {
        set_error_handler(static fn(): bool => true);

        try {
            return stream_socket_client(
                sprintf('ssl://%s', $address->authority()),
                $errorNumber,
                $error,
                null,
                STREAM_CLIENT_CONNECT,
                stream_context_create(self::READ_ANY),
            );
        } finally {
            restore_error_handler();
        }
    }
}
