<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: c8b5ffb1983acf50ff270be8a1a930a1c98cfb2c, api_version 1.
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
 * @phpstan-type ConfigReport array{changed: bool, consequence?: string|null, rehearsed: bool, review?: Review|null, settings: list<SettingReport>}
 * @phpstan-type Cost 'cheap'|'consequential'
 * @phpstan-type Edited array{found: string, secret: bool, wrote: string}
 * @phpstan-type Findings array{active: list<Active>, edited?: Edited|null, keeps: list<string>, library: list<LibraryPath>, opens: list<Opening>, stops: list<string>}
 * @phpstan-type LibraryPath array{because: string, carried: bool, host?: string|null, path: string, service: string}
 * @phpstan-type Opening array{because: string, setting?: string|null, what: string}
 * @phpstan-type Review array{change: ConfigChange, findings?: Findings, proof?: Validation|null, refusal?: string|null, stance: Stance}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
