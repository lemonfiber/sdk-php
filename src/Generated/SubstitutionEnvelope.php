<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 9fd0586162913789261e9b17399655655f1de60a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `substitution` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{applied: bool, substitution: array{asked_by: list<string>, capability: string, leaves_unfilled: list<array{by: string, capability: string}>, now: string, setting: string, was?: string|null}}
 */
final class SubstitutionEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Substitution;

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
