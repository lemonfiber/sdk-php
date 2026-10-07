#!/usr/bin/env python3
"""Whether a change touches code, so the jobs that judge only code can skip one that does not.

A pull request that changes only documentation holds runners for the PHP
toolchain, the test suite, the mutation shards, the backward-compatibility
check and the CodeQL analysis, and none of them can answer differently than it
did on the base. So each of those jobs asks this first, through a `what
changed` job, and skips where the answer is no. `ci.yml` and `codeql.yml` each
run that job, and both run this script, so the two cannot disagree about a
path.

It answers once per kind of gate, as a line written to `$GITHUB_OUTPUT`:

  code     the PHP gates in `ci.yml`. Documentation is not code: `docs/`, any
           Markdown file and `LICENSE`, none of which a PHP gate reads. Nor is
           anything under `.github/` but `ci.yml` itself, the one workflow that
           runs them, and `ci.yml` too where every line it changes is a pin on
           a shared workflow from `lemonfiber/spec`, since those jobs run no PHP.
  analyze  CodeQL's analysis of the workflows, which reads `.github/`. Only
           documentation is not code for it.

Anything not named as documentation is code, so a file of a kind nobody has
thought about runs every gate. A rename is read as the deletion and the addition
it is, so moving a source file into `docs/` reaches code through the deletion.

Asked about nothing it can compare (no base, a base that is not a commit here,
a push that opened a branch) it answers yes for every gate. So does a run that
fails: the jobs asking run whenever the `what changed` job did not succeed.

Usage:
  the_code_a_change_touches.py <base>    decide for HEAD against <base>
  the_code_a_change_touches.py --self-test
Exit 0 = decided, 1 = the self-test found a claim broken.
"""

from __future__ import annotations

import os
import pathlib
import re
import subprocess
import sys
import tempfile

# The gates this answers for, in the order their lines are written.
GATES = ("code", "analyze")

# The workflow that runs the PHP gates.
CI = ".github/workflows/ci.yml"

# A changed line that moves a pin on a shared workflow and nothing else.
PIN = re.compile(r"[-+]\s*uses: lemonfiber/spec/\.github/workflows/[^@]+@[0-9a-f]{40}( # .*)?")


def documentation(path: str) -> bool:
    """Whether no gate reads this path."""
    return path.startswith("docs/") or path.endswith(".md") or path == "LICENSE"


def only_pins(base: str, path: str, cwd: pathlib.Path | None) -> bool:
    """Whether every line the change makes to `path` is a pin on a shared workflow."""
    diff = subprocess.run(
        ["git", "diff", "-U0", base, "HEAD", "--", path],
        cwd=cwd,
        capture_output=True,
        check=True,
        text=True,
    ).stdout
    changed = [
        line for line in diff.splitlines() if line[:1] in "+-" and not line.startswith(("+++ ", "--- "))
    ]
    return all(PIN.fullmatch(line) for line in changed)


def reaches(base: str, path: str, cwd: pathlib.Path | None) -> dict[str, bool]:
    """Which gates one changed path reaches."""
    if documentation(path):
        return {"code": False, "analyze": False}
    if path == CI:
        return {"code": not only_pins(base, path, cwd), "analyze": True}
    if path.startswith(".github/"):
        return {"code": False, "analyze": True}
    return {"code": True, "analyze": True}


def changed(base: str, cwd: pathlib.Path | None) -> list[str] | None:
    """Each path HEAD changes against `base`; None where `base` is not a commit."""
    if not base:
        return None
    known = subprocess.run(
        ["git", "cat-file", "-e", f"{base}^{{commit}}"], cwd=cwd, capture_output=True, check=False
    )
    if known.returncode != 0:
        return None
    out = subprocess.run(
        ["git", "diff", "--no-renames", "--name-only", "-z", base, "HEAD"],
        cwd=cwd,
        capture_output=True,
        check=True,
        text=True,
    ).stdout
    return out.split("\0")[:-1]


def decide(base: str, cwd: pathlib.Path | None = None) -> tuple[dict[str, bool], list[str]]:
    """Which gates the change reaches, and the lines that say why."""
    paths = changed(base, cwd)
    if paths is None:
        return dict.fromkeys(GATES, True), [f"No commit `{base}` to compare against, so every gate runs."]
    answer = dict.fromkeys(GATES, False)
    why = []
    for path in paths:
        gates = [gate for gate, hit in reaches(base, path, cwd).items() if hit]
        for gate in gates:
            answer[gate] = True
        why.append(f"{path}: {', '.join(gates) or 'no gate'}")
    return answer, why


