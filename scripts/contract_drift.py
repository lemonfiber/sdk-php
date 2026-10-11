"""The vendored contract, compared with the one a revision of lemonfiber holds.

`contract-bump` asks whether the copy under `contract/` is the contract
lemonfiber's main holds; `contract-drift` asks the same through spec's shared
`upstream-drift`. The revision arrives as
the archive of its tree, and whichever layout it holds is the one compared:
the directory `contract/web-api/` where it has an index, the single file
`contract/web-api.contract.json` otherwise. Two copies agree when they hold the
same files and each file parses to the same JSON, so a copy in the other
layout differs in every file.

Run from the root of the tree holding the copy, with the revision's archive
beside it as `lemonfiber.tar.gz`:

  python3 scripts/contract_drift.py

Exits 0 when the copies agree, 1 when they differ, naming each file that does,
and 2 when either cannot be read.
"""

from __future__ import annotations

import argparse
import json
import pathlib
import sys
import tarfile

FILE = "contract/web-api.contract.json"
DIRECTORY = "contract/web-api"
INDEX = "index.json"
SERVED = "lemonfiber.tar.gz"


class Unreadable(Exception):
    """A copy that cannot be read as a contract."""


def served_by(archive: pathlib.Path) -> dict[str, object]:
    """Every contract file the archive holds, keyed by its path in the tree."""
    directory: dict[str, object] = {}
    single: dict[str, object] = {}

    try:
        with tarfile.open(archive, "r:gz") as tar:
            for member in tar.getmembers():
                if not member.isfile():
                    continue
                _, _, inside = member.name.partition("/")
                if inside.startswith(DIRECTORY + "/"):
                    directory[inside] = parsed(tar, member)
                elif inside == FILE:
                    single[inside] = parsed(tar, member)
    except (OSError, tarfile.TarError) as error:
        raise Unreadable(f"{archive} is not an archive this can read: {error}") from error

    if directory:
        if f"{DIRECTORY}/{INDEX}" not in directory:
            raise Unreadable(f"the revision holds {DIRECTORY}/ with no {INDEX} in it")
        return directory
    if single:
        return single
    raise Unreadable(f"the revision holds neither {DIRECTORY}/{INDEX} nor {FILE}")


def parsed(tar: tarfile.TarFile, member: tarfile.TarInfo) -> object:
    handle = tar.extractfile(member)
    if handle is None:
        raise Unreadable(f"{member.name} could not be read from the archive")
    try:
        return json.loads(handle.read())
    except ValueError as error:
        raise Unreadable(f"{member.name} is not JSON: {error}") from error


def vendored_in(repo: pathlib.Path) -> dict[str, object]:
    """Every file of the vendored copy, keyed by its path in the tree.

    A vendored file that is not JSON is kept as its bytes, so it differs from
    whatever the revision holds rather than stopping the comparison.
    """
    if (repo / DIRECTORY / INDEX).is_file():
        paths = sorted(p for p in (repo / DIRECTORY).rglob("*") if p.is_file())
    elif (repo / FILE).is_file():
        paths = [repo / FILE]
    else:
        raise Unreadable(f"there is no vendored contract at {DIRECTORY}/ or {FILE}")

    copy: dict[str, object] = {}
    for path in paths:
        raw = path.read_bytes()
        try:
            copy[path.relative_to(repo).as_posix()] = json.loads(raw)
        except ValueError:
            copy[path.relative_to(repo).as_posix()] = raw
    return copy


def differing(ours: dict[str, object], theirs: dict[str, object]) -> list[str]:
    """Every path one copy holds and the other does not, or holds as other JSON."""
    return sorted(
        path
        for path in ours.keys() | theirs.keys()
        if path not in ours or path not in theirs or ours[path] != theirs[path]
    )


def main() -> int:
    argparse.ArgumentParser(description=__doc__.splitlines()[0]).parse_args()

    repo = pathlib.Path.cwd()

    try:
        ours = vendored_in(repo)
        theirs = served_by(repo / SERVED)
    except Unreadable as error:
        print(f"::error::{error}")
        return 2

    changed = differing(ours, theirs)

    if changed:
        print(f"{len(changed)} files differ:")
        for path in changed:
            print(f"  {path}")
    else:
        print("The vendored contract is the one the revision holds.")

    return 1 if changed else 0


if __name__ == "__main__":
    sys.exit(main())
