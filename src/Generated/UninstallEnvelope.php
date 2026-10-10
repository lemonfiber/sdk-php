<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `uninstall` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Coming array{name: string, progress: int}
 * @phpstan-type Foreign array{at: string, bytes: int, files: int}
 * @phpstan-type Item array{bytes?: int|null, kept?: string|null, name: string, secret: bool, sort: Sort, what: string}
 * @phpstan-type Outside array{by_hand: string, found: bool, what: string, why: string}
 * @phpstan-type Sort 'container'|'network'|'image'|'path'
 * @phpstan-type Tier 'stop'|'services'|'configuration'|'media'
 * @phpstan-type Uninstall array{manifest: UninstallManifest, rehearsed: bool, removal: UninstallRemoval}
 * @phpstan-type UninstallConfidence array{complete: bool, unread: list<string>}
 * @phpstan-type UninstallLeft array{by_hand: string, name: string, why: string}
 * @phpstan-type UninstallManifest array{agreement: string, backup?: string|null, bytes: int, coming: list<Coming>, confidence: UninstallConfidence, foreign: list<Foreign>, items: list<Item>, keeps: string, outside: list<Outside>, removes: string, tier: Tier, volume?: string|null}
 * @phpstan-type UninstallRemoval array{state: 'surveyed'}|array{state: 'confirmed'}|array{credentials: list<string>, gone: list<string>, state: 'complete'}|array{credentials: list<string>, gone: list<string>, left: list<UninstallLeft>, state: 'partial'}
 * @phpstan-type Data Uninstall
 */
final class UninstallEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Uninstall;

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
