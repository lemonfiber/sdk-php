<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 599816ca1d30c3c9a9dbe014e6350f1d2eaa7bd1, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `space` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{agreement: string, candidates: list<array{bytes: int, consequence?: string|null, name: string, standing: array{standing: 'never_imported'}|array{ratio: int, standing: 'seeding'}|array{standing: 'left_alone'}}>, consumption: list<array{category: array{name: string, of: 'tree'}|array{of: 'landing'}|array{of: 'seeding'}|array{of: 'orphaned'}|array{of: 'extracted'}|array{of: 'services'}|array{of: 'unmanaged'}, reclaim: 'by_losing_content'|'in_progress'|'at_the_cost_of_ratio'|'the_easy_win'|'already_have_it'|'marginally'|'you_said_not', tally: array{files: int, logical: int, physical: int, shared: int}}>, halted: bool, interrupted: list<array{name: string, partial: int, said: string}>, level: 'unknown'|'ample'|'advisory'|'warning'|'critical'|'exhausted', outsized: list<array{bytes: int, path: string, times_typical: int}>, reclaimable: list<array{category: array{name: string, of: 'tree'}|array{of: 'landing'}|array{of: 'seeding'}|array{of: 'orphaned'}|array{of: 'extracted'}|array{of: 'services'}|array{of: 'unmanaged'}, reclaim: 'by_losing_content'|'in_progress'|'at_the_cost_of_ratio'|'the_easy_win'|'already_have_it'|'marginally'|'you_said_not', tally: array{files: int, logical: int, physical: int, shared: int}}>, reclaimed?: array{bytes: int, gone: list<string>, left: list<array{at: string, why: string}>}|null, volumes: list<array{at: string, committed: int, free?: int|null, level: 'unknown'|'ample'|'advisory'|'warning'|'critical'|'exhausted', limit?: int|null, point: string, projected?: int|null, reading: array{as: 'live'}|array{as: 'as_of', at: int}, role: 'data'|'services'}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
