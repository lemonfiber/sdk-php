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
 * The `clients` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Cause array{because: string, fix: string, tell: string}
 * @phpstan-type Device array{caution?: string|null, client: string, deep_link?: string|null, device: string, instead?: string|null, open_source: bool, support: Support}
 * @phpstan-type Guidance array{devices: list<Device>, nothing_is_installed: string, only_at_home: string, straining?: Straining|null, trouble: list<Trouble>}
 * @phpstan-type Straining array{caution: string, instead: string, preset: string}
 * @phpstan-type Support 'good'|'workable'|'poor'|'fallback'
 * @phpstan-type Trouble array{causes: list<Cause>, symptom: string}
 * @phpstan-type Data Guidance
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
