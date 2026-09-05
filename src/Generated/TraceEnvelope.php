<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: v0.11.0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `trace` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{confidence: 'certain'|'uncertain', coverage?: array{have: int, seasons: list<array{have: int, outstanding: list<array{number: int, season: int, stage: 'not-monitored'|'monitored'|'searching'|'found'|'grabbed'|'downloading'|'downloaded'|'importing'|'imported'|'available', title: string}>, season: int, unmonitored: int, wanted: int}>, unmonitored: int, wanted: int}|null, findings: list<string>, furthest: 'not-monitored'|'monitored'|'searching'|'found'|'grabbed'|'downloading'|'downloaded'|'importing'|'imported'|'available', history: list<array{at: string, outcome: 'grabbed'|'download-failed'|'imported'|'removed'}>, item: string, matched: bool, stages: list<array{at?: string|null, service: string, stage: 'not-monitored'|'monitored'|'searching'|'found'|'grabbed'|'downloading'|'downloaded'|'importing'|'imported'|'available'}>, stall?: string|null}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
