<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: aaaf59f88495ca7c7f39c55a4d24cb9a3dcbb7d7, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `settings` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Unvetted from Shapes
 * @phpstan-type Owner array{of: 'core'}|array{namespace: string, of: 'namespace'}
 * @phpstan-type Reader array{default?: string|null, service: string, variable: string}
 * @phpstan-type SecretFrom 'operator'|'recipe'|'granted'|'minted'
 * @phpstan-type SecretRead array{from?: SecretFrom|null, held: bool, name: string, readers: list<Reader>, whose: Owner}
 * @phpstan-type SettingChange array{agreement: string, applied: bool, consequence?: string|null, regenerates: list<string>, restarts: list<string>, setting: string, unrestarted?: string|null, value?: string|null, was?: string|null}
 * @phpstan-type SettingRead array{environment?: string|null, name: string, readers: list<Reader>, value?: string|null, whose: Owner}
 * @phpstan-type SettingsReport array{change?: SettingChange|null, rehearsed: bool, secrets: list<SecretRead>, settings: list<SettingRead>, unvetted?: Unvetted|null}
 * @phpstan-type Data SettingsReport
 */
final class SettingsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Settings;

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
