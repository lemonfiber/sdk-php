# Generated contract types

Everything in this directory is written by `composer contract:generate` from
`contract/web-api.contract.json`, the artefact `lemonfiber` produces from the
`serde` types it serialises with (ADR-0014, ARCH-R56, ARCH-R58).

**Nothing here is edited by hand.** A change made here is lost on the next
generation, and a shape written here by hand is a second source of truth for
something that already has one.

| File | What it holds |
|---|---|
| `Contract.php` | The `api_version` these types were generated from, and the revision they came from |
| `Kind.php` | Every kind the contract describes |
| `RefusalCode.php` | Every code the contract lists a refusal as carrying |
| `RefusalStatus.php` | The status each code is answered with, which `RefusalCode::status()` reads |
| `RefusalDescription.php` | The registry's line about each code, which `RefusalCode::description()` reads |
| `<Kind>Envelope.php` | One class per kind: the kind it reads, and the payload type the contract gives it |

Pint, PHPStan, Rector and the coverage and mutation gates skip this directory,
and the repository guards hold it to the line cap and to nothing else; the
generator writes nothing where a file would hold more lines than that. Its
correctness is proved by regeneration producing no diff — `composer contract:check`, run in CI (ARCH-R66) — not by
passing a linter. Everything that *uses* these types is analysed as usual, so a
generated type that does not fit its callers still fails the build.

Hand-written code in this package covers behaviour no schema expresses:
envelope reading, the loopback rule, the token header, heartbeat detection,
resumption, and staleness across a gap.
