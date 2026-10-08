"""The vendored contract, compared with the one a revision of lemonfiber holds.

`contract-drift` and `contract-bump` both ask whether the copy under
`contract/` is the contract lemonfiber's main holds. The revision arrives as
the archive of its tree, and whichever layout it holds is the one compared:
the directory `contract/web-api/` where it has an index, the single file
`contract/web-api.contract.json` otherwise. Two copies agree when they hold the
same files and each file parses to the same JSON, so a copy in the other
layout differs in every file.

Run from the root of the tree holding the copy, with the revision's archive
beside it as `lemonfiber.tar.gz`:

  python3 scripts/contract_drift.py
  python3 scripts/contract_drift.py --head <sha> --report

Exits 0 when the copies agree, 1 when they differ, naming each file that does,
and 2 when either cannot be read.
"""

from __future__ import annotations

import argparse
import json
import os
import pathlib
import sys
import tarfile

FILE = "contract/web-api.contract.json"
DIRECTORY = "contract/web-api"
INDEX = "index.json"
STAMP = "contract/VERSION"
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


def described(copy: dict[str, object]) -> tuple[object, set[str]]:
    """The `api_version` a copy names and the kinds it describes."""
    head = copy.get(f"{DIRECTORY}/{INDEX}", copy.get(FILE))
    if not isinstance(head, dict):
        return None, set()
    kinds = head.get("kinds")
    return head.get("api_version"), set(kinds) if isinstance(kinds, dict) else set()


def report(repo: pathlib.Path, head: str, ours: dict[str, object], theirs: dict[str, object], changed: list[str]) -> None:
    """Says what `contract-drift` found, in the log and on the run page."""
    summary = os.environ.get("GITHUB_STEP_SUMMARY")
    said: list[str] = []

    def say(line: str = "") -> None:
        print(line)
        said.append(line)

    stamp = (repo / STAMP).read_text(encoding="utf-8").strip()

    if not changed:
        say("## The vendored contract is current")
        say()
        say(f"It matches the one lemonfiber `{head}` describes, vendored at `{stamp}`.")
    else:
        our_version, our_kinds = described(ours)
        their_version, their_kinds = described(theirs)
        missing = sorted(their_kinds - our_kinds)
        extra = sorted(our_kinds - their_kinds)

        say("## The vendored contract is behind")
        say()
        say("**This check gates.** It fails every pull request against `main` until")
        say("the artefact in `contract/` is the one lemonfiber serves. You did not")
        say("break this and you do not need to understand the kinds below to fix it.")
        say()
        say(f"- vendored at `{stamp}`: api_version {our_version}, {len(our_kinds)} kinds")
        say(f"- served at lemonfiber `{head}`: api_version {their_version}, {len(their_kinds)} kinds")
        if missing:
            say(f"- described by the server and not here: {', '.join(missing)}")
        if extra:
            say(f"- described here and not by the server: {', '.join(extra)}")
        say(f"- {len(changed)} files differ:")
        for path in changed:
            say(f"  - `{path}`")
        say()
        say("### Run this")
        say()
        say("```sh")
        say(f"composer contract:sync -- {head}")
        say("composer contract:generate")
        say("```")
        say()
        say("Then commit `contract/` and `src/Generated`. Where that regenerates more")
        say("than types — a kind arriving is a change to this package's surface — land")
        say("the sync on its own first; that is the change this gate is asking for.")

        print(
            "::error::the vendored contract is behind lemonfiber "
            f"{head} — run `composer contract:sync -- {head}` then "
            "`composer contract:generate`, and commit contract/ and src/Generated"
        )

    if summary is not None:
        pathlib.Path(summary).write_text("\n".join(said) + "\n", encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__.splitlines()[0])
    parser.add_argument("--head", default="", help="the revision the archive is of, for the report")
    parser.add_argument("--report", action="store_true", help="say what contract-drift found, with the remedy")
    arguments = parser.parse_args()

    repo = pathlib.Path.cwd()

    try:
        ours = vendored_in(repo)
        theirs = served_by(repo / SERVED)
    except Unreadable as error:
        print(f"::error::{error}")
        return 2

    changed = differing(ours, theirs)

    if arguments.report:
        report(repo, arguments.head, ours, theirs, changed)
    elif changed:
        print(f"{len(changed)} files differ:")
        for path in changed:
            print(f"  {path}")
    else:
        print("The vendored contract is the one the revision holds.")

    return 1 if changed else 0


if __name__ == "__main__":
    sys.exit(main())
