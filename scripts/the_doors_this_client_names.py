"""The contract page names the reads, and this client holds a path for each.

The contract this package vendors carries envelope kinds and **no endpoints**, so
every path is knowledge written by hand in `src/Contract/Api.php`. Nothing read
the two against each other, and the gap is not hypothetical: `held` and `config`
were both generated as envelope types, shipped, and reachable by nobody, since
the type existed and the door did not. A caller cannot spell the path itself —
that is the whole point of the class — so a missing constant makes a read
unreachable rather than inconvenient.

`lemonfiber/scripts/reads_match_the_contract.py` is this check's sibling and
holds the server to the same block. Between them the page is read from both
ends: a route nothing here can reach, and a constant nothing there serves.

The spec is a different repository and this one does not vendor it, so the page
arrives as an argument. CI checks it out beside the tree under review; by hand
it is whatever spec clone is at hand:

  python3 scripts/the_doors_this_client_names.py --spec ../spec

Reading nothing is a failure, not a pass. A block that stopped matching the
shape this parses would otherwise agree with a client holding no paths at all.
"""

from __future__ import annotations

import argparse
import pathlib
import re
import sys

# Where the paths are written, relative to the repository root.
NAMES = pathlib.Path("src/Contract/Api.php")

# The page, relative to a spec checkout, and the heading the block sits under.
PAGE = pathlib.Path("20-architecture/contracts/web-api.md")
HEADING = "## Reading"

# A path this client holds, and the constant holding it.
CONSTANT = re.compile(r"const string ([A-Z][A-Z0-9_]*_ENDPOINT) = '(/api/[^']*)';")

# One entry of the block. The path only: an entry carrying `?…` says the
# endpoint takes parameters, which is not part of the path it is served on.
ENTRY = re.compile(r"GET (/api/[a-z0-9{}/-]+)")

# A fenced block, however the fence is labelled.
FENCE = re.compile(r"```[a-z]*\n(.*?)```", re.DOTALL)

# A trailing `{…}` segment, which the client holds as a base plus a method
# composing the name onto it — the arrangement `/api/actions` and `/api/jobs`
# already have. `/api/bundle/{name}` is matched by `BUNDLE_ENDPOINT` and
# `Api::bundle()`, so the segment is stripped before the two are compared.
TEMPLATED = re.compile(r"/\{[^}]+\}$")

# Doors this client holds a path for that the reading block does not name, each
# with what it is instead. The block is about reads; these three are not reads,
# and each is its own section of the same page.
NOT_A_READ = {
    "/api/events": "the live stream, which has its own section",
    "/api/actions": "the one door every action is asked for through",
    "/api/jobs": "where work already begun is asked about and released",
}

# Below this a reading has found the wrong text rather than a smaller surface.
# A floor under the *reading* rather than a count of the doors, so it trails the
# real number deliberately and a door genuinely retired need not argue with it.
FEWEST = 25


def held(root: pathlib.Path) -> tuple[dict[str, str], list[str]]:
    """Every path this client holds, by the constant holding it."""
    source = root / NAMES
    if not source.is_file():
        return {}, [f"no contract class at {source} — this is looking in the wrong place"]

    found: dict[str, str] = {}
    twice: list[str] = []
    for name, path in CONSTANT.findall(source.read_text(encoding="utf-8")):
        if path in found.values():
            twice.append(
                f"{source}: `{name}` holds `{path}`, which another constant already "
                "holds — one of the two is a door nothing will ever reach through it"
            )
        found[name] = path
    return found, twice


def named(spec: pathlib.Path) -> tuple[set[str], list[str]]:
    """Every endpoint the block names, and anything unreadable about the page."""
    page = spec / PAGE
    if not page.is_file():
        return set(), [f"no contract page at {page} — is --spec a spec checkout?"]

    text = page.read_text(encoding="utf-8")
    if HEADING not in text:
        return set(), [f"{page} has no `{HEADING}` heading"]

    section = text.split(HEADING, 1)[1].split("\n## ", 1)[0]
    blocks = FENCE.findall(section)
    if not blocks:
        return set(), [f"{page}: nothing is fenced under `{HEADING}`"]

    return {
        TEMPLATED.sub("", entry) for entry in ENTRY.findall("\n".join(blocks))
    }, []


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--spec", type=pathlib.Path, default=pathlib.Path(".spec-canonical")
    )
    parser.add_argument("--repo", type=pathlib.Path, default=pathlib.Path("."))
    args = parser.parse_args()

    constants, problems = held(args.repo)
    entries, unreadable = named(args.spec)
    problems.extend(unreadable)

    paths = set(constants.values())

    for count, what, where in (
        (len(paths), "paths", str(args.repo / NAMES)),
        (len(entries), f"endpoints under `{HEADING}`", str(args.spec / PAGE)),
    ):
        if count < FEWEST:
            problems.append(
                f"read {count} {what} from {where}, fewer than the {FEWEST} this "
                "client has never gone below — the text has changed shape and this "
                "is no longer reading it"
            )

    unreachable = sorted(entries - paths)
    if unreachable:
        problems.append(
            f"`{HEADING}` in {PAGE} names these and this client holds no path for "
            f"them, so nothing can reach them: {', '.join(unreachable)}"
        )

    unnamed = sorted(
        path for path in paths - entries if path not in NOT_A_READ
    )
    if unnamed:
        problems.append(
            "this client holds these paths and the contract page does not name "
            f"them — add them to `{HEADING}` in {PAGE}, or say here what they are "
            f"instead of a read: {', '.join(unnamed)}"
        )

    if problems:
        for problem in problems:
            print(f"::error::{problem}", file=sys.stderr)
        return 1

    print(f"this client holds a path for each of the {len(entries)} reads the contract page names")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
