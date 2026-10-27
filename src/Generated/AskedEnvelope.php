<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 28f15257698f727ce6347408c6e6584f56fe6a57, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `asked` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type TitleKind from Shapes
 * @phpstan-type AskedReport array{kind: TitleKind, leaves: LeftAfter, member: string, outcome: Became, rehearsed: bool, released: bool, said: string, seasons: list<int>, title: string}
 * @phpstan-type Became 'asked'|'waiting'|'on-its-way'
 * @phpstan-type LeftAfter array{films?: int|null, television?: int|null}
 * @phpstan-type Data AskedReport
 */
final class AskedEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Asked;

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