def report(answer: dict[str, bool], why: list[str]) -> None:
    """The answer where the workflow reads it, and the reason where a person does."""
    lines = [f"{gate}={'true' if answer[gate] else 'false'}" for gate in GATES]
    print("\n".join([*lines, *why]))
    if output := os.environ.get("GITHUB_OUTPUT"):
        with pathlib.Path(output).open("a", encoding="utf-8") as out:
            out.write("".join(f"{line}\n" for line in lines))
    if summary := os.environ.get("GITHUB_STEP_SUMMARY"):
        with pathlib.Path(summary).open("a", encoding="utf-8") as out:
            out.write("### What changed\n\n" + "".join(f"- `{line}`\n" for line in [*lines, *why]))


def self_test() -> int:
    """Each claim in the docstring, against a repository made to break it."""
    failures = []
    with tempfile.TemporaryDirectory() as made:
        root = pathlib.Path(made)

        def git(*args: str) -> str:
            return subprocess.run(
                ["git", "-c", "user.name=t", "-c", "user.email=t@t", "-c", "commit.gpgsign=false", *args],
                cwd=root,
                capture_output=True,
                check=True,
                text=True,
            ).stdout.strip()

        def commit(files: dict[str, str | None]) -> str:
            for name, text in files.items():
                path = root / name
                if text is None:
                    path.unlink()
                else:
                    path.parent.mkdir(parents=True, exist_ok=True)
                    path.write_text(text, encoding="utf-8")
            git("add", "-A")
            git("commit", "-q", "--allow-empty", "-m", "x")
            return git("rev-parse", "HEAD")

        pin = "      uses: lemonfiber/spec/.github/workflows/dco.yml@{} # v1.0.{}\n"
        workflow = "jobs:\n  dco:\n" + pin.format("a" * 40, 1) + "  checks:\n    runs-on: ubuntu-latest\n"

        git("init", "-q")
        base = commit(
            {
                "README.md": "a",
                "docs/guide.md": "a",
                "LICENSE": "a",
                "src/Client.php": "a",
                CI: workflow,
                ".github/workflows/codeql.yml": "a",
                ".github/dependabot.yml": "a",
            }
        )

        def asks(name: str, files: dict[str, str | None], code: bool, analyze: bool) -> None:
            git("reset", "-q", "--hard", base)
            commit(files)
            got, why = decide(base, root)
            if got != {"code": code, "analyze": analyze}:
                failures.append(f"{name}: {got}, expected code={code} analyze={analyze} ({why})")

        asks("nothing at all", {}, code=False, analyze=False)
        asks(
            "the readme, a guide and the licence",
            {"README.md": "b", "docs/guide.md": "b", "LICENSE": "b"},
            code=False,
            analyze=False,
        )
        asks("a guide deleted", {"docs/guide.md": None}, code=False, analyze=False)
        asks("Markdown under the source", {"src/README.md": "b"}, code=False, analyze=False)
        asks(
            "a source file beside a guide", {"README.md": "b", "src/Client.php": "b"}, code=True, analyze=True
        )
        asks(
            "a source file moved into the docs",
            {"src/Client.php": None, "docs/client.md": "a"},
            code=True,
            analyze=True,
        )
        asks("a file of a kind nobody listed", {"notes.txt": "b"}, code=True, analyze=True)
        asks(
            "a pin moved in ci.yml",
            {CI: workflow.replace(pin.format("a" * 40, 1), pin.format("b" * 40, 2))},
            code=False,
            analyze=True,
        )
        asks(
            "a job changed in ci.yml",
            {CI: workflow.replace("ubuntu-latest", "ubuntu-24.04")},
            code=True,
            analyze=True,
        )
        asks(
            "a pin moved and a job changed in ci.yml",
            {
                CI: workflow.replace(pin.format("a" * 40, 1), pin.format("b" * 40, 2)).replace(
                    "ubuntu-latest", "ubuntu-24.04"
                )
            },
            code=True,
            analyze=True,
        )
        asks("ci.yml deleted", {CI: None}, code=True, analyze=True)
        asks("another workflow", {".github/workflows/codeql.yml": "b"}, code=False, analyze=True)
        asks("the Dependabot settings", {".github/dependabot.yml": "b"}, code=False, analyze=True)
        asks("this script", {"scripts/the_code_a_change_touches.py": "b"}, code=True, analyze=True)

        for missing in ("", "0" * 40, "f" * 40):
            got, _ = decide(missing, root)
            if not all(got.values()):
                failures.append(f"a base of {missing!r} decided some gate skips: {got}")

    for failure in failures:
        print(f"FAIL: {failure}", file=sys.stderr)
    if not failures:
        print("every claim refused its break")
    return 1 if failures else 0


def main(argv: list[str]) -> int:
    if argv == ["--self-test"]:
        return self_test()
    if len(argv) != 1:
        print(__doc__, file=sys.stderr)
        return 2
    report(*decide(argv[0]))
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
