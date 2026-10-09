<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `seed` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type UnsupportedReport from Shapes
 * @phpstan-type Assessment 'assessed'|'unassessable'
 * @phpstan-type SeedReport array{assessment: Assessment, rehearsed: bool, unsupported?: list<UnsupportedReport>, wirings: list<Wiring>}
 * @phpstan-type SeedSeverity array{severity: 'informational'}|array{breakage: string, remediation: string, severity: 'warning'}
 * @phpstan-type SeedState array{state: 'wired'}|array{state: 'already-wired'}|array{state: 'drifted'}|array{state: 'stale'}|array{ours: string, state: 'conflicted', yours?: string|null}|array{state: 'adopted'}|array{state: 'unmanaged'}|array{ours?: string|null, state: 'would-wire', yours?: string|null}|array{state: 'would-adopt'}|array{reason: string, state: 'observed'}|array{reason: string, state: 'unmatched'}|array{reason: string, state: 'skipped'}|array{detail: string, state: 'failed'}|array{reason: string, state: 'refused'}
 * @phpstan-type Wiring array{connection: string, severity: SeedSeverity, state: SeedState}
 * @phpstan-type Data SeedReport
 */
final class SeedEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Seed;

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
