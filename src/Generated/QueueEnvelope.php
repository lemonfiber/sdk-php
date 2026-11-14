<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 2a22ca0bbdb5f45403cd5c01012e917bc23bbdc4, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `queue` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Act 'remove'|'blocklist'|'search'
 * @phpstan-type ActOutcome 'done'|'would'|'refused'|'fell-back'
 * @phpstan-type ActTaken array{act: Act, instead?: Act|null, outcome: ActOutcome, why?: string|null}
 * @phpstan-type HealRun array{at: int, items?: list<Healed>, rehearsed: bool}
 * @phpstan-type Healed array{acts: list<ActTaken>, release: string, service: string, title?: string|null, why: string}
 * @phpstan-type HealingSwitch 'on'|'off'
 * @phpstan-type QueueHealth array{classified_at?: int|null, grace_s: int, healing: HealingSwitch, incomplete: bool, items?: list<Wedged>, mismatch?: string|null, rehearsed: bool, runs?: list<HealRun>, strikes_needed: int, unreachable?: list<Unreached>}
 * @phpstan-type Unreached array{service: string, why: string}
 * @phpstan-type Wedge 'stalled'|'slow'|'failed'|'stuck-on-metadata'|'orphaned'
 * @phpstan-type Wedged array{added_at?: int|null, class: Wedge, in_grace_until?: int|null, next?: list<Act>|null, release: string, service: string, strikes: int, title?: string|null, why: string}
 * @phpstan-type Data QueueHealth
 */
final class QueueEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Queue;

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
