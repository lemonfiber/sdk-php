<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: fc07885a2d4c8498362bdcaf6539f91033928867, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `channels` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Channel array{id: string, name: string, now?: Programme|null, number?: string|null}
 * @phpstan-type ChannelsReport array{channels: list<Channel>, id: string, member: string, rehearsed: bool}
 * @phpstan-type Programme array{certificate?: string|null, ends?: int|null, title: string}
 * @phpstan-type Data ChannelsReport
 */
final class ChannelsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Channels;

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
