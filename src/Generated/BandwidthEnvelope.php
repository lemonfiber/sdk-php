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
 * The `bandwidth` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Pulling from Shapes
 * @phpstan-type Answer array{answered: 'held', down: BandwidthHeld, period?: Period|null, up: BandwidthHeld}|array{answered: 'silent', said: string}
 * @phpstan-type BandwidthHeld array{accepted?: int|null, asked?: int|null, moving?: int|null, verdict: BandwidthVerdict}
 * @phpstan-type BandwidthReading array{limit: Limit, resolved: Resolved, says: string}
 * @phpstan-type BandwidthVerdict 'unasked'|'nothing-to-limit'|'holding'|'ignored'|'overrunning'
 * @phpstan-type Cap array{exceeded: WhenExceeded, monthly: int}
 * @phpstan-type Capacity array{down: int, source: Source, taken: int, through_tunnel: bool, up: int}
 * @phpstan-type Holding array{answer: Answer, client: string, pulling?: Pulling|null}
 * @phpstan-type Limit array{as: 'unlimited'}|array{as: 'share', at: int}|array{as: 'absolute', at: int}
 * @phpstan-type Metered array{down: int, excludes: string, incomplete: list<string>, month: string, up: int}
 * @phpstan-type Period 'active'|'quiet'
 * @phpstan-type Reached 'within'|'warning'|'exceeded'
 * @phpstan-type Resolved array{is: 'unlimited'}|array{bytes_per_second: int, is: 'at'}|array{is: 'unmeasured'}
 * @phpstan-type RespiteStanding array{standing: 'none'}|array{seconds: int, standing: 'in-force'}|array{seconds: int, standing: 'expired'}
 * @phpstan-type Restraint 'unlimited'|'limited'|'scheduled-active'|'scheduled-quiet'|'overridden'|'cap-warning'|'cap-exceeded'
 * @phpstan-type Rhythm array{from: string, to: string}
 * @phpstan-type Sharing array{acting?: string|null, applied: bool, cap?: Cap|null, capacity?: Capacity|null, cautions: list<string>, clients: list<Holding>, down: BandwidthReading, means: string, metered?: Metered|null, ratio?: string|null, reached?: Reached|null, rehearsed: bool, respite: RespiteStanding, respite_says?: string|null, restraint: Restraint, rhythm?: Rhythm|null, untouched: list<string>, up: BandwidthReading, zone?: string|null}
 * @phpstan-type Source 'declared'|'observed'
 * @phpstan-type WhenExceeded 'pause'|'throttle'|'continue'
 * @phpstan-type Data Sharing
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
