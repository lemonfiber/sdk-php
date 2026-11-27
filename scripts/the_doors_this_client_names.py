"""The contract page names the reads, and this client holds a path for each.

This package generates no path from the contract it vendors, so every path is
written by hand in `src/Contract/`. A caller does not spell a path itself, so a
read whose envelope type is generated and whose constant is missing is
unreachable.

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
import json
import pathlib
import re
import sys

# Where the paths are written, relative to the repository root: `Api` and the
# interfaces holding its reads, which it implements.
NAMES = pathlib.Path("src/Contract")

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

# Every route the vendored contract serves, relative to the repository root. A
# path this client holds that the reading block does not name is one of these,
# served at another method or as the base a `{…}` segment is composed onto.
ROUTES = pathlib.Path("contract/web-api/routes.json")

# Below this a reading has found the wrong text rather than a smaller surface.
# A floor under the *reading* rather than a count of the doors, so it trails the
# real number deliberately and a door genuinely retired need not argue with it.
FEWEST = 25


def held(root: pathlib.Path) -> tuple[dict[str, str], list[str]]:
    """Every path this client holds, by the constant holding it."""
    folder = root / NAMES
    sources = sorted(folder.glob("*.php")) if folder.is_dir() else []
    if not sources:
        return {}, [f"no contract class in {folder} — this is looking in the wrong place"]

    found: dict[str, str] = {}
    twice: list[str] = []
    for source in sources:
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


def served(root: pathlib.Path) -> tuple[set[str], list[str]]:
    """Every path the vendored contract serves a route at, and each one's base before a `{…}`."""
    listing = root / ROUTES
    try:
        routes = json.loads(listing.read_text(encoding="utf-8"))
    except (OSError, ValueError):
        routes = None
    if not isinstance(routes, list):
        return set(), [f"no list of routes at {listing} — vendor the contract first"]
    paths = {route["path"] for route in routes if isinstance(route, dict) and "path" in route}
    return paths | {TEMPLATED.sub("", path) for path in paths}, []


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
    routes, unlisted = served(args.repo)
    problems.extend(unlisted)

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

    unnamed = sorted(paths - entries - routes)
    if unnamed:
        problems.append(
            f"this client holds these paths, and neither `{HEADING}` in {PAGE} names "
            f"them nor {ROUTES} serves a route at them: {', '.join(unnamed)}"
        )

    if problems:
        for problem in problems:
            print(f"::error::{problem}", file=sys.stderr)
        return 1

    print(f"this client holds a path for each of the {len(entries)} reads the contract page names")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
