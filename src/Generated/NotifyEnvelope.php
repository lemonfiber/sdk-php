<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: bde24bea7750e01ca80449d334dc84c578bad596, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `notify` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type NotificationKind from Shapes
 * @phpstan-type NotifyBackend array{chosen_by: NotifyChosenBy, failures: list<NotifyDelivery>, hosted?: NotifyHosted|null, id: string, kind: NotifyBackendKind, last?: NotifyDelivery|null, proof: NotifyProof, retry?: NotifyRetry|null, where: string}
 * @phpstan-type NotifyBackendKind 'ntfy'|'gotify'|'webhook'|'apprise'|'unifiedpush'
 * @phpstan-type NotifyChosenBy 'operator'|array{member: string}
 * @phpstan-type NotifyDelivery array{at: int, kind: NotificationKind, outcome: NotifyOutcome}
 * @phpstan-type NotifyHosted array{host: string, leaves: list<string>}
 * @phpstan-type NotifyOutcome 'confirmed'|'sent'|array{failed: string}
 * @phpstan-type NotifyProof 'unproven'|array{proven: int}|array{held: string}
 * @phpstan-type NotifyReport array{backends: list<NotifyBackend>, rehearsed: bool, state: NotifyState, unread: list<NotifyUnread>}
 * @phpstan-type NotifyRetry array{attempt: int, next: int, of: int}
 * @phpstan-type NotifyState 'unconfigured'|'ready'|'degraded'|'unreachable'
 * @phpstan-type NotifyUnread array{backend: string, because: string}
 * @phpstan-type Data NotifyReport
 */
final class NotifyEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Notify;

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
