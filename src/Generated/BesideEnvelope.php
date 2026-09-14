<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: be01322365767b4f7a75b07dd99785140086c371, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `beside` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{ports: list<array{from: int, service: string, to: int}>, refusal?: string|null, stance: 'unchanged'|'pending'|'blocked'|'applied', written?: string|null}
 */
final class BesideEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Beside;

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
