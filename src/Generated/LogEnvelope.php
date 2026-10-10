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
 * The `log` envelope, shaped as the contract describes it.
 *
 * @phpstan-type LogLevel 'trace'|'debug'|'info'|'warn'|'error'|'fatal'
 * @phpstan-type LogLine array{at?: string|null, level?: LogLevel|null, line: string, service: string, stream: Stream}
 * @phpstan-type Stream 'stdout'|'stderr'
 * @phpstan-type Data LogLine
 */
final class LogEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Log;

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
