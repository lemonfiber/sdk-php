<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 53eb6d48a3f5e00735d516e7240932a35bb7a5e0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `stuck` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Stage from Shapes
 * @phpstan-type StuckEntry array{service: string, stage: Stage, title: string}
 * @phpstan-type StuckReport array{incomplete: bool, items: list<StuckEntry>}
 * @phpstan-type Data StuckReport
 */
final class StuckEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Stuck;

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
