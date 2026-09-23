<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 7d4d153a396e03d6c866440f97f9b61e6584c952, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `stuck` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{incomplete: bool, items: list<array{service: string, stage: 'not-monitored'|'monitored'|'searching'|'found'|'grabbed'|'downloading'|'downloaded'|'importing'|'imported'|'available', title: string}>, unsupported?: list<array{because: string, what: string}>}
 */
final class StuckEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Stuck;

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
