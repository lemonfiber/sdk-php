<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: 61611373c390f4f525924990a8e893e37d1f600f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `plugins` envelope, shaped as the contract describes it.
 *
 * @phpstan-type Data array{install?: array{against?: 'recordings'|'service'|null, changes: list<array{path: string, puts: 'directory'|'document'}>, overrides: list<array{setting: string, why: string}>, proofs: list<array{asks: string, came_to?: array{outcome: 'passed'}|array{faults: list<string>, outcome: 'failed'}|array{outcome: 'unproven', why: string}|null, establishes: string, of?: string|null, proof: string, why: string}>, recorded: bool, reversed?: array{left: list<array{because: string, target: string}>, rehearsed: bool, reversed: list<array{action: array{does: 'remove', id: string, resource: string}|array{does: 'restore', key: string, value?: string|null, wrote: string}|array{does: 'delete', path: string}|array{current: string, does: 'repin', previous: string}|array{does: 'reconfigure', field: string, id: string, resource: string, value?: string|null}, target: string}>}|null, verified?: array{broke: list<array{before?: array{note?: string|null, outcome: 'pass'}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'warn', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'fail', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{outcome: 'unverified', reason: string, remedy: array{action: string, detail?: string|null}}|array{outcome: 'skipped', reason: string}|null, now: array{category: 'environment'|'storage'|'network'|'vpn'|'credentials'|'services'|'providers'|'queue'|'config', caused_by?: string|null, check: string, said?: string|null, service?: string|null, title: string, verdict: array{note?: string|null, outcome: 'pass'}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'warn', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'fail', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{outcome: 'unverified', reason: string, remedy: array{action: string, detail?: string|null}}|array{outcome: 'skipped', reason: string}}}>, unsettled: list<array{before?: array{note?: string|null, outcome: 'pass'}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'warn', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'fail', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{outcome: 'unverified', reason: string, remedy: array{action: string, detail?: string|null}}|array{outcome: 'skipped', reason: string}|null, now: array{category: 'environment'|'storage'|'network'|'vpn'|'credentials'|'services'|'providers'|'queue'|'config', caused_by?: string|null, check: string, said?: string|null, service?: string|null, title: string, verdict: array{note?: string|null, outcome: 'pass'}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'warn', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{cause?: array{cause?: mixed, code: string, detail?: string|null, meaning: string, remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|null, code: string, detail?: string|null, meaning: string, outcome: 'fail', remedies: list<array{action: string, detail?: string|null}>, severity: 'advisory'|'warning'|'error'|'critical', state: 'actionable'|'guided'|'remediable'|'unknown'|'suppressed', summary: string}|array{outcome: 'unverified', reason: string, remedy: array{action: string, detail?: string|null}}|array{outcome: 'skipped', reason: string}}}>}|null, would: array{plugin: string, services: list<array{config_path: string, digest: string, image: string, reached?: array{group?: string|null, port: int, tier: 'loopback'}|array{group?: string|null, hostname: string, port: int, tier: 'household'}|null, service: string, tag: string, takes_data: bool}>, version: string}}|null, installed: list<array{plugin: string, services: list<array{config_path: string, digest: string, image: string, reached?: array{group?: string|null, port: int, tier: 'loopback'}|array{group?: string|null, hostname: string, port: int, tier: 'household'}|null, service: string, tag: string, takes_data: bool}>, version: string}>}
 */
final class PluginsEnvelope
{
    /**
     * The kind an envelope must carry to be read as this one.
     */
    public const Kind KIND = Kind::Plugins;

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
