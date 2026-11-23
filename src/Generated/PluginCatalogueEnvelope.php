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
 * The `plugin-catalogue` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Instant from Shapes
 * @phpstan-type CatalogueFailure array{code: string, summary: string}
 * @phpstan-type CataloguedPlugin array{description: string, id: string, installed?: InstalledVersion|null, name: string, version: string, without_it: string}
 * @phpstan-type InstalledVersion array{newer: bool, version: string}
 * @phpstan-type PluginCatalogueReport array{entries: list<CataloguedPlugin>, failure?: CatalogueFailure|null, published?: Instant|null, serial?: int|null, setting?: ReachSetting|null, state: PluginCatalogueState, unreadable?: int|null}
 * @phpstan-type PluginCatalogueState 'listed'|'off'|'failed'
 * @phpstan-type ReachSetting array{action: SettingWrite, key: string, value: string}
 * @phpstan-type SettingWrite 'config-set'
 * @phpstan-type Data PluginCatalogueReport
 */
final class PluginCatalogueEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::PluginCatalogue;

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
