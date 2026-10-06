<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: a20047c4c38de3189f6aecd3c61dd26c9fff6646, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `repair` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{acted: bool, agreement: string, beyond: list<array{check: string, remedy: array{action: string, detail?: string|null}}>, mended: list<array{outcome: array{outcome: 'fixed'}|array{outcome: 'fix_failed'}|array{leaving: string, outcome: 'stopped'}|array{outcome: 'declined'}|array{outcome: 'would_overwrite'}|array{outcome: 'unmanaged'}, repair: array{check: string, does: string, effects: list<string>, reversible: bool}}>, offered: list<array{check: string, does: string, effects: list<string>, reversible: bool}>, rehearsed: bool}
 */
final class RepairEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Repair;

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
