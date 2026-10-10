<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 640f134910d8eecc146aa1c2531c4c7d2b0f177f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `invitation` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Unrated from Shapes
 * @phpstan-type Invitation array{address: string, applied?: InvitationApplied|null, caution?: string|null, decline?: string|null, hours: int, join?: string|null, linked: Linked, name: string, rehearsed: bool, standing: InvitationStanding, suspended: list<string>, unjoinable?: string|null, withdrawn: list<string>}
 * @phpstan-type InvitationApplied array{filtering: string, libraries: list<string>, limit?: string|null, requesting: Linked, unrated: Unrated}
 * @phpstan-type InvitationStanding 'made'|'waiting'|'joined'|'reset'
 * @phpstan-type Linked 'made'|'not-yet'|'not-tried'
 * @phpstan-type Data Invitation
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
