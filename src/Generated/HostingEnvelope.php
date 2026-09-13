<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 24ddf47e248873717c50eb3243dec61f43e6cccd, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `hosting` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{caveat?: string|null, changed?: array{installed: bool, name: string, rehearsed: bool, started: bool, touched: list<string>}|null, commands: list<array{command: string, definition?: string|null, guarantees: string, missing?: string|null, name: string, output?: string|null, runs?: string|null, standing: 'not-hosted'|'hosted'|'installed-unverified'|'stopped'|'orphaned'|'unsupported'}>, instruction?: string|null, manager: 'launchd'|'systemd'|'unsupported'}
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data);
    }
}
