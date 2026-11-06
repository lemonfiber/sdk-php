#!/usr/bin/env bash
# Says each break of what the companion pins that <head> makes and <base> does
# not, one `[BC]` line each. A break <base> already makes is the release train's
# to carry to the companion. The commit is the one the companion's main branch
# pins in its lockfile, which is read with `GH_TOKEN`.
#
# Each comparison ends on its verdict. One that did not reach it compared
# nothing, and nothing compared is not nothing broken, so that exits 1, as a pin
# that cannot be read does.
#
# Usage: scripts/bc-added.sh <base> <head>
set -euo pipefail

base="$1"
head="$2"
check=vendor-bin/bc/vendor/bin/roave-backward-compatibility-check

lock="$(gh api repos/lemonfiber/lemonfiber-companion/contents/composer.lock -H 'Accept: application/vnd.github.raw')"
pin="$(printf '%s' "${lock}" | jq -r '[.packages[], (.["packages-dev"] // [])[]] | map(select(.name == "lemonfiber/sdk-php")) | first | .source.reference // empty')"
if [[ -z "${pin}" ]]; then
  echo "::error::The companion's lockfile names no commit of lemonfiber/sdk-php, so there is nothing to compare against." >&2
  exit 1
fi
if ! git cat-file -e "${pin}^{commit}" 2>/dev/null; then
  echo "::error::The companion pins ${pin}, which is not a commit of this repository." >&2
  exit 1
fi
echo "Comparing against ${pin}, the commit the companion's main branch pins." >&2

scratch="$(mktemp -d)"
trap 'rm -rf "${scratch}"' EXIT

# One side of the comparison, as its sorted `[BC]` lines in <side>.txt.
compare() {
  local side="$1" commit="$2"
  "${check}" --from="${pin}" --to="${commit}" > "${scratch}/${side}.out" 2>&1 || true
  if ! grep -Eq 'backwards-incompatible changes detected' "${scratch}/${side}.out"; then
    cat "${scratch}/${side}.out" >&2
    echo "::error::The comparison of the ${side} against ${pin} did not finish." >&2
    exit 1
  fi
  { grep '^\[BC\]' "${scratch}/${side}.out" || true; } | sort -u > "${scratch}/${side}.txt"
}

compare base "${base}"
compare head "${head}"
comm -13 "${scratch}/base.txt" "${scratch}/head.txt"
