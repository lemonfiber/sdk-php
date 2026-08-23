<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 5911b3a5523129f9c5d09e04d1c60e5adc32edd2, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `dashboard` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{alerts: list<array{affected: list<string>, check: string, kind: string, moment: 'onset'|'resolved', remedies: list<string>, severity: 'advisory'|'warning'|'error'|'critical', summary: string}>, health: array{affected: list<array{check: string, downstream: list<string>, remedies: list<string>, severity: 'advisory'|'warning'|'error'|'critical', summary: string}>, standing: 'healthy'|'stopped'|'unconfigured'|'advisory'|'degraded'|'broken'|'critical'|'unknown', wanting_attention: int, worst?: string|null}, queue: array{data: list<array{depth: int, service: string, stuck: int}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, services: array{data: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', depends_on: list<string>, exit?: int|null, id: string, name: string, profile: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, storage: array{data: array{exhaustion?: array{nanos: int, secs: int}|null, free: array{reading: 'known', value: int}|array{reading: 'stale', value: int}|array{reading: 'unknown'}, hardlink: 'linking'|'copying'|'unknown'}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, stuck: list<array{blocking?: string|null, held_for: int, items: int, name: string, stall: 'redownload-loop'|'repeated-import-failure'|'completed-not-imported'|'orphaned'|'stalled-download'|'waiting-indefinitely'|'slow'}>, telemetry: 'live'|'degraded'|'disconnected'|'no-stack'|'unconfigured', transfers: array{data: list<array{eta?: array{nanos: int, secs: int}|null, name: string, progress: int, protocol: 'usenet'|'torrent', speed: array{reading: 'known', value: int}|array{reading: 'stale', value: int}|array{reading: 'unknown'}}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, vpn?: array{data: array{country: string, egress_matches: bool, exit_ip: string, forwarded_port?: int|null}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}|null}
 */
final class DashboardEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Dashboard;

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
