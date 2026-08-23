<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: v0.9.0-unreleased, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `setup` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{data_root?: string|null, outcome: 'applied'|'abandoned'|'already-set-up', protocols: array{torrent: bool, usenet: bool}, service_user?: string|null}
 */
final class SetupEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Setup;

    /**
     * The same envelope with its payload typed by its kind (ARCH-R63).
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
