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
 * The `adoption` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{back_up: list<string>, project?: string|null, refusal?: string|null, stance: 'unchanged'|'pending'|'blocked'|'applied', upgrades: list<array{backup_first: bool, because: string, existing: string, ours: string, refused: bool, service: string, verdict: string}>}
 */
final class AdoptionEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Adoption;

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
