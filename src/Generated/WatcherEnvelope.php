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
 * The `watcher` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Covers array{service: string}|array{'entry-point': string}|array{job: string}
 * @phpstan-type Heartbeat array{every_s: int, grace_s: int, last_check_in?: int|null}
 * @phpstan-type MonitorKind 'http'|'keyword'|'port'|'ping'|'dns'|'docker'|'heartbeat'
 * @phpstan-type Unconfigured array{wanted: string, watches: string, why: string}
 * @phpstan-type WatchPath 'household'|'internal'
 * @phpstan-type WatchState 'up'|'down'|'pending'|'paused'|'expected-down'|'not-reporting'
 * @phpstan-type WatchedTarget array{active: bool, covers?: Covers|null, heartbeat?: Heartbeat|null, kind: MonitorKind, last_reading?: int|null, name: string, path?: WatchPath|null, since: int, state: WatchState, unconfigured?: Unconfigured|null}
 * @phpstan-type WatcherReport array{monitors?: list<WatchedTarget>, reachable: bool, read_at: int, uncovered?: list<Covers>, unreachable_because?: string|null, watcher?: string|null}
 * @phpstan-type Data WatcherReport
 */
final class WatcherEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Watcher;

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
