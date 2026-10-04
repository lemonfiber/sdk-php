<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 60695f36d8b8af57d669571c79659e82e6a11e43, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `news-items` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{problems: list<array{check: string, onset: string, summary: string}>, requests: list<array{by: string, number: int, title?: string|null}>, unread: list<'updates'|'requests'|'problems'>, updates: list<array{delivers?: string|null, version: string}>}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
