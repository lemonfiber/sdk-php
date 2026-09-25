<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 844c3a814eda21947270c155943221a2e08e1561, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `invitation` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{address: string, applied?: array{filtering: string, libraries: list<string>, limit?: string|null, requesting: 'made'|'not-yet'|'not-tried', unrated: 'held-back'|'let-through'}|null, caution?: string|null, hours: int, linked: 'made'|'not-yet'|'not-tried', name: string, rehearsed: bool, standing: 'made'|'waiting'|'joined'|'reset', withdrawn: list<string>}
 */
final class InvitationEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Invitation;

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
