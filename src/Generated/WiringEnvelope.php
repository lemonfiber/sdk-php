<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: e0d5ee48e087ddd488e3bbade0614c2bdb8df72f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `wiring` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Unfilled from Shapes
 * @phpstan-import-type Wired from Shapes
 * @phpstan-type WiringReport array{unfilled: list<Unfilled>, wired: list<Wired>}
 * @phpstan-type Data WiringReport
 */
final class WiringEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Wiring;

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
