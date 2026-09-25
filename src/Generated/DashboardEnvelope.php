<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 844c3a814eda21947270c155943221a2e08e1561, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `dashboard` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{alerts: list<array{affected: list<string>, check: string, kind: string, meaning: string, moment: 'onset'|'resolved', remedies: list<string>, severity: 'advisory'|'warning'|'error'|'critical', summary: string}>, door: array{data: array{address?: array{caution?: string|null, url: string}|null, beside: list<array{address?: array{caution?: string|null, url: string}|null, because: string, facing: 'asking'|'watching'|'shelf'|'operators'|'carriage'|'unstated', service: string}>, chosen: array{chosen: 'derived'}|array{chosen: 'named', door: string}|array{chosen: 'refused', door: array{because: string, named: string}}, facing?: 'asking'|'watching'|'shelf'|'operators'|'carriage'|'unstated'|null, meaning: string, service?: string|null, standing: 'established'|'library-only'|'unreachable'|'stranded'|'none'}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, health: array{affected: list<array{check: string, downstream: list<string>, meaning: string, remedies: list<string>, severity: 'advisory'|'warning'|'error'|'critical', summary: string}>, standing: 'healthy'|'stopped'|'unconfigured'|'advisory'|'degraded'|'broken'|'critical'|'unknown', wanting_attention: int, worst?: string|null}, household: array{data: array{allows?: string|null, available: bool, filtering?: string|null, findings: list<string>, members: list<array{access: array{administrator: bool, age_limit?: int|null, disabled: bool, every_library: bool, libraries: list<string>, rated?: array{allows: list<string>, fell_back: bool, holds_back: list<string>}|null, restriction: 'unrestricted'|'rating-limited'|'library-limited'|'both'|'inconsistent', unrated: 'held-back'|'let-through'}, asking?: array{films: array{limit?: int|null, period?: string|null, remaining?: int|null, used: int}, frees_up?: string|null, policy: 'trusted'|'within-a-limit'|'everything-waits', standing: 'unlimited'|'within-quota'|'near-quota'|'quota-exhausted', television: array{limit?: int|null, period?: string|null, remaining?: int|null, used: int}}|null, claimed: bool, last_seen?: string|null, name: string, requests: list<array{estimate?: array{bytes: int, measured: bool}|null, id: int, media?: string|null, refused?: array{at?: string|null, expired?: bool, reason: string, told?: array{at?: string|null, to: list<string>}|null}|null, state?: 'waiting-for-approval'|'declined'|'failed'|'getting'|'partly-here'|'here'|'gone'|null, title?: string|null, waiting_days?: int|null}>, to_hand_over: list<string>}>, policy?: 'trusted'|'within-a-limit'|'everything-waits'|null}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, queue: array{data: list<array{depth: int, service: string, stuck: int}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, services: array{data: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', depends_on: list<string>, describes: string, exit?: int|null, forms: list<string>, id: string, name: string, profile: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, storage: array{data: array{exhaustion?: array{nanos: int, secs: int}|null, free: array{reading: 'known', value: int}|array{reading: 'stale', value: int}|array{reading: 'unknown'}, hardlink: 'linking'|'copying'|'unknown'}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, stuck: list<array{blocking?: string|null, held_for: int, items: int, name: string, stall: 'redownload-loop'|'repeated-import-failure'|'completed-not-imported'|'orphaned'|'stalled-download'|'waiting-indefinitely'|'slow'}>, telemetry: 'live'|'degraded'|'disconnected'|'no-stack'|'unconfigured', transfers: array{data: list<array{eta?: array{nanos: int, secs: int}|null, name: string, progress: int, protocol: 'usenet'|'torrent', speed: array{reading: 'known', value: int}|array{reading: 'stale', value: int}|array{reading: 'unknown'}}>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}, vpn?: array{data: array{country: string, egress_matches: bool, exit_ip: string, forwarded_port?: int|null}, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}|null}
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
