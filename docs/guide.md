# Using the PHP client

This guide covers everything `lemonfiber/sdk-php` does. The
[README](../README.md) has the install steps and a first call.

## Connecting

Every client is built with the longest a call may wait. There is no default.

### On the same machine

`lemonfiber ui` prints a token for each run. Give the client the port and that
token:

```php
use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Time\Duration;

$client = Client::onPort(9000, $token, Duration::ofSeconds(10));
```

`Client::at('http://localhost:9000', $token, $wait)` does the same from an
address. It refuses any address that is not on this machine.

### From another machine

Over the network the client must be given the stack's certificate fingerprint,
and the address must be `https`:

```php
$client = Client::pinnedAt('https://nas.local:9000', $token, $fingerprint, Duration::ofSeconds(10));
```

`$fingerprint` is the SHA-256 digest of the stack's certificate, 64 hexadecimal
characters. `lemonfiber ui --lan --tls --port 9000` prints it when it starts (`--lan`
needs a password set first, with `--set-password`). The
connection is refused during the TLS handshake if the stack presents any other
certificate, before anything is written. There is no way to turn the check off.

The token for a remote client is a session or an integration key, not the
per-run token. To exchange a password for a session:

```php
use Lemonfiber\Sdk\Admission;

$admitted = Admission::at('https://nas.local:9000', $fingerprint, Duration::ofSeconds(10))
    ->open($password);              // the operator's password
// ->openAs('ada', $password)       // or a household member's name and password

$admitted->token;               // what the client carries from here on
$admitted->untilEpochSeconds;   // when the session ends
$admitted->member;              // the member's name, or null for the operator
```

A wrong password raises `PasswordWasRefused`, and too many of them
`TooManyAttempts`. `Admission::onPort($port, $wait)` is the same door on this
machine.

The token travels in the `X-Lemonfiber-Token` header and never in an address. A
request only ever goes to the scheme, host and port the client was built for: a
redirect is not followed, and raises `Unreachable`.

## Reading

`Api` holds a constant for the path of every read lemonfiber serves:

```php
use Lemonfiber\Sdk\Contract\Api;

$status = $client->read(Api::STATUS_ENDPOINT);
$status->kind;   // 'status'
$status->data;   // the payload

$client->read(Api::REQUESTS_ENDPOINT, ['member' => 'ada']);
```

Every envelope also carries `host`: the machine the answer is about, where it is
not the one lemonfiber runs on, and null where it is.

Each read is documented where it is declared, with the parameters it takes and the
kind it answers with: the machine's reads in
[`MachineReads`](../src/Contract/MachineReads.php) and the household's in
[`HouseholdReads`](../src/Contract/HouseholdReads.php). A read refuses a
parameter it does not know rather than dropping it, because a dropped filter
answers a wider question than the one you asked. A list value sends the parameter
once for each item.

## Typed payloads

There is one generated class per kind. It checks the kind and gives the payload
the type the contract describes, so static analysis can check how you use it:

```php
use Lemonfiber\Sdk\Generated\StatusEnvelope;

$status = StatusEnvelope::in($client->read(Api::STATUS_ENDPOINT));

$status->data['condition'];      // 'inactive' | 'degraded' | 'partial' | 'active'
$status->data['active_forms'];   // list<string>
```

`StatusEnvelope::in()` raises `UnexpectedKind` for an envelope of any other kind.
`Generated\Kind` lists every kind.

## Acting

An action's name and its arguments are the command line's own. `Api::action()`
builds the path:

```php
$client->act(Api::action('restart'), ['forms' => ['tv'], 'services' => ['sonarr']]);
```

lemonfiber refuses a name it does not offer and a field the action does not take.
An action is sent once and never retried, because a second sending would be a
second change.

### Repairs

`repair` has a method of its own, because the offer and the agreement are the same
request read twice:

```php
use Lemonfiber\Sdk\Repair;

$offer = $client->repair(Repair::offer());   // says what each repair would do; does nothing
$client->repair(Repair::agreedTo($agreement, 'vpn.killswitch'));   // carries out that one
```

`$agreement` is the `agreement` field of the `repair` envelope the offer came back
with. lemonfiber looks again before it acts and refuses an agreement whose offer
has changed since. `Repair::agreedInAdvance()` is standing consent, the
command line's `--yes`.

### Work that outlives the request

An action that reaches the services answers with the name of a job rather than
its outcome. Ask what became of it with `whatBecameOf()`, and stop it with
`letGoOf()`. Both answer with a `JobStanding`:

```php
use Lemonfiber\Sdk\Envelope\Envelope;

$client->whatBecameOf($job)->answering(
    stillRunning: fn() => 'ask again in a moment',
    finished: fn(Envelope $outcome) => $outcome,
    ended: fn() => 'it stopped before it got there',
);
```

Pass the three arms by name: two of them take nothing, and position is an easy
way to mix them up. A job name only lives as long as the lemonfiber run that made
it; asking about one that run never made raises `NoSuchJob`. A job that stopped
on a problem raises `RequestFailed` with lemonfiber's own sentence.

## Logs

`/api/logs` answers with one `log` envelope per line, so it has a method of its
own. Ask for one service and a number of lines; there is no default:

```php
use Lemonfiber\Sdk\Logs;

$window = $client->logs(Logs::ofService('sonarr', 200));

$window->count();            // how many lines came back
$window->reachedTheBound();  // true when as many came back as were asked for

foreach ($window->lines() as $line) {
    echo $line->data['line'], PHP_EOL;
}
```

