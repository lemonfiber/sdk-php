<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: a1ca3119cbfcfcf6279e73af3cbbf53a64d7fed8, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `boot` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Forwarded from Shapes
 * @phpstan-type BootOutcome 'passed'|'failed'|'waiting-for-install'
 * @phpstan-type BootReport array{booted_at?: int|null, declined?: string|null, failed_boots?: int|null, failing_since?: int|null, last_verified?: BootVerification|null, verified?: BootVerification|null}
 * @phpstan-type BootTunnel array{state: 'held'}|array{said: string, state: 'not-established'}|array{state: 'not-checked'}
 * @phpstan-type BootVerification array{against?: int|null, at: int, not_back?: list<string>, outcome: BootOutcome, port?: Forwarded|null, said?: string|null, tunnel: BootTunnel}
 * @phpstan-type Data BootReport
 */
final class BootEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Boot;

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
