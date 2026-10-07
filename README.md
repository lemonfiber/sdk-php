# lemonfiber/sdk-php

A PHP client for the web API that [lemonfiber](https://github.com/lemonfiber/lemonfiber)
serves. For PHP applications that read or control a lemonfiber media stack, on the same
machine or, with the stack's certificate pinned, from another one.

It gives you a typed class for every kind of answer, typed exceptions for every failure,
and a live event stream that marks values from before a dropped connection as stale.

**Not on Packagist.** There is no release yet; install it from GitHub at a commit.

## Requirements

- PHP 8.5 with the `filter`, `json` and `openssl` extensions.
- lemonfiber, serving its web API with `lemonfiber ui`.

The runtime dependencies are [Saloon 4](https://docs.saloon.dev) and Guzzle 8.

## Install

Pin a commit from [the commit list](https://github.com/lemonfiber/sdk-php/commits/main):

```sh
composer config repositories.lemonfiber-sdk vcs https://github.com/lemonfiber/sdk-php
composer require "lemonfiber/sdk-php:dev-main#<commit>"
```

## Quick start

Start lemonfiber's web API. It prints the address and a token for this run:

```console
$ lemonfiber ui --port 9000 --no-browser
lemonfiber is serving at:
  http://[::1]:9000
  http://127.0.0.1:9000
…
The token for this run, which the page will ask you for:
  <token>
```

Then ask it how the stack is doing. Save this as `status.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Lemonfiber\Sdk\Client;
use Lemonfiber\Sdk\Contract\Api;
use Lemonfiber\Sdk\Generated\StatusEnvelope;
use Lemonfiber\Sdk\Time\Duration;

$client = Client::onPort(9000, (string) getenv('LEMONFIBER_TOKEN'), Duration::ofSeconds(10));

$status = StatusEnvelope::in($client->read(Api::STATUS_ENDPOINT));

echo $status->data['condition'], ' ', implode(', ', $status->data['active_forms']), PHP_EOL;
```

Run it with the token lemonfiber printed:

```console
$ LEMONFIBER_TOKEN=<token> php status.php
active library
```

That is the output with only the `library` form running and healthy. A form is a named
part of the stack, such as `library` or `tv`; see
[forms](https://docs.lemonfiber.app/running/forms-and-slices/).

A failure is an exception: `Unreachable` when nothing answered, `RequestFailed` when
lemonfiber answered and refused. Each carries a sentence you can show a person.

## Where to go next

- [The guide](docs/guide.md): connecting from another machine, signing in with a password,
  actions and repairs, jobs, logs, the live event stream, every exception and testing
  with a mock.
- [The web API](https://docs.lemonfiber.app/api/): the envelope every answer arrives in,
  every payload kind and the field-by-field reference.
- [The command reference](https://docs.lemonfiber.app/commands/every-command/): every
  read and action is a command, and takes the same arguments.
- [The changelog](CHANGELOG.md): every change, generated from the commit history by
  `composer changelog`.

## Contributing

```sh
composer install   # also turns on the git hooks
composer ci        # every gate CI runs except backward compatibility
```

The gates, and the rules they hold the code to, are in [AGENTS.md](AGENTS.md).
`src/Generated/` is generated from lemonfiber's contract and never edited by hand; the
[guide](docs/guide.md#where-the-types-come-from) says how to regenerate it. Every change
cites a requirement in the [specification](https://github.com/lemonfiber/spec); start with
the [contributing guide](https://github.com/lemonfiber/spec/blob/main/50-governance/contributing.md).

## Security

Report a vulnerability privately, as the
[security policy](https://github.com/lemonfiber/.github/blob/main/SECURITY.md) describes.
Do not open a public issue.

## Licence

[Hippocratic License 3.0](LICENSE) (HL3-CORE): source-available and ethical-source, not
OSI-approved. The
[licence rationale](https://github.com/lemonfiber/spec/blob/main/90-appendix/license-rationale.md)
explains what that means for you. Made by NightWorksIO.
