<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: e6cd57b374cee20ba03feb8f18df4b1743056976, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `outbound` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{ours: list<array{allowed: bool, cost: string, destination: list<string>, purpose: string, reach: 'registry'|'guides'|'echo'|'indexer'|'usenet'|'household', sends: string, switch: string}>, theirs: list<array{destination: string, purpose: string, service: string}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
