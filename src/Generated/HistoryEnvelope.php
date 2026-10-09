<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: dc6670838b58c27ef1b1c64966f911492ff92c66, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `history` envelope, shaped as the contract describes it.
 *
 * @phpstan-type ChangeReport array{alongside: int, at: string, because?: string|null, did: string, instead?: string|null, operation: string, reversal: ChangeReversal, target: string}
 * @phpstan-type ChangeReversal 'whole'|'partial'|'none'
 * @phpstan-type HistoryReport array{changes: list<ChangeReport>, horizon: string}
 * @phpstan-type Data HistoryReport
 */
final class HistoryEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::History;

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
