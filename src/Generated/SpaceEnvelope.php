<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `space` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Candidate from Shapes
 * @phpstan-type Consumption array{category: SpaceCategory, reclaim: Reclaim, tally: Tally}
 * @phpstan-type Freshness array{as: 'live'}|array{as: 'as_of', at: int}
 * @phpstan-type Interrupted array{name: string, partial: int, said: string}
 * @phpstan-type Level 'unknown'|'ample'|'advisory'|'warning'|'critical'|'exhausted'
 * @phpstan-type Outsized array{bytes: int, path: string, times_typical: int}
 * @phpstan-type Reckoning array{agreement: string, candidates: list<Candidate>, consumption: list<Consumption>, halted: bool, interrupted: list<Interrupted>, level: Level, outsized: list<Outsized>, reclaimable: list<Consumption>, reclaimed?: Reclaimed|null, rehearsed: bool, volumes: list<Volume>}
 * @phpstan-type Reclaim 'by_losing_content'|'in_progress'|'at_the_cost_of_ratio'|'the_easy_win'|'already_have_it'|'marginally'|'you_said_not'
 * @phpstan-type Reclaimed array{bytes: int, gone: list<string>, left: list<SpaceLeft>, rehearsed: bool}
 * @phpstan-type Role 'data'|'services'
 * @phpstan-type SpaceCategory array{name: string, of: 'tree'}|array{of: 'landing'}|array{of: 'seeding'}|array{of: 'orphaned'}|array{of: 'extracted'}|array{of: 'services'}|array{of: 'unmanaged'}
 * @phpstan-type SpaceLeft array{at: string, why: string}
 * @phpstan-type Tally array{files: int, logical: int, physical: int, shared: int}
 * @phpstan-type Volume array{at: string, committed: int, free?: int|null, level: Level, limit?: int|null, point: string, projected?: int|null, reading: Freshness, role: Role}
 * @phpstan-type Data Reckoning
 */
final class SpaceEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Space;

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
