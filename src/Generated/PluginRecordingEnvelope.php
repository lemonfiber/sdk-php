<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2a22ca0bbdb5f45403cd5c01012e917bc23bbdc4, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `plugin-recording` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Violation from Shapes
 * @phpstan-type Failed array{log: list<string>, service: string, step: string, why: string}
 * @phpstan-type Recorded array{failed: list<Failed>, holds: bool, id: string, recordings: list<RecordingTaken>, refusals: list<Violation>}
 * @phpstan-type RecordingTaken array{fixture?: string|null, named: string, took: Took}
 * @phpstan-type Took array{outcome: 'written'}|array{outcome: 'unnoted'}|array{outcome: 'reproduced'}|array{outcome: 'unreproduced', why: string}|array{outcome: 'overran', seconds: int}|array{outcome: 'unrecorded', why: string}|array{outcome: 'removed'}|array{outcome: 'unasked'}|array{host: string, outcome: 'left'}
 * @phpstan-type Data Recorded
 */
final class PluginRecordingEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::PluginRecording;

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
