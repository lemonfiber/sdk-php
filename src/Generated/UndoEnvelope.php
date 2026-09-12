<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 387f0c83c55614ac3839bb21e88dd0c3a2feb50f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `undo` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{left: list<array{because: string, target: string}>, reversed: list<array{action: array{does: 'remove', id: string, resource: string}|array{does: 'restore', key: string, value?: string|null, wrote: string}|array{does: 'delete', path: string}|array{current: string, does: 'repin', previous: string}|array{does: 'reconfigure', field: string, id: string, resource: string, value?: string|null}, target: string}>}
 */
final class UndoEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Undo;

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
