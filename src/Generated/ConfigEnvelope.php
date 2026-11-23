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
 * The `config` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type SettingReport from Shapes
 * @phpstan-import-type Stance from Shapes
 * @phpstan-import-type Validation from Shapes
 * @phpstan-type Active array{name: string, progress: int, protocol: string}
 * @phpstan-type ConfigChange array{cost: Cost, from?: string|null, key: string, to: string}
 * @phpstan-type ConfigReport array{changed: bool, consequence?: string|null, decisions: list<DecisionReport>, rehearsed: bool, review?: Review|null, settings: list<SettingReport>, unmanaged: list<UnmanagedReport>, unmanaged_more: int}
 * @phpstan-type Cost 'cheap'|'consequential'
 * @phpstan-type CoveredKind 'service'|'path'|'setting'
 * @phpstan-type CoveredReport array{kind: CoveredKind, name: string}
 * @phpstan-type DecisionKey 'DATA_ROOT'|'MEDIA_SERVER_MODE'|'LEMONFIBER_USENET'|'LEMONFIBER_TORRENT'|'LEMONFIBER_VPN'|'PUID'|'PGID'|'VPN_PROVIDER'|'INDEXER_URL'|'INDEXER_APIKEY'|'USENET_HOST'|'USENET_PORT'|'USENET_USER'|'USENET_PASS'|'USENET_TLS'|'LEMONFIBER_FRONT_DOOR'|'TZ'|'LEMONFIBER_LOCALE'
 * @phpstan-type DecisionReport array{affects: string, cost: Cost, credential: bool, held: bool, key: DecisionKey, setting?: string|null}
 * @phpstan-type Edited array{found: string, secret: bool, wrote: string}
 * @phpstan-type Findings array{active: list<Active>, edited?: Edited|null, keeps: list<string>, library: list<LibraryPath>, opens: list<Opening>, stops: list<string>}
 * @phpstan-type LibraryPath array{because: string, carried: bool, host?: string|null, path: string, service: string}
 * @phpstan-type Opening array{because: string, setting?: string|null, what: string}
 * @phpstan-type Review array{change: ConfigChange, findings?: Findings, proof?: Validation|null, refusal?: string|null, stance: Stance}
 * @phpstan-type UnmanagedReport array{area: string, area_cut: bool, covers: list<CoveredReport>, covers_more: int, why: string, why_cut: bool}
 * @phpstan-type Data ConfigReport
 */
final class ConfigEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Config;

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
