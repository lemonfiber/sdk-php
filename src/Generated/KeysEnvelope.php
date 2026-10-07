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
 * The `keys` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type KeyPurpose from Shapes
 * @phpstan-type KeyListing array{keys: list<ListedKey>, purposes: string, rehearsed: bool, revoked?: string|null}
 * @phpstan-type KeyState 'active'|'revoked'|'orphaned'|'unconfirmed'
 * @phpstan-type ListedKey array{member_minted: bool, minted: string, name: string, purpose: KeyPurpose, revoked?: string|null, scope: string, state: KeyState, used?: string|null}
 * @phpstan-type Data KeyListing
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
