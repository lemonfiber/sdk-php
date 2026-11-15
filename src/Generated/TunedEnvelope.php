<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 6a2d9bbd6d8c0b7a09258f6352d427c89b8bc2b6, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `tuned` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Obstacle from Shapes
 * @phpstan-type TunedReport array{channel: string, direct?: string|null, fingerprint?: string|null, lapses?: int|null, member: string, rehearsed: bool, stream?: string|null, transcoded?: string|null, unlocated?: Obstacle|null}
 * @phpstan-type Data TunedReport
 */
final class TunedEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Tuned;

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
