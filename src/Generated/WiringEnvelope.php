<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 7d4d153a396e03d6c866440f97f9b61e6584c952, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `wiring` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{unfilled: list<array{by: string, capability: string}>, wired: list<array{by: string, reaches: array{capability: string, how: 'asked', services: list<string>, settled: array{settled: 'outright'}|array{settled: 'each'}|array{claimants: list<string>, settled: 'contested'}|array{over: list<string>, settled: 'chosen', whose: 'stack'|'operator', why?: string|null}|array{settled: 'unfilled'}}|array{how: 'by-name', service: string, why: string}}>}
 */
final class WiringEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Wiring;

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
