<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c4e98c06e6a6aab2127ae392414bfb9f088b038d, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `news-items` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type NewsKind from Shapes
 * @phpstan-import-type TitleKind from Shapes
 * @phpstan-import-type TitleUnit from Shapes
 * @phpstan-type News array{problems: list<NewsProblem>, requests: list<NewsRequest>, unread: list<NewsKind>, updates: list<NewsUpdate>}
 * @phpstan-type NewsProblem array{check: string, onset: int, summary: string}
 * @phpstan-type NewsRequest array{asked?: int|null, by: string, kind?: TitleKind|null, request: string, title?: string|null, unit?: TitleUnit|null}
 * @phpstan-type NewsUpdate array{delivers?: string|null, version: string}
 * @phpstan-type Data News
 */
final class NewsItemsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::NewsItems;

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
