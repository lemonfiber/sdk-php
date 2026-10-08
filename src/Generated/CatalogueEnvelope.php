<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: a1ca5f05d410c09480bf901a4c442e5b141aa1b7, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `catalogue` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Criticality from Shapes
 * @phpstan-type CatalogueReport array{removed: list<RemovedService>, services: list<CataloguedService>}
 * @phpstan-type CataloguedService array{criticality: Criticality, describes: string, id: string, name: string, without_it: string}
 * @phpstan-type RemovedService array{id: string, reason: string, removed_in: string, replaced_by?: string|null}
 * @phpstan-type Data CatalogueReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
