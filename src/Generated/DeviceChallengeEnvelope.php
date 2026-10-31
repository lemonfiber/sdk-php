<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 668dad0f9ba40b08aaf0dfbda2926b8fb5267d40, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `device-challenge` envelope, shaped as the contract describes it.
 *
 * @phpstan-type DeviceChallenge array{challenge: string, expires: string}
 * @phpstan-type Data DeviceChallenge
 */
final class DeviceChallengeEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::DeviceChallenge;

    /**
     * The same envelope with its payload typed by its kind.
     *
     * @param  Envelope<mixed>  $envelope
     * @return Envelope<Data>
     *
     * @throws UnexpectedKind
     */
    public static function in(Envelope $envelope): Envelope
    {
        /** @var Data $data */
        $data = Payload::under(self::KIND, $envelope);

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
