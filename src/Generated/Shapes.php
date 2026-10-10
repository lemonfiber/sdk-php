<?php

// Generated from the contract vendored under contract/. Do not edit.
// Source: cf9a18239dd3e3d710ae8d9d9d8916ab255b799f, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every shape more than one kind carries, each named once and imported where it is used.
 *
 * @phpstan-type Action array{does: 'remove', id: string, resource: string}|array{does: 'restore', key: string, value?: string|null, wrote: string}|array{does: 'delete', path: string}|array{does: 'withdraw', key: string, owner: string, path: string, written: int}|array{does: 'rewind', path: string, previous: string, written: int}|array{current: string, does: 'repin', previous: string}|array{does: 'reconfigure', field: string, id: string, resource: string, value?: string|null}|array{does: 'revoke', name: string}|array{does: 'reinstate', name: string}
 * @phpstan-type Address array{caution?: string|null, url: string}
 * @phpstan-type Alert array{affected: list<string>, check: string, exit?: int|null, id?: string|null, kind: string, meaning: string, moment: Moment, remedies: list<string>, severity: ProblemSeverity, summary: string}
 * @phpstan-type AskingStanding 'unlimited'|'within-quota'|'near-quota'|'quota-exhausted'
 * @phpstan-type Candidate array{bytes: int, consequence?: string|null, name: string, standing: SeedingStanding}
 * @phpstan-type CarryingReport array{backup_first: bool, because: string, existing: string, ours: string, refused: bool, service: string, verdict: string}
 * @phpstan-type ChangelogState 'current'|'pending'|'stale'
 * @phpstan-type Chosen array{chosen: 'derived'}|array{chosen: 'named', door: string}|array{chosen: 'refused', door: Refusal}
 * @phpstan-type Code string
 * @phpstan-type Condition 'inactive'|'degraded'|'partial'|'active'
 * @phpstan-type ConflictReport array{held_by: string, port: int, wanted_by: string}
 * @phpstan-type Counted array{limit?: int|null, period?: string|null, remaining?: int|null, used: int}
 * @phpstan-type Criticality 'critical'|'core'|'important'|'enhancing'|'optional'
 * @phpstan-type Disposition 'shown'|'recorded'|'rehearsed'|'held'|'reapplied'|'would-reapply'
 * @phpstan-type DoctorCategory 'environment'|'storage'|'network'|'vpn'|'credentials'|'services'|'providers'|'queue'|'config'
 * @phpstan-type DoctorVerdict array{note?: string|null, outcome: 'pass'}|array{cause?: Problem|null, code: Code, detail?: string|null, meaning: string, outcome: 'warn', remedies: list<Remedy>, severity: ProblemSeverity, state: ProblemState, steps?: list<ProblemStep>, summary: string}|array{cause?: Problem|null, code: Code, detail?: string|null, meaning: string, outcome: 'fail', remedies: list<Remedy>, severity: ProblemSeverity, state: ProblemState, steps?: list<ProblemStep>, summary: string}|array{outcome: 'unverified', reason: string, remedy: Remedy}|array{outcome: 'skipped', reason: string}
 * @phpstan-type Dropped array{needs: StackProtocol, profile: string}
 * @phpstan-type Entry array{reference?: string|null, requirements: list<string>, summary: string}
 * @phpstan-type Estimate array{bytes: int, measured: bool}
 * @phpstan-type Facing 'asking'|'watching'|'shelf'|'operators'|'carriage'|'unstated'
 * @phpstan-type Filtered array{forms: list<string>, id: string, name: string, needs: StackProtocol, profile: string}
 * @phpstan-type Finding array{category: DoctorCategory, caused_by?: string|null, check: string, onset?: string|null, origin: ValueOrigin, said?: string|null, service?: string|null, service_name?: string|null, title: string, verdict: DoctorVerdict}
 * @phpstan-type Footprint array{estimated_mib: int, unestimated: list<string>}
 * @phpstan-type FrontDoorBeside array{address?: Address|null, because: string, facing: Facing, service: string}
 * @phpstan-type FrontDoorReport array{address?: Address|null, beside: list<FrontDoorBeside>, chosen: Chosen, facing?: Facing|null, meaning: string, service?: string|null, standing: FrontDoorStanding}
 * @phpstan-type FrontDoorStanding 'established'|'library-only'|'unreachable'|'stranded'|'none'
 * @phpstan-type Group array{entries: list<Entry>, title: string}
 * @phpstan-type HouseholdMember array{access: MemberAccess, asking?: MemberAsking|null, claimed: bool, last_seen?: string|null, name: string, requests: list<MemberRequest>, standing: MemberStanding, to_hand_over: list<string>}
 * @phpstan-type HouseholdReport array{allows?: string|null, available: bool, filtering?: string|null, findings: list<string>, members: list<HouseholdMember>, policy?: Policy|null, rehearsed: bool}
 * @phpstan-type KeyPurpose 'home-assistant'|'mcp'|'other'
 * @phpstan-type Line array{detail: string, said: string, step: WalkthroughStep}
 * @phpstan-type Medium 'film'|'series'|'episode'|'other'
 * @phpstan-type MemberAccess array{administrator: bool, age_limit?: int|null, disabled: bool, every_library: bool, libraries: list<string>, rated?: Rated|null, restriction: Restriction, unrated: Unrated}
 * @phpstan-type MemberAsking array{films: Counted, frees_up?: string|null, policy: Policy, standing: AskingStanding, television: Counted}
 * @phpstan-type MemberRequest array{arrived?: string|null, estimate?: Estimate|null, id: int, media?: string|null, refused?: Refused|null, shelf_id?: string|null, state?: RequestState|null, title?: string|null, waiting_days?: int|null, year?: int|null}
 * @phpstan-type MemberStanding 'invited'|'expired'|'declined'|'active'|'suspended'
 * @phpstan-type Moment 'onset'|'resolved'
 * @phpstan-type MovedReport array{from: int, service: string, to: int}
 * @phpstan-type MusicChoice array{format: string, means: string, note: string, scope: string, size_per_hour: string, targets: string}
 * @phpstan-type NewsKind 'updates'|'requests'|'problems'
 * @phpstan-type Notes array{releases: list<ReleaseSummary>, requirements: array<string, Requirement>, running?: Release|null, state: ChangelogState}
 * @phpstan-type Passed array{at?: string|null, to: list<string>}
 * @phpstan-type Pinned array{fingerprint: string}
 * @phpstan-type Plan array{dropped: list<Dropped>, filtered: list<Filtered>, footprint: Footprint, forms: list<string>, profiles: list<string>, running?: list<string>|null, services: list<string>}
 * @phpstan-type Policy 'trusted'|'within-a-limit'|'everything-waits'
 * @phpstan-type Problem array{cause?: mixed, code: Code, detail?: string|null, meaning: string, remedies: list<Remedy>, severity: ProblemSeverity, state: ProblemState, steps?: list<ProblemStep>, summary: string}
 * @phpstan-type ProblemSeverity 'advisory'|'warning'|'error'|'critical'
 * @phpstan-type ProblemState 'actionable'|'guided'|'remediable'|'unknown'|'suppressed'
 * @phpstan-type ProblemStep array{came: StepCame, landed: bool, recipe: string, step: string}
 * @phpstan-type Pulling 'fetching'|'stopped'
 * @phpstan-type Rated array{allows: list<string>, fell_back: bool, holds_back: list<string>}
 * @phpstan-type Reaches array{capability: string, how: 'asked', origins: array<string, ValueOrigin>, services: list<string>, settled: WiringSettled}|array{how: 'by-name', service: string, why: string}
 * @phpstan-type Refusal array{because: string, named: string}
 * @phpstan-type Refused array{at?: string|null, expired?: bool, reason: string, told?: Passed|null}
 * @phpstan-type Release array{carried?: string|null, delivers?: string|null, groups: list<Group>, patches?: string|null, released_on?: string|null, tag: string, user_facing: bool, version: string, withdrawn?: string|null}
 * @phpstan-type ReleaseSummary array{delivers?: string|null, patches?: string|null, released_on?: string|null, user_facing: bool, version: string, withdrawn?: string|null}
 * @phpstan-type Remedy array{action: string, detail?: string|null}
 * @phpstan-type RequestState 'waiting-for-approval'|'declined'|'failed'|'getting'|'partly-here'|'here'|'gone'
 * @phpstan-type Requirement array{feature: string, shipped_in: list<string>, url?: string|null, withdrawn?: bool}
 * @phpstan-type Restriction 'unrestricted'|'rating-limited'|'library-limited'|'both'|'inconsistent'
 * @phpstan-type Scope array{scope: 'whole_stack'}|array{name: string, scope: 'service'}|array{project: string, scope: 'existing', trees: list<Tree>}
 * @phpstan-type SeedingStanding array{standing: 'never_imported'}|array{ratio: int, standing: 'seeding'}|array{standing: 'left_alone'}
 * @phpstan-type Service array{criticality: Criticality, depends_on: list<string>, describes: string, exit?: int|null, forms: list<string>, id: string, name: string, profile: string, state: ServiceState}
 * @phpstan-type ServiceState 'failed'|'crash-looping'|'unhealthy'|'absent'|'stopped'|'starting'|'running'|'healthy'|'host-managed'
 * @phpstan-type SettingReport array{key: string, origin: ValueOrigin, secret: bool, value: string}
 * @phpstan-type StackEdit array{diff: string, path: string}
 * @phpstan-type StackProtocol 'usenet'|'torrent'
 * @phpstan-type Stage 'not-monitored'|'monitored'|'searching'|'found'|'grabbed'|'downloading'|'downloaded'|'importing'|'imported'|'available'
 * @phpstan-type Stance 'unchanged'|'pending'|'blocked'|'applied'
 * @phpstan-type StepCame 'answered'|'skipped'|'not-reached'|'unreachable'|'refused'|'withheld'|'unexpected'|'uncaptured'|'oversized'
 * @phpstan-type Term array{also_called: list<string>, deep?: string|null, forms: list<string>, short: string, word: string}
 * @phpstan-type Tree array{archive_path: string, host_path: string}
 * @phpstan-type Triggered array{state: 'started'}|array{state: 'not-started'}|array{detail: string, state: 'failed'}
 * @phpstan-type Undo array{action: Action, target: string}
 * @phpstan-type UndoLeft array{because: string, target: string}
 * @phpstan-type UndoNoted array{because: string, target: string}
 * @phpstan-type UndoReversal array{left: list<UndoLeft>, noted?: list<UndoNoted>, rehearsed: bool, reversed: list<Undo>}
 * @phpstan-type Unfilled array{by: string, capability: string}
 * @phpstan-type Unrated 'held-back'|'let-through'
 * @phpstan-type UnsupportedReport array{because: string, what: string}
 * @phpstan-type Validation array{observed: string, outcome: 'valid'}|array{detail: string, outcome: 'rejected'}|array{detail: string, outcome: 'unreachable'}|array{detail: string, outcome: 'degraded'}
 * @phpstan-type ValueOrigin array{origin: 'bundled'}|array{origin: 'operator'}|array{named: string, origin: 'plugin'}|array{origin: 'unknown', why: string}|array{named: string, origin: 'overridden', replaced: mixed}|array{named: string, origin: 'orphaned'}
 * @phpstan-type ValueReplaced array{from: mixed, value?: string|null, withheld: bool}
 * @phpstan-type WalkthroughStep 'choosing'|'searching'|'grabbing'|'downloading'|'importing'|'scanning'|'available'
 * @phpstan-type Whose 'stack'|'operator'
 * @phpstan-type Wired array{by: string, origin: ValueOrigin, reaches: Reaches}
 * @phpstan-type WiringSettled array{settled: 'outright'}|array{settled: 'each'}|array{claimants: list<string>, settled: 'contested'}|array{over: list<string>, settled: 'chosen', whose: Whose, why?: string|null}|array{settled: 'unfilled'}
 */
final class Shapes {}
