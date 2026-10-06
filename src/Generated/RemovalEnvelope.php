<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 79bb11356f6a117d14293c49cd2d154164c5f439, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `removal` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{'asks-through-the-request-service': bool, confirmed: bool, findings: list<string>, name: string, rehearsed: bool, requests: int, revoked: 'everywhere'|'media-server-only'|'nothing'}
 */
final class RemovalEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Removal;

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
