<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: ad4e5f20666a8a10e0d0ed61f6a1a6a118614162, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `hosting` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Changed array{installed: bool, name: string, rehearsed: bool, started: bool, touched: list<string>}
 * @phpstan-type HostedCommand array{command: string, definition?: string|null, guarantees: string, missing?: string|null, name: string, output?: string|null, runs?: string|null, standing: Hosting}
 * @phpstan-type Hosting 'not-hosted'|'hosted'|'installed-unverified'|'stopped'|'orphaned'|'unsupported'
 * @phpstan-type HostingReport array{caveat?: string|null, changed?: Changed|null, commands: list<HostedCommand>, instruction?: string|null, manager: Manager, rehearsed: bool}
 * @phpstan-type Manager 'launchd'|'systemd'|'unsupported'
 * @phpstan-type Data HostingReport
 */
final class HostingEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Hosting;

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
