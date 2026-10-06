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
 * The `keys` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{keys: list<array{member_minted: bool, minted: string, name: string, purpose: 'home-assistant'|'mcp'|'other', revoked?: string|null, scope: string, state: 'active'|'revoked'|'orphaned'|'unconfirmed', used?: string|null}>, purposes: string, rehearsed: bool, revoked?: string|null}
 */
final class KeysEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Keys;

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
