<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: e2aa153d5bd6a7f1bd039e314ace6ba5a164f46c, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `setup` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Protocols array{torrent: bool, usenet: bool}
 * @phpstan-type SetupOutcome 'applied'|'abandoned'|'already-set-up'
 * @phpstan-type SetupReport array{data_root?: string|null, outcome: SetupOutcome, protocols: Protocols, service_user?: string|null}
 * @phpstan-type Data SetupReport
 */
final class SetupEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Setup;

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
