<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2a1472c24a6e4bb8d5ff70a792d61cc4b61e26b8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `quality` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Disposition from Shapes
 * @phpstan-import-type MusicChoice from Shapes
 * @phpstan-import-type StackEdit from Shapes
 * @phpstan-type PresetChoice array{means: string, needs_transcoding_here: bool, preset: string, resolution: string, scope: string, size_per_hour: string, transcoding: string}
 * @phpstan-type QualityReport array{choices: list<PresetChoice>, customised: bool, disposition: Disposition, music?: MusicChoice|null, overwritten?: StackEdit|null, rehearsed: bool}
 * @phpstan-type Data QualityReport
 */
final class QualityEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Quality;

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
