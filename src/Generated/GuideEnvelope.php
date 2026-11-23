<?php

// Generated from the contract vendored under contract/. Do not edit.
// Contract::SOURCE names its revision and Contract::API_VERSION its wire version.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `guide` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Instant from Shapes
 * @phpstan-type Airing array{certificate?: string|null, ends: Instant, starts: Instant, title: string}
 * @phpstan-type GuideReport array{channels: list<Guided>, id: string, member: string, rehearsed: bool}
 * @phpstan-type Guided array{guide_until?: Instant|null, id: string, name: string, number?: string|null, programmes: list<Airing>}
 * @phpstan-type Data GuideReport
 */
final class GuideEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Guide;

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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host, $envelope->job);
    }
}
