<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: d477cc1d38842ce1245279201fb03b6fbef2e51d, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `stop-seeding` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{agreement: string, download: array{bytes: int, consequence?: string|null, name: string, standing: array{standing: 'never_imported'}|array{ratio: int, standing: 'seeding'}|array{standing: 'left_alone'}}, goes: string, gone?: array{bytes: int, name: string, rehearsed: bool}|null}
 */
final class StopSeedingEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::StopSeeding;

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
