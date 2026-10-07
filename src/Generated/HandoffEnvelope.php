<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 5a9496cec5089736f1f018ae55c551dd30cd6673, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `handoff` envelope, shaped as the contract describes it.
 *
 * @phpstan-type HandoffClient array{client: string, code: string, deep_link: bool, device: string, open_source: bool}
 * @phpstan-type HandoffRemedy 'invite'|'ask-again'|'start-server'|'record-address'
 * @phpstan-type HandoffReport array{address?: string|null, caution?: string|null, clients: list<HandoffClient>, issued?: string|null, name: string, quick_connect: bool, reason?: string|null, rehearsed: bool, remedy?: HandoffRemedy|null, sessions: list<HandoffSession>, state: HandoffState, steps: list<string>}
 * @phpstan-type HandoffSession array{client: string, device: string, last_seen?: string|null}
 * @phpstan-type HandoffState 'unprovisioned'|'ready'|'pending'|'connected'|'failed'
 * @phpstan-type Data HandoffReport
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
