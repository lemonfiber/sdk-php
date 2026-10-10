<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: 0e9fbff3d7b423c54967af31b0b77d083e37b0a0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

use Lemonfiber\Sdk\Envelope\Envelope;
use Lemonfiber\Sdk\Envelope\Payload;
use Lemonfiber\Sdk\Exception\UnexpectedKind;

/**
 * The `plugins` envelope, shaped as the contract describes it.
 *
 * @phpstan-import-type DoctorVerdict from Shapes
 * @phpstan-import-type Finding from Shapes
 * @phpstan-import-type StepCame from Shapes
 * @phpstan-import-type UndoReversal from Shapes
 * @phpstan-type Api array{key_source: KeySource, kind: ApiKind, path?: string|null, version?: int|null}
 * @phpstan-type ApiKind 'servarr'|'sabnzbd'|'qbittorrent'|'seerr'|'bindery'|'jellyfin'|'bazarr'|'audiobookshelf'|'nzbhydra2'
 * @phpstan-type Contribution array{action?: string|null, at: string, category?: string|null, detail?: string|null, expect?: Expect|null, expected?: list<PluginExpectedFailure>, fixture?: string|null, for?: string|null, id: string, request?: PluginRequest|null, service?: string|null, timeout_s?: int|null, title?: string|null, why?: string|null}
 * @phpstan-type Expect array{body_starts_with?: string|null, content_type?: string|null, json?: array<string, Expected>|null, json_array_min?: int|null, json_at_least?: array<string, int>|null, json_has_keys?: list<string>|null, json_is_absent?: bool|null, json_types?: array<string, ExpectedKind>|null, status?: int|null}
 * @phpstan-type Expected bool|int|string
 * @phpstan-type ExpectedKind 'bool'|'int'|'str'|'list'|'dict'
 * @phpstan-type KeySource 'config-xml'|'config-ini'|'config-json'|'config-yaml'|'api-settings'|'generated'|'none'
 * @phpstan-type PluginAdapterOwner 'lemonfiber'
 * @phpstan-type PluginChange array{path: string, puts: PluginPuts}
 * @phpstan-type PluginChangedCheck array{before?: DoctorVerdict|null, now: Finding}
 * @phpstan-type PluginConstraint 'status'|'json'|'json_has_keys'|'json_types'|'json_at_least'|'json_array_min'|'json_is_absent'|'content_type'|'body_starts_with'
 * @phpstan-type PluginDeclaration array{claims?: list<string>, license?: string, overrides?: list<PluginOverriding>, reaches?: list<string>, reviewed?: bool, secrets?: list<PluginSecret>, upstream?: string}
 * @phpstan-type PluginDeclaredVerdict 'fails'
 * @phpstan-type PluginEvidence 'recordings'|'service'
 * @phpstan-type PluginExpectedFailure array{constraint: PluginConstraint, fixture: string, place?: string|null, reason: string, verdict: PluginDeclaredVerdict}
 * @phpstan-type PluginFailingAsDeclared array{constraint: PluginConstraint, fixture: string, held: string, place?: string|null, reason: string}
 * @phpstan-type PluginInstall array{against?: PluginEvidence|null, changes: list<PluginChange>, contests: list<WiringContest>, overrides: list<PluginOverriding>, proofs: list<PluginProving>, recipes_ran: list<PluginRecipeRan>, recorded: bool, reversed?: UndoReversal|null, taking: list<PluginTaking>, verified?: PluginVerification|null, would: PluginInstalled}
 * @phpstan-type PluginInstalled array{adapters?: list<PluginServiceAdapter>, contributions?: list<Contribution>, declared?: PluginDeclaration, description?: string|null, from?: string, installed_at?: string, manifest?: string, name?: string|null, plugin: string, provides?: list<string>, recipes?: list<PluginRecipe>, revision?: string, services: list<PluginPlaced>, signed?: string, version: string}
 * @phpstan-type PluginInstalls array{agreement?: string|null, install?: PluginInstall|null, installed: list<PluginInstalled>, nonconforming?: list<PluginNonconforming>, proof?: PluginReproof|null, rehearsed: bool, removal?: PluginRemoval|null, sources?: list<PluginSource>, substituted?: list<PluginSubstituted>, update?: PluginUpdate|null}
 * @phpstan-type PluginNonconforming array{at: string, capability: string, operation: string, plugin: string, why: string}
 * @phpstan-type PluginOverriding array{setting: string, why: string}
 * @phpstan-type PluginPair array{approval?: string, from?: string, origin: string, release?: string, to: string, value: string}
 * @phpstan-type PluginPlaced array{api?: Api|null, config_path: string, description?: string, digest: string, fronts?: string|null, image: string, listens?: int|null, media_types?: list<string>, name?: string, networks?: list<string>, provides?: list<string>, reached?: PluginReached|null, service: string, shape?: PluginShape|null, speaks?: list<string>, tag: string, takes_data: bool}
 * @phpstan-type PluginProving array{asks: string, came_to?: PluginVerdict|null, establishes: string, of?: string|null, proof: string, why: string}
 * @phpstan-type PluginPuts 'directory'|'document'|'key'|'region'
 * @phpstan-type PluginReached array{group?: string|null, port: int, tier: 'loopback'}|array{group?: string|null, hostname: string, port: int, tier: 'household'}
 * @phpstan-type PluginRecipe array{id: string, pairs: list<PluginPair>, steps: list<PluginStep>, title: string, why: string}
 * @phpstan-type PluginRecipeRan array{held: bool, recipe: string, steps: list<PluginStepRan>, why?: string|null}
 * @phpstan-type PluginRemoval array{interrupts: list<string>, leaves: list<PluginUnfilled>, plugin: string, removed: bool, went_back: UndoReversal}
 * @phpstan-type PluginReproof array{asked: bool, cleared: bool, kept: list<PluginNonconforming>, plugin: string, proofs: list<PluginProving>}
 * @phpstan-type PluginRequest array{accept?: string|null, method: string, path: string}
 * @phpstan-type PluginRestored array{placed: bool, running: bool, version: string}
 * @phpstan-type PluginSecret array{id: string, of: string, why: string}
 * @phpstan-type PluginServiceAdapter array{kind: ApiKind, owner: PluginAdapterOwner, service: string}
 * @phpstan-type PluginShape 'egress-guard'
 * @phpstan-type PluginSource array{from: string, plugin: string, standing: PluginSourceStanding}
 * @phpstan-type PluginSourceStanding array{standing: 'reachable'}|array{standing: 'unreachable', why: string}|array{standing: 'unasked', why: string}
 * @phpstan-type PluginStep array{adapter?: PluginStepAdapter|null, id: string, method: string, path: string, to: string}
 * @phpstan-type PluginStepAdapter array{kind: ApiKind, owner: PluginAdapterOwner}
 * @phpstan-type PluginStepRan array{came: StepCame, landed: bool, status?: int|null, step: string, to: string, tries: int, why?: string|null}
 * @phpstan-type PluginSubstituted array{capability: string, plugin: string, service: string}
 * @phpstan-type PluginTaking array{approval: string, devices: list<string>, grants: list<string>, service: string, shape: PluginShape}
 * @phpstan-type PluginUnfilled array{capability: string, filled_by: string}
 * @phpstan-type PluginUpdate array{from: string, install: PluginInstall, interrupts: list<string>, plugin: string, restored?: PluginRestored|null, stopped?: string|null, to: string, went_back: UndoReversal}
 * @phpstan-type PluginVerdict array{outcome: 'passed'}|array{faults: list<string>, outcome: 'failed'}|array{outcome: 'unproven', why: string}|array{declared: list<PluginFailingAsDeclared>, outcome: 'failing-as-declared'}
 * @phpstan-type PluginVerification array{broke: list<PluginChangedCheck>, unsettled: list<PluginChangedCheck>}
 * @phpstan-type WiringContest array{by: string, capability: string, claimants: list<string>}
 * @phpstan-type Data PluginInstalls
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

        return new Envelope($envelope->apiVersion, $envelope->kind, $data, $envelope->host);
    }
}
