<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `playing` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Medium from Shapes
 * @phpstan-type Playback array{device: string, episode?: int|null, medium: Medium, member: string, member_id: string, paused: bool, season?: int|null, series?: string|null, title: string}
 * @phpstan-type PlayingReport array{available: bool, findings: list<string>, member: string, sessions: list<Playback>}
 * @phpstan-type Data PlayingReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
