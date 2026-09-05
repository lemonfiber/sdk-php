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
 * The `clients` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{devices: list<array{caution?: string|null, client: string, device: string, instead?: string|null, support: 'good'|'workable'|'poor'|'fallback'}>, nothing_is_installed: string, only_at_home: string, straining?: array{caution: string, instead: string, preset: string}|null, trouble: list<array{causes: list<array{because: string, fix: string, tell: string}>, symptom: string}>}
 */
final class ClientsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Clients;

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
