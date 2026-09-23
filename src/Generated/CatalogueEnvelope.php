<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 14d079c574ea72f9dcba3c16c0b6b96bf44fd9c2, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `catalogue` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{removed: list<array{id: string, reason: string, removed_in: string, replaced_by?: string|null}>, services: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', describes: string, id: string, name: string, without_it: string}>}
 */
final class CatalogueEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Catalogue;

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
