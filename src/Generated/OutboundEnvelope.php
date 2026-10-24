<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: b79526cbfe6f7083080f172277706ccc61d392b8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `outbound` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type ValueOrigin from Shapes
 * @phpstan-type Elsewhere array{destination: string, origin: ValueOrigin, purpose: string, recorded: bool, service: string}
 * @phpstan-type Leaving array{on_behalf: list<OnBehalf>, ours: list<Outbound>, theirs: list<Elsewhere>}
 * @phpstan-type OnBehalf array{destination: string, plugin: string, purpose: string}
 * @phpstan-type Outbound array{allowed: bool, cost: string, destination: list<string>, purpose: string, reach: OutboundReach, sends: string, switch: string}
 * @phpstan-type OutboundReach 'registry'|'guides'|'echo'|'indexer'|'usenet'|'household'|'updates'|'plugin-source'|'catalogue'|'posters'
 * @phpstan-type Data Leaving
 */
final class OutboundEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Outbound;

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
