# lemonfiber/sdk-php

The PHP client for [lemonfiber](https://github.com/lemonfiber/lemonfiber)'s local HTTP API.

`sdk-php` is a peer of [`sdk-ts`](https://github.com/lemonfiber/sdk-ts). Both implement the same
specification; neither defines it. **The [spec](https://github.com/lemonfiber/spec) is the
reference** — where this client disagrees with the contract, this client is wrong.

## Install

```sh
composer require lemonfiber/sdk-php
```

Requires PHP 8.5. The only runtime dependency is [Saloon 4](https://docs.saloon.dev), plus the
PSR-7 interfaces it already brings.

## Use

lemonfiber prints a token each time it starts. Pass it in; the client sends it as a header and
never puts it in an address.

```php
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Time\Duration;

$client = Client::onPort(9000, $tokenLemonfiberPrinted);

$status = $client->read('/api/status');
$status->kind;        // 'status'
$status->data;        // the payload, shaped by kind

$client->act('/api/actions/retry-import', ['service' => 'sonarr']);
```

What an envelope holds is shaped by its `kind`, so it is reached through the kind rather than
as an open value (ARCH-R63). There is one generated class per kind, and it is the way through:

```php
use Lemonfiber\Sdk\Generated\Kind;
use Lemonfiber\Sdk\Generated\LogEnvelope;

$envelope = $client->read('/api/logs');   // Envelope<mixed>

if ($envelope->kind === Kind::Log->value) {
    $log = LogEnvelope::in($envelope);    // Envelope<the shape the contract gives `log`>

    $log->data;   // typed by that shape, and checked by static analysis
}
```

`LogEnvelope::in()` refuses an envelope carrying any other kind rather than handing back a
payload of the wrong shape.

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

A copy of the artefact is vendored here, beside the release tag it came from, so generation
needs no network and a contract change arrives as a diff somebody reads (ARCH-R65). Three
commands, and only the first touches the network:

| Command | Network | What it does |
|---|---|---|
| `composer contract:sync -- v0.9.0` | yes | Fetches that release's artefact, checks it is one, and vendors it into `contract/` with its tag |
| `composer contract:generate` | no | Writes `src/Generated/` from the vendored copy. Deterministic; its output is committed |
| `composer contract:check` | no | Regenerates and fails on any diff. Part of `composer ci`, so CI fails on a stale `src/Generated` (ARCH-R66) |

`contract/VERSION` names the release the vendored copy came from.

Generation refuses an artefact whose `api_version` this package does not implement, naming both
versions and writing nothing (ARCH-R67). Types that compile and lie are worse than a build that
stops.

`Contract::API_VERSION` comes from the artefact, and `Api::VERSION` comes from that, so the wire
version is stated once rather than repeated by hand.

Everything else in `src/` is behaviour no schema expresses:

| Written by hand | What it holds to |
|---|---|
| `Http\RunToken` | The per-run token travels in a header, never in an address (ARCH-R52) |
| `Http\BaseUrl` | Loopback only; any other host is refused before anything is sent (C6-R1, C6-R14) |
| `Envelope\EnvelopeReader` | A version mismatch is refused plainly, naming both versions, rather than rendering part of an answer (ARCH-R55) |
| `Envelope\Payload` | An envelope is read as the kind it carries, or not at all (ARCH-R63) |
| `Events\EventStream` | A stream that goes quiet longer than the agreed heartbeat is reported as broken, not as calm (ARCH-R50) |
| `Events\HeldValues` | Values gathered before a reconnection gap are marked out of date (ARCH-R51) |
| `Exception\*` | The error model, in plain language (G2, G4) |

The package carries semver. `api_version` is a separate monotonic integer describing the wire
(ARCH-R2). Many package versions may speak one wire version.

## Quality bar

Every gate below is a merge gate. `composer ci` runs all of them.

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
| Backward compatibility | `composer bc` | Roave, against the last released tag |

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
`because`, `we `, `the reason`, `this is why`, `originally`, `it turns out`, `note that` or
`arguably`.

## Licence

Hippocratic License 3.0 (HL3-CORE). See [LICENSE](LICENSE).
