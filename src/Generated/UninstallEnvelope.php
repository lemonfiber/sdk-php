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
 * The `uninstall` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{manifest: array{agreement: string, backup?: string|null, bytes: int, coming: list<array{name: string, progress: int}>, confidence: array{complete: bool, unread: list<string>}, foreign: list<array{at: string, bytes: int, files: int}>, items: list<array{bytes?: int|null, kept?: string|null, name: string, secret: bool, sort: 'container'|'network'|'image'|'path', what: string}>, keeps: string, outside: list<array{by_hand: string, found: bool, what: string, why: string}>, removes: string, tier: 'stop'|'services'|'configuration'|'media', volume?: string|null}, removal: array{state: 'surveyed'}|array{state: 'confirmed'}|array{credentials: list<string>, gone: list<string>, state: 'complete'}|array{credentials: list<string>, gone: list<string>, left: list<array{by_hand: string, name: string, why: string}>, state: 'partial'}}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
