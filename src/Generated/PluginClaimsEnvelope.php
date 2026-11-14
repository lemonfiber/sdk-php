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
 * The `plugin-claims` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type PluginEvidence from Shapes
 * @phpstan-import-type PluginVerdict from Shapes
 * @phpstan-import-type Violation from Shapes
 * @phpstan-type Asserted array{id: string, kind: Assertion, says: string, service: string, verdict: PluginVerdict}
 * @phpstan-type Assertion 'proof'|'check'
 * @phpstan-type Claimed array{against: PluginEvidence, capabilities: list<Claiming>, checks: list<Asserted>, contributions: list<Contributed>, extension_points_version: int, id: string, installable: bool, name: string, proofs: list<Asserted>, refusals: list<Violation>, version: string, vocabulary_version: int}
 * @phpstan-type Claiming array{filling?: Filling|null, name: string, own: bool, probes: list<ProbeRan>, service: string, shown: Shown}
 * @phpstan-type Contributed array{at: string, for?: string|null, id: string, says: string}
 * @phpstan-type Filling array{answer: 'by', service: string}|array{answer: 'contested', claimants: list<string>}|array{answer: 'unfilled'}
 * @phpstan-type ProbeRan array{probe: string, verdict: PluginVerdict}
 * @phpstan-type Shown 'claimed'|'demonstrated'|'unproven'|'refuted'
 * @phpstan-type Data Claimed
 */
final class PluginClaimsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::PluginClaims;

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
