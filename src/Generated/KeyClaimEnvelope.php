<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2fdb17698fa8603b4fd6649e0109bd01705f8b3b, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `key-claim` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type KeyPurpose from Shapes
 * @phpstan-type KeyClaim array{lapses: string, link: string, name: string, purpose: KeyPurpose, scope: string}
 * @phpstan-type Data KeyClaim
 */
final class KeyClaimEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::KeyClaim;

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
