<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 3902d3ed91cb34b34ae6ba94c660f308539115c5, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `playing` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{available: bool, findings: list<string>, member: string, sessions: list<array{device: string, episode?: int|null, medium: 'film'|'series'|'other', member: string, member_id: string, paused: bool, season?: int|null, series?: string|null, title: string}>}
 */
final class PlayingEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Playing;

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
