<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `trace` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Stage from Shapes
 * @phpstan-type Coverage array{have: int, seasons: list<SeasonCoverage>, unmonitored: int, wanted: int}
 * @phpstan-type Part array{number: int, season: int, stage: Stage, title: string}
 * @phpstan-type SeasonCoverage array{have: int, outstanding: list<Part>, season: int, unmonitored: int, wanted: int}
 * @phpstan-type TraceConfidence 'certain'|'uncertain'
 * @phpstan-type TraceMoment array{at: string, outcome: TraceOutcome}
 * @phpstan-type TraceOutcome 'grabbed'|'download-failed'|'imported'|'removed'
 * @phpstan-type TraceReport array{confidence: TraceConfidence, coverage?: Coverage|null, findings: list<string>, furthest: Stage, history: list<TraceMoment>, item: string, matched: bool, stages: list<TraceStage>, stall?: string|null}
 * @phpstan-type TraceStage array{at?: string|null, service: string, stage: Stage}
 * @phpstan-type Data TraceReport
 */
final class TraceEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Trace;

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
