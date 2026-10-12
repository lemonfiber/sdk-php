<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 92aaa5d577c67686de2760f80c67325ec997a3f8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `askable` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type MemberAsking from Shapes
 * @phpstan-import-type TitleKind from Shapes
 * @phpstan-type Askable array{id: string, kind: TitleKind, stands: Stands, title: string, year?: int|null}
 * @phpstan-type AskableReport array{approval: string, asking: MemberAsking, kinds: list<TitleKind>, member: string, next?: string|null, term: string, titles: list<Askable>}
 * @phpstan-type Stands 'here'|'partly-here'|'on-its-way'|'askable'
 * @phpstan-type Data AskableReport
 */
final class AskableEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Askable;

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
