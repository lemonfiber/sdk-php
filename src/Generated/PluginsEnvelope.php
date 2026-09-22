<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 9628c81a376b78ad26c54847659f6047a544b913, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `plugins` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{install?: array{recorded: bool, would: array{plugin: string, services: list<array{config_path: string, digest: string, image: string, reached?: array{group?: string|null, port: int, tier: 'loopback'}|array{group?: string|null, hostname: string, port: int, tier: 'household'}|null, service: string, tag: string, takes_data: bool}>, version: string}}|null, installed: list<array{plugin: string, services: list<array{config_path: string, digest: string, image: string, reached?: array{group?: string|null, port: int, tier: 'loopback'}|array{group?: string|null, hostname: string, port: int, tier: 'household'}|null, service: string, tag: string, takes_data: bool}>, version: string}>}
 */
final class PluginsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Plugins;

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
