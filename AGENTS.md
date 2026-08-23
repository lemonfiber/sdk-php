# AGENTS.md — sdk-php

Guidance for any AI agent working in this repo.

> **Common rules for every lemonfiber repo are canonical in the spec:**
> [50-governance/ai-contributors.md](https://github.com/lemonfiber/spec/blob/main/50-governance/ai-contributors.md).
> Read them. This file is the `sdk-php`-specific header only.

## What this repo is

The PHP client for lemonfiber's local web API. A library with **no user interface
and no server**, built on [Saloon](https://docs.saloon.dev) for its HTTP layer.
Spec:
[`30-repos/sdk-php.md`](https://github.com/lemonfiber/spec/blob/main/30-repos/sdk-php.md)
and the
[web API contract](https://github.com/lemonfiber/spec/blob/main/20-architecture/contracts/web-api.md).

It is a **peer** of [`sdk-ts`](https://github.com/lemonfiber/sdk-ts), not a
translation of it. Both conform to the same specification; neither is the
reference for the other. A client that disagrees with the contract is wrong.

## The rules you cannot break

- **`src/Generated/` is not edited by hand.** It is produced from the vendored
  `contract/web-api.contract.json` (`ARCH-R56`, `ARCH-R58`). `composer
  contract:check` regenerates and fails on any diff. That directory is skipped by
  Pint, PHPStan, Rector, the guards and both test gates on purpose — generated
  code is proved by regeneration, not by passing a linter.
- **No suppressions.** No PHPStan baseline, no `ignoreErrors`, and
  `@phpstan-ignore`, `@codeCoverageIgnore`, `@SuppressWarnings` and their
  relatives are rejected by `scripts/guards.php`.
- **No dependency beyond the HTTP layer** without a recorded reason. A client
  library's dependency tree becomes every consumer's.
- **No rendering, no policy, no state beyond the stream.** A figure it has not
  been given is one it does not have.
- **The token is a header, never a URL** (`ARCH-R52`); **loopback only**, any
  other host refused before anything is sent (`C6-R1`).
- **Comments state what a thing is or does.** Reasoning, history and
  justification belong in an ADR in the spec, not in source. `scripts/guards.php`
  fails a comment line opening with `because`, `the reason`, `we` as a word, and
  their relatives.

## What is written and what is generated

Everything a schema can express is generated. What is written by hand is the
behaviour it cannot: heartbeat detection and resumption, marking values held
across a reconnect gap as stale, the version refusal that names both versions,
and the error model's wording. That is the part worth reviewing.

## Checks

```
composer ci       # every gate but one, in the order CI runs them
composer bc       # the one it leaves out: backward compatibility
composer test     # the suite alone
```

Every gate in `composer ci` is a merge gate, including 100% line coverage **and**
a 100% mutation score — coverage that kills no mutants proves only that lines ran.
Backward compatibility is a merge gate too, and sits outside `composer ci`: it
needs a released tag to compare against and a checker installed on its own
(`composer bin bc install`). There are no tags yet, so its CI job skips its steps
and passes having compared nothing.

## Before you open a PR

- `composer ci` is clean.
- Cite a spec identifier in a commit `Spec:` trailer and the PR body.
- Sign off every commit (`git commit -s`); the DCO gate fails without it.
- No AI attribution in commits, PR bodies, or comments.
