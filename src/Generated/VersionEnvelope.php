<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 11738d67882155f12773108d2fe7c3bd80fd7c2b, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `version` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Notes from Shapes
 * @phpstan-type VersionReport array{attestation_status?: string|null, binary: string, changelog: Notes, compose?: string|null, stack: string, supported_schema: list<int>}
 * @phpstan-type Data VersionReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
