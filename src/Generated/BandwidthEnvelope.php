<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 14d079c574ea72f9dcba3c16c0b6b96bf44fd9c2, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `bandwidth` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{acting?: string|null, applied: bool, cap?: array{exceeded: 'pause'|'throttle'|'continue', monthly: int}|null, capacity?: array{down: int, source: 'declared'|'observed', taken: int, through_tunnel: bool, up: int}|null, cautions: list<string>, clients: list<array{answer: array{answered: 'held', down: array{accepted?: int|null, asked?: int|null, moving?: int|null, verdict: 'unasked'|'nothing-to-limit'|'holding'|'ignored'|'overrunning'}, period?: 'active'|'quiet'|null, up: array{accepted?: int|null, asked?: int|null, moving?: int|null, verdict: 'unasked'|'nothing-to-limit'|'holding'|'ignored'|'overrunning'}}|array{answered: 'silent', said: string}, client: string, pulling?: 'fetching'|'stopped'|null}>, down: array{limit: array{as: 'unlimited'}|array{as: 'share', at: int}|array{as: 'absolute', at: int}, resolved: array{is: 'unlimited'}|array{bytes_per_second: int, is: 'at'}|array{is: 'unmeasured'}, says: string}, means: string, metered?: array{down: int, excludes: string, incomplete: list<string>, month: string, up: int}|null, ratio?: string|null, reached?: 'within'|'warning'|'exceeded'|null, respite: array{standing: 'none'}|array{seconds: int, standing: 'in-force'}|array{seconds: int, standing: 'expired'}, respite_says?: string|null, restraint: 'unlimited'|'limited'|'scheduled-active'|'scheduled-quiet'|'overridden'|'cap-warning'|'cap-exceeded', rhythm?: array{from: string, to: string}|null, untouched: list<string>, up: array{limit: array{as: 'unlimited'}|array{as: 'share', at: int}|array{as: 'absolute', at: int}, resolved: array{is: 'unlimited'}|array{bytes_per_second: int, is: 'at'}|array{is: 'unmeasured'}, says: string}, zone?: string|null}
 */
final class BandwidthEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Bandwidth;

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
