<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 5911b3a5523129f9c5d09e04d1c60e5adc32edd2, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `seed` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{assessment: 'assessed'|'unassessable', wirings: list<array{connection: string, severity: array{severity: 'informational'}|array{breakage: string, remediation: string, severity: 'warning'}, state: array{state: 'wired'}|array{state: 'already-wired'}|array{state: 'drifted'}|array{state: 'stale'}|array{ours: string, state: 'conflicted', yours?: string|null}|array{state: 'adopted'}|array{state: 'unmanaged'}|array{reason: string, state: 'skipped'}|array{detail: string, state: 'failed'}|array{reason: string, state: 'refused'}}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
