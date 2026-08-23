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
 * The `log` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{at?: string|null, line: string, service: string, stream: 'stdout'|'stderr'}
 */
final class LogEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Log;

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
