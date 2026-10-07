<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `substitution` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Unfilled from Shapes
 * @phpstan-type Substitution array{asked_by: list<string>, capability: string, leaves_unfilled: list<Unfilled>, now: string, setting: string, was?: string|null, why?: string|null}
 * @phpstan-type SubstitutionReport array{agreement: string, applied: bool, rehearsed: bool, substitution: Substitution}
 * @phpstan-type Data SubstitutionReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
