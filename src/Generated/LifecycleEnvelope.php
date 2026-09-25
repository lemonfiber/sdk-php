<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: b767cc90686b33d68a8af39c283309f8849ac5ba, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `lifecycle` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{action: string, command: list<string>, condition?: 'inactive'|'degraded'|'partial'|'active'|null, forwarding?: string|null, held?: string|null, plan: array{dropped: list<array{needs: 'usenet'|'torrent', profile: string}>, filtered: list<array{forms: list<string>, id: string, name: string, needs: 'usenet'|'torrent', profile: string}>, footprint: array{estimated_mib: int, unestimated: list<string>}, forms: list<string>, profiles: list<string>, services: list<string>}, port_conflicts?: list<array{held_by: string, port: int, wanted_by: string}>, rehearsed: bool, services: list<array{criticality: 'critical'|'core'|'important'|'enhancing'|'optional', depends_on: list<string>, describes: string, exit?: int|null, forms: list<string>, id: string, name: string, profile: string, state: 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'}>, stack_edits: list<array{diff: string, path: string}>, status?: int|null, switched?: array{kept: list<string>, started: list<string>, stop_command?: list<string>|null, stopped: list<string>}|null}
 */
final class LifecycleEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Lifecycle;

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
