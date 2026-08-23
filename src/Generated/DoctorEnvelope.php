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
 * The `doctor` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{findings: list<array{category: 'environment'|'storage'|'network'|'vpn'|'credentials'|'services'|'providers'|'queue'|'config', caused_by?: string|null, check: string, said?: string|null, service?: string|null, title: string, verdict: array{note?: string|null, outcome: 'pass'}|array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{outcome: 'unverified', reason: string, remedy: array{action: string, detail?: string|null}}|array{outcome: 'skipped', reason: string}}>, overall: 'healthy'|'degraded'|'broken'|'unknown'}
 */
final class DoctorEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Doctor;

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
