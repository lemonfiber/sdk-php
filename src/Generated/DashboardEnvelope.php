<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 65103564328269478a863ae41bbf527f6f6c5f1e, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `dashboard` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type Alert from Shapes
 * @phpstan-import-type FrontDoorReport from Shapes
 * @phpstan-import-type HouseholdReport from Shapes
 * @phpstan-import-type ProblemSeverity from Shapes
 * @phpstan-import-type Service from Shapes
 * @phpstan-type Affected array{check: string, downstream: list<string>, exit?: int|null, meaning: string, onset: string, remedies: list<string>, severity: ProblemSeverity, summary: string}
 * @phpstan-type DashboardProtocol 'usenet'|'torrent'
 * @phpstan-type DashboardReading array{reading: 'known', value: int}|array{reading: 'stale', value: int}|array{reading: 'unknown'}
 * @phpstan-type Duration array{nanos: int, secs: int}
 * @phpstan-type Hardlink 'linking'|'copying'|'unknown'
 * @phpstan-type HealthStanding 'healthy'|'stopped'|'unconfigured'|'advisory'|'degraded'|'broken'|'critical'|'unknown'
 * @phpstan-type HealthSummary array{affected: list<Affected>, standing: HealthStanding, wanting_attention: int, worst?: string|null}
 * @phpstan-type PanelArray_of_Queue array{data: list<Queue>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelArray_of_Service array{data: list<Service>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelArray_of_Transfer array{data: list<Transfer>, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelFrontDoorReport array{data: FrontDoorReport, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelHouseholdReport array{data: HouseholdReport, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelStorage array{data: Storage, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type PanelVpn array{data: Vpn, panel: 'ready'}|array{data: array{reason: string}, panel: 'unavailable'}
 * @phpstan-type Queue array{depth: int, service: string, stuck: int}
 * @phpstan-type Snapshot array{alerts: list<Alert>, door: PanelFrontDoorReport, health: HealthSummary, household: PanelHouseholdReport, queue: PanelArray_of_Queue, services: PanelArray_of_Service, storage: PanelStorage, stuck: list<Stuck>, telemetry: Telemetry, transfers: PanelArray_of_Transfer, vpn?: PanelVpn|null}
 * @phpstan-type Stall 'redownload-loop'|'repeated-import-failure'|'completed-not-imported'|'orphaned'|'stalled-download'|'waiting-indefinitely'|'slow'
 * @phpstan-type Storage array{exhaustion?: Duration|null, free: DashboardReading, hardlink: Hardlink}
 * @phpstan-type Stuck array{blocking?: string|null, held_for: int, items: int, name: string, stall: Stall}
 * @phpstan-type Telemetry 'live'|'degraded'|'disconnected'|'no-stack'|'unconfigured'
 * @phpstan-type Transfer array{eta?: Duration|null, name: string, progress: int, protocol: DashboardProtocol, speed: DashboardReading}
 * @phpstan-type Vpn array{country: string, egress_matches: bool, exit_ip: string, forwarded_port?: int|null}
 * @phpstan-type Data Snapshot
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
