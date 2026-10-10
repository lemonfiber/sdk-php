<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `held` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Medium from Shapes
 * @phpstan-import-type Pinned from Shapes
 * @phpstan-type Held array{backdrop?: string|null, door?: Pinned|null, id: string, medium: Medium, poster?: string|null, stream_from?: string|null, title: string, unlocated?: string|null, year?: int|null}
 * @phpstan-type HeldReport array{available: bool, findings: list<string>, holdings: list<Held>, id: string, member: string, rehearsed: bool}
 * @phpstan-type Data HeldReport
 */
final class HeldEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Held;

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
