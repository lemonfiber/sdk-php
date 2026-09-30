<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: c1ccda0afcca37266c325c0750f0216cfc640098, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `handoff` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{address?: string|null, caution?: string|null, clients: list<array{client: string, code: string, deep_link: bool, device: string, open_source: bool}>, issued?: string|null, name: string, quick_connect: bool, reason?: string|null, rehearsed: bool, sessions: list<array{client: string, device: string, last_seen?: string|null}>, state: 'unprovisioned'|'ready'|'pending'|'connected'|'failed', steps: list<string>}
 */
final class HandoffEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Handoff;

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
