# lemonfiber/sdk-php

The PHP client for [lemonfiber](https://github.com/lemonfiber/lemonfiber)'s local HTTP API.

`sdk-php` is a peer of [`sdk-ts`](https://github.com/lemonfiber/sdk-ts). Both implement the same
specification; neither defines it. **The [spec](https://github.com/lemonfiber/spec) is the
reference** — where this client disagrees with the contract, this client is wrong.

## Install

> **Status: unreleased.** The package is not on Packagist and this repository has
> no tags, so the command below does not resolve yet.

```sh
composer require lemonfiber/sdk-php
```

Requires PHP 8.5. The runtime dependencies are [Saloon 4](https://docs.saloon.dev) and Guzzle 8,
plus the PSR-7 interfaces.

## Use

lemonfiber prints a token each time it starts. Pass it in; the client sends it as a header and
never puts it in an address.

```php
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Time\Duration;

$client = Client::onPort(9000, $tokenLemonfiberPrinted);

$status = $client->read(Api::STATUS_ENDPOINT);
$status->kind;        // 'status'
$status->data;        // the payload, shaped by kind

$client->act('/api/actions/restart', ['forms' => ['tv'], 'services' => ['sonarr']]);
```

`Api` carries the path of every read this client knows, so a caller names the read instead of
spelling where it lives — the contract describes envelope kinds and no endpoints, so the paths are
this client's to hold and its to move:

| Constant | Answers with | Takes |
|---|---|---|
| `Api::VERSION_ENDPOINT` | `version` | nothing |
| `Api::FORMS_ENDPOINT` | `forms`, or `preview` naming some | `form`, which lemonfiber accepts more than once |
| `Api::STATUS_ENDPOINT` | `status` | nothing |
| `Api::SERVICES_ENDPOINT` | `status`, narrowed | `form`, which lemonfiber accepts more than once |
| `Api::CHECKS_ENDPOINT` | `doctor` | `only`, naming a group of checks or one check |
| `Api::STORAGE_ENDPOINT` | `doctor`, held to the disk | nothing |
| `Api::LOGS_ENDPOINT` | `log`, one a line | `form` and `service`, each more than once, plus `tail` and `follow` |
| `Api::REQUESTS_ENDPOINT` | `household` | `member`, once |
| `Api::HELD_ENDPOINT` | `held` | `member`, whose shelf, and `most`, how many |
| `Api::CONFIG_ENDPOINT` | `config` | `key`, once; naming none is every setting |
| `Api::QUALITY_ENDPOINT` | `quality` | nothing |
| `Api::TRACE_ENDPOINT` | `trace` | `term`, what is followed, and `season` |
| `Api::STUCK_ENDPOINT` | `stuck` | nothing |
| `Api::UPDATE_ENDPOINT` | `update` or `self-update` | `what`, required, and `to` for the `self` reading |
| `Api::ALERTS_ENDPOINT` | `alerts` | nothing |
| `Api::BANDWIDTH_ENDPOINT` | `bandwidth` | nothing |
| `Api::SPACE_ENDPOINT` | `space` | nothing |
| `Api::STORED_ENDPOINT` | `stored` | nothing |
| `Api::OUTBOUND_ENDPOINT` | `outbound` | nothing |
| `Api::CATALOGUE_ENDPOINT` | `catalogue` | nothing |
| `Api::PROVENANCE_ENDPOINT` | `provenance` | nothing |
| `Api::CLIENTS_ENDPOINT` | `clients` | nothing |
| `Api::CREDENTIALS_ENDPOINT` | `credentials`, carrying no value | nothing |
| `Api::HISTORY_ENDPOINT` | `history` | nothing |
| `Api::HOSTING_ENDPOINT` | `hosting` | nothing |
| `Api::MIGRATION_ENDPOINT` | `migration` | nothing |
| `Api::FRONT_DOOR_ENDPOINT` | `front-door` | nothing |
| `Api::EXPLAIN_ENDPOINT` | `word`, or `glossary` naming none | `word`, once |
| `Api::BACKUPS_ENDPOINT` | `archives` | nothing |
| `Api::bundle($name)` | the bundle file itself, as `application/gzip` rather than an envelope | nothing; the name is the last segment |
| `Api::UNINSTALL_ENDPOINT` | `uninstall` | `tier`, once; naming none removes nothing |

```php
$client->read(Api::REQUESTS_ENDPOINT, ['member' => 'ada']);
```

A read refuses a parameter it has no name for rather than dropping it, since a dropped narrowing
answers a wider question than the one that was asked and a wider answer reads like the answer.

An action's name and its arguments are the command line's own. A name this surface does not
offer is refused rather than invented, and a field no action takes is refused rather than
ignored. The name is composed into a path by `Api::action()` rather than written out by
whoever calls, so there is one place it moves when lemonfiber moves it.

The repair is the one action with a method of its own, since it is the one whose two halves are
a single request read twice. Unconfirmed it says what each repair would do and what else
changes if it does; confirmed it carries out what was agreed to, and the yes names the offer it
answered:

```php
use Lemonfiber\Sdk\Repair;

$client->repair(Repair::offer());                                // says what it would do, does none of it
$client->repair(Repair::agreedTo($agreement, 'vpn.killswitch')); // carries that one out, out of that offer
```

`$agreement` is the `agreement` the `repair` envelope came back with, which is what names the
offer being answered. lemonfiber builds that name again from a fresh look before it acts and
refuses an agreement whose offer has moved on, so a yes given to something that has since
changed is turned down rather than spent on whatever stands now.

`Repair` is a type rather than three arguments so that the arrangements lemonfiber refuses
cannot be written at all: a yes that does not name the offer it answered, and an offer with none
of it agreed to. `Repair::agreedInAdvance()` is the third shape — the standing consent the
command line spells `--yes` — and it is spelled out at the call site, since a run carried out
under it was agreed to by whoever wrote the call rather than by whoever is at the screen.

A repair reaches the services, so what comes back is a name for the work rather than its
outcome: the `job` envelope, and the `repair` envelope arrives through it.

## When nothing answers

Every failure this client raises implements `Exception\Problem`, including a request that nothing
answered. A stack that is stopped, asleep or out of reach, a connection that is refused, and one
that breaks before an answer arrives all raise `Exception\Unreachable`, from every call that
sends — a read, an action, a repair, asking after or letting go of work, the logs, live updates
and the door. The transport's own exception never reaches a caller.

```php
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;

try {
    $client->read(Api::STATUS_ENDPOINT);
} catch (RequestFailed $refused) {
    $refused->status();   // lemonfiber answered, and said no
} catch (Unreachable $silence) {
    $silence->endpoint(); // '/api/status'
    $silence->reason();   // what the connection reported
}
```

A pinned address whose peer presents a certificate other than the pinned one raises
`Exception\CertificateWasRefused` instead of `Unreachable`: something answered, and it is not
the machine the pin was taken from. The connection is refused during the handshake and nothing
is written to it; the client then reads the certificate the peer presented, over a connection
that writes nothing either, to tell that refusal apart from silence. `presented()` and
`pinned()` carry both digests.

`RequestFailed` is lemonfiber answering, which says the connection works and the address is
right; `Unreachable` is no answer at all. `reason()` is the connection's own words, with every
address in them cut back to its scheme, host, port and path, so sign-in details and a query
never travel with it. It does not say the request went unheard: an action whose answer was lost
on the way back may have been applied, and re-sending it under the same attempt name is one act.

## Work that outlives the request

Any action reaching the services runs for minutes, so lemonfiber answers it with a name and
runs the work somewhere the connection cannot reach. The name is redeemed afterwards:

```php
$standing = $client->whatBecameOf($job);

$standing->answering(
    stillRunning: fn() => 'ask again in a moment',
    finished: fn(Envelope $outcome) => $outcome,
    ended: fn() => 'this one stopped before it got there',
);
```

**Three standings across two statuses, and neither half tells them apart alone.** Still going
and ended are both the `job` envelope; ended and finished are both `200`. Only the pair
separates them, which is why the reading is in `JobStanding` rather than at each call site —
and why `answering()` takes an arm for each and has no default. Work that ended was released
by name or let go for want of anybody asking; a client that read it as still going would poll
a name nothing is doing, and one that read it as a stack it could not reach would report a
machine as broken when it answered perfectly.

Pass the arms by name. The two that take nothing are alike enough that position is a poor way
to tell them apart, and this repository's own analyser forbids named arguments in its own call
sites, so its tests read positionally where yours should not.

A name is also the handle work is stopped by, since a screen has nothing to interrupt with:

```php
$client->letGoOf($job);   // ends it, and says where it now stands
```

A name does not outlive the run that minted it. One carried across a break in the connection
may name nothing by the time it is asked about, which arrives as `NoSuchJob` rather than as a
stack that could not be reached — and is a reason to redeem a name promptly. Asking after a
name is a read: the work it names is work lemonfiber acknowledged, and asking again cannot
start a second one.

Work that stopped on a problem is not one of the three standings. It answers with the `error`
envelope at whatever status that problem warrants, and arrives as `RequestFailed` carrying the
sentence lemonfiber wrote — including where that status is `404`, which is why a name nobody
minted is told apart by the media type rather than by the status.

What an envelope holds is shaped by its `kind`, so it is reached through the kind rather than
as an open value (ARCH-R63). There is one generated class per kind, and it is the way through:

```php
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\StatusEnvelope;

$envelope = $client->read('/api/status');   // Envelope<mixed>

if ($envelope->kind === Kind::Status->value) {
    $status = StatusEnvelope::in($envelope);  // Envelope<the shape the contract gives `status`>

    $status->data;   // typed by that shape, and checked by static analysis
}
```

`StatusEnvelope::in()` refuses an envelope carrying any other kind rather than handing back a
payload of the wrong shape.

## Logs, as a window rather than a stream

`/api/logs` is the one read whose answer is not a single envelope: it renders a `log` envelope
per line. It is asked for through a type rather than through a path and a query, and that type
takes a service and a number of lines and nothing else.

```php
use Lemonfiber\Sdk\Logs;

$window = $client->logs(Logs::ofService('sonarr', 200));

$window->service();          // 'sonarr'
$window->bound();            // 200 — how many lines were asked for
$window->count();            // how many came back
$window->reachedTheBound();  // whether the view stops where it was told to

foreach ($window->lines() as $line) {
    $line->data['line'];     // typed by the shape the contract gives `log`
}
```

There is no default line count. How many lines to show is a decision about somebody's screen and
this client owns nobody's screen; lemonfiber holds the ceiling and names it in its refusal, so no
copy of that figure lives here.

`follow` is absent rather than offered and declined. Asking lemonfiber to keep reading is not this
request with a flag on it — the answer stops being lines and becomes a name for work that will not
end, with the lines arriving on the event stream instead.

**Nothing on the wire says how much was left behind.** A window is so many `log` envelopes and then
the end of the body: no total, no cursor, no mark where the gathering stopped. What
`reachedTheBound()` answers is the size asked for against the number that arrived — as many as were
asked for means the view stops at the bound and what is behind it is unknown; fewer means the bound
cut nothing, which is not a claim that this is everything the service ever said.

Live updates arrive as envelopes. Anything gathered before a break in the connection is marked
out of date rather than shown as current:

```php
$feed = $client->events(heartbeat: Duration::ofSeconds(15));

foreach ($feed->follow() as $envelope) {
    $held = $feed->held()->get('status');

    $held?->isStale();   // true once the connection has broken and been reopened
}
```

## Where the contract comes from

Shapes are generated. `src/Generated/` holds types produced from `web-api.contract.json`, the
artefact lemonfiber builds from the `serde` types it serialises with (ADR-0014, ARCH-R56,
ARCH-R58). Nothing in that directory is edited by hand.

A copy of the artefact is vendored here, beside the revision it came from, so generation
needs no network and a contract change arrives as a diff somebody reads (ARCH-R65). Three
commands, and only the first touches the network:

| Command | Network | What it does |
|---|---|---|
| `composer contract:sync -- v1.0.0` | yes | Fetches the artefact at that revision — a release tag or a full commit hash — checks it is one, and vendors it into `contract/` beside the revision it came from |
| `composer contract:generate` | no | Writes `src/Generated/` from the vendored copy. Deterministic; its output is committed |
| `composer contract:check` | no | Regenerates and fails on any diff. Part of `composer ci`, so CI fails on a stale `src/Generated` (ARCH-R66) |

`contract/VERSION` names the revision the vendored copy came from.

Generation refuses an artefact whose `api_version` this package does not implement, naming both
versions and writing nothing (ARCH-R67). Types that compile and lie are worse than a build that
stops.

`Contract::API_VERSION` comes from the artefact, and `Api::VERSION` comes from that, so the wire
version is stated once rather than repeated by hand.

Everything else in `src/` is behaviour no schema expresses:

| Written by hand | What it holds to |
|---|---|
| `Http\RunToken` | The per-run token travels in a header, never in an address (ARCH-R52) |
| `Repair` | The offer and the yes are one request read twice, and the arrangements the surface refuses cannot be written (N2-R4, N2-R5, N2-R6) |
| `JobStanding` | Still going, finished and ended are three standings across two statuses, and none of them is a fall-through |
| `Http\BaseUrl` | Loopback, or an address a certificate pin vouches for; any other host is refused before anything is sent, and a loopback address is not refused for being named rather than numeric (ARCH-R60, ARCH-R99) |
| `Envelope\EnvelopeReader` | A version mismatch is refused plainly, naming both versions, rather than rendering part of an answer (ARCH-R55) |
| `Envelope\Payload` | An envelope is read as the kind it carries, or not at all (ARCH-R63) |
| `Logs`, `LogWindow` | The logs are a bounded read that names its service and states its own edge (N2-R10) |
| `Events\EventStream` | A stream quiet for twice the agreed heartbeat is reported as broken, not as calm; one missed beat is not (ARCH-R61) |
| `Events\HeldValues` | Values gathered before a reconnection gap are marked out of date (ARCH-R51) |
| `Exception\RequestFailed` | A refusal carries the sentence lemonfiber answered with, read back through `said()`; an answer carrying none names the endpoint and the status instead (G4-R1). Where the answer was an `error` envelope, `refusal()` carries the whole problem document — code, severity, state, summary, meaning, remedies, detail and cause — with anything left out left absent. `detail` quotes what a service said with recognised secrets withheld, best effort, so it is fit to show and not to forward, and it is never part of the message |
| `Exception\CertificateWasRefused` | A pinned peer presenting another certificate is told apart from silence, carrying the digest it presented and the one it was pinned to (ARCH-R99) |
| `Exception\Unreachable` | A request nothing answered is one of this client's problems wherever it was sent, carrying the endpoint and the connection's reason with every address in it cut back to where it points |
| `Exception\*` | The error model, in plain language (G2, G4) |

The package carries semver. `api_version` is a separate integer describing the wire
(ARCH-R46). Many package versions may speak one wire version.

## Quality bar

Every gate below is a merge gate. `composer ci` runs all of them but the last. Backward
compatibility is its own script and its own CI job: it needs a checker installed separately
(`composer bin bc install`) and a released tag to compare against.

`composer install` also turns on this repository's pre-push hook, which refuses a push that
would leave a branch carrying no commit `origin/main` does not — what pushing the trunk over a
feature branch looks like. `composer update` does it too. A clone nobody has installed into has
no hook: it is `git config core.hooksPath .githooks`, per clone, and git cannot read
`.githooks/` on its own.

| Gate | Command | Threshold |
|---|---|---|
| Formatting | `composer lint` | Pint, `per` preset plus strict rules, zero diffs |
| Static analysis | `composer analyse` | PHPStan level max, strict rules, deprecation rules, ergebnis rules, 100% type coverage |
| Dead idioms | `composer refactor` | Rector dry run, zero changes |
| Repository guards | `composer guards` | No suppressions, no file over 550 lines, no address off this machine, no reasoning in comments |
| Dependencies | `composer deps` | `validate --strict`, `normalize`, `audit`, no unused or undeclared packages |
| Contract types | `composer contract:check` | Regeneration produces no diff |
| Tests | `composer test:coverage` | 100% line coverage |
| Mutation testing | `composer test:mutation` | 100% mutation score |
| Backward compatibility | `composer bc` | Roave, against the newest `v*` tag. There are none yet, so the CI job skips both its steps and passes having compared nothing |

`src/Generated/` is skipped by Pint, PHPStan, Rector, the guards and both test gates. Generated
code is proved by regeneration producing no diff, not by passing a linter; everything that uses
it is analysed as usual.

There is no PHPStan baseline and no `ignoreErrors`. `@phpstan-ignore`, `@codeCoverageIgnore`,
`@SuppressWarnings` and their relatives are rejected by `scripts/guards.php`, which reads comments
through PHP's own tokeniser.

**Pest 5** rather than PHPUnit directly: it carries a coverage threshold (`--min`) and mutation
testing as first-class flags, so both gates are the runner's own exit code rather than a script
parsing a report.

**Pint** rather than PHP-CS-Fixer directly: same engine, one configuration file, and the `per`
preset with explicit strict rules on top.

**Pest's mutation testing** rather than Infection: Infection 0.35 generates a PHPUnit 9 era
configuration that PHPUnit 13 rejects, so it cannot run on this toolchain.

## Comments

Comments state what a thing is or does. Reasoning, history and justification belong in an ADR in
the spec repository, not in source. `scripts/guards.php` fails any comment line opening with
`because`, `the reason`, `this is why`, `originally`, `it turns out`, `note that`, `arguably`,
or `we` as a word.

## Licence

Hippocratic License 3.0 (HL3-CORE). See [LICENSE](LICENSE).
