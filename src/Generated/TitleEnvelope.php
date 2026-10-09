<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ebe81f0581db0d09c3025f7f745d5fa71883085a, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `title` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Medium from Shapes
 * @phpstan-import-type Pinned from Shapes
 * @phpstan-type Episode array{backdrop?: string|null, door?: Pinned|null, id: string, medium: Medium, minutes?: int|null, number?: int|null, overview?: string|null, poster?: string|null, stream_from?: string|null, title: string, unlocated?: string|null, year?: int|null}
 * @phpstan-type Season array{episodes: list<Episode>, id: string, name: string, number?: int|null}
 * @phpstan-type Title array{backdrop?: string|null, certificate?: string|null, door?: Pinned|null, genres: list<string>, id: string, medium: Medium, minutes?: int|null, overview?: string|null, poster?: string|null, released?: string|null, seasons: list<Season>, stream_from?: string|null, title: string, unlocated?: string|null, year?: int|null}
 * @phpstan-type TitleReport array{id: string, member: string, rehearsed: bool, title?: Title|null}
 * @phpstan-type Data TitleReport
 */
final class TitleEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Title;

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
