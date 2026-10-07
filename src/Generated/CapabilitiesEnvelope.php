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
 * The `capabilities` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Capabilities array{capabilities: array<string, CapabilityState>}
 * @phpstan-type CapabilityState 'available'|'unconfigured'|'unpermitted'
 * @phpstan-type Data Capabilities
 */
final class CapabilitiesEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Capabilities;

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