Nothing in the answer says how much was left out. `reachedTheBound()` only
compares what you asked for with what arrived. To keep reading new lines, follow
the event stream instead.

## Support bundles

```php
$file = $client->bundle($name);   // the name lemonfiber wrote the bundle under

$file->bytes();         // the file, as delivered
$file->contentType();   // 'application/gzip'
```

## Live updates

```php
$feed = $client->events(heartbeat: Duration::ofSeconds(15));

foreach ($feed->follow() as $envelope) {
    $held = $feed->held()->get('status');
    $held?->isStale();   // true once the connection broke, until the stream carries it again
}
```

The server sends a heartbeat every 15 seconds. Silence for twice the heartbeat
counts as a broken connection, and one missed beat does not. A broken connection
is reopened from the last event it carried, up to five times in a row
(`reconnectLimit`). Values gathered before the break are marked stale rather than
shown as current.

## Errors

Every exception this client raises implements `Exception\Problem`. The ones you
will handle most:

```php
use Lemonfiber\Sdk\Exception\RequestFailed;
use Lemonfiber\Sdk\Exception\Unreachable;

try {
    $client->read(Api::STATUS_ENDPOINT);
} catch (RequestFailed $refused) {
    $refused->status();   // lemonfiber answered, and said no
    $refused->code();     // why, as a RefusalCode, or null
    $refused->said();     // lemonfiber's sentence, for a person
} catch (Unreachable $silence) {
    $silence->endpoint(); // '/api/status'
    $silence->why();      // a WhyNothingAnswered
}
```

| Exception               | Meaning                                                                                                                   |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `RequestFailed`         | lemonfiber answered and refused. The connection and address are fine                                                      |
| `Unreachable`           | Nothing answered: the stack is stopped or out of reach, the connection was refused or broke, or the wait ran out          |
| `CertificateWasRefused` | Something answered at a pinned address with a different certificate. `presented()` and `pinned()` give both digests       |
| `ConfigurationProblem`  | An address, port, token or fingerprint you passed cannot be used. Nothing was sent                                        |
| `ApiVersionMismatch`    | The answer is in an `api_version` this package does not speak; the message names both                                     |
| `UnreadableResponse`    | What arrived was not a lemonfiber envelope                                                                                |
| `UnexpectedKind`        | A typed envelope class was handed another kind                                                                            |
| `NoSuchJob`             | The job name was not made by this run of lemonfiber                                                                       |
| `PasswordWasRefused`    | `Admission` was given a wrong password                                                                                    |
| `TooManyAttempts`       | Too many wrong passwords lately                                                                                           |
| `StreamInterrupted`     | The event stream broke and could not be reopened                                                                          |

`Unreachable::why()` says how the request met nothing, so each case can get its
own remedy: `NameNotFound`, `Refused` (something at the address turned the
connection away), `NoRoute` (what an address a machine has moved away from looks
like), `TimedOut` or `Other`. `reason()` is the connection's own words, with every
address cut back to its scheme, host, port and path.

`Unreachable` does not prove a request went unheard. An action whose answer was
lost on the way back may have been applied, so read the stack's state before
acting again.

A read is retried up to twice when nothing answered or a gateway could not reach
the stack, within the one wait the client was built with.

### Refusal codes

Decide what a refusal means from `code()`, never from `said()`: the sentence is
for people and may be reworded, and one HTTP status covers several refusals with
different remedies. `Generated\RefusalCode` has a case for each code the contract
lists, with the status it is answered with (`status()`). A code this package does
not know, as a newer lemonfiber may send, gives a null `code()`; read the refusal
by `status()` alone. `refusal()` gives the whole problem document: code,
severity, summary, meaning, remedies and detail. The codes are listed in
[every error by code](https://docs.lemonfiber.app/fixing/every-error-by-code/).

## Testing without a stack

Give the client or the door a Saloon `MockClient`, and nothing reaches the
network:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

$mock = new MockClient([
    MockResponse::make(
        ['api_version' => 1, 'kind' => 'status', 'data' => ['active_forms' => ['library'], 'condition' => 'active']],
        200,
        ['Content-Type' => 'application/json'],
    ),
]);

$client = Client::onPort(9000, 'a-token', Duration::ofSeconds(10))->withMockClient($mock);
```

## Two version numbers

Package versions follow semver. The wire has `api_version`, a whole
number that only goes up; `Api::VERSION` is the one this package speaks, and many
package versions can speak the same one. See
[two version numbers](https://docs.lemonfiber.app/api/two-version-numbers/).

## Where the types come from

Everything under `src/Generated/` is generated from
`contract/web-api.contract.json`, which lemonfiber builds from the Rust types
that produce its answers. Nothing there is edited by hand.
`contract/VERSION` names the lemonfiber commit the copy came from.

| Command                                     | Network | What it does                                                         |
| ------------------------------------------- | ------- | -------------------------------------------------------------------- |
| `composer contract:sync -- <tag-or-commit>` | yes     | Vendors the contract at that revision of lemonfiber into `contract/` |
| `composer contract:generate`                | no      | Rewrites `src/Generated/` from the vendored copy                     |
| `composer contract:check`                   | no      | Regenerates and fails on any difference                              |

Generation refuses a contract whose `api_version` this package does not speak,
and writes nothing.
