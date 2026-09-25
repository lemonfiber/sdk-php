<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: c155500c230b63a9da22b07d0054b1ca07526733, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `version` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{binary: string, changelog: array{releases: list<array{delivers?: string|null, patches?: string|null, released_on?: string|null, user_facing: bool, version: string, withdrawn?: string|null}>, requirements: array<string, array{feature: string, shipped_in: list<string>, url?: string|null, withdrawn?: bool}>, running?: array{carried?: string|null, delivers?: string|null, groups: list<array{entries: list<array{reference?: string|null, requirements: list<string>, summary: string}>, title: string}>, patches?: string|null, released_on?: string|null, tag: string, user_facing: bool, version: string, withdrawn?: string|null}|null, state: 'current'|'pending'|'stale'}, compose?: string|null, stack: string, supported_schema: list<int>}
 */
final class VersionEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Version;

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
