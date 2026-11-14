#!/usr/bin/env bash
# Compares the web-visual Behat screenshots against the committed baselines.
# Never fails: differences become GitHub warnings, a job summary and report.md (reused as the PR comment).
# For every changed snapshot it writes strips/<name>.png: before | after | diff (changed pixels in red).
# Set IMAGE_BASE_URL to where strips/ will be published to embed them in the report.
# Needs ImageMagick. See docs/contributor-guide/Visual-Regression.md.
set -uo pipefail
export LC_ALL=C # awk printf must use "." as the decimal separator

ACTUAL=tests/TestReports/Visual
BASE=tests/visual/baselines
DIFF="$ACTUAL/diff"
STRIPS="$ACTUAL/strips"
REPORT="$ACTUAL/report.md"
# A pixel counts as changed when it differs by more than FUZZ; a snapshot counts as changed when
# more than MAX_RATIO of its pixels did. Raise these if anti-aliasing noise causes warnings.
FUZZ="${VISUAL_FUZZ:-3%}"
MAX_RATIO="${VISUAL_MAX_RATIO:-0.001}"

# ImageMagick 7 may ship only `magick`; 6 only `compare`/`identify`
im() { if command -v magick >/dev/null; then magick "$@"; else "$@"; fi; }

mkdir -p "$DIFF" "$STRIPS"
rows=""
details=""
changed=0

# report <name> <status> <images...>: record a changed snapshot and build its strip
report() {
  local name=$1 status=$2
  shift 2
  rows+="| \`$name\` | $status |"$'\n'
  echo "::warning title=Visual change::$name: $status"
  changed=$((changed + 1))
  [ $# -gt 0 ] || return 0
  im "$@" -bordercolor '#888' -border 2 +append +repage -resize '2400x>' "$STRIPS/$name.png"
  if [ -n "${IMAGE_BASE_URL:-}" ]; then
    details+="<details><summary><code>$name</code>: $status</summary>"$'\n\n'
    details+="![$name]($IMAGE_BASE_URL/$name.png)"$'\n\n'"</details>"$'\n'
  fi
}

for actual in "$ACTUAL"/*.png; do
  [ -e "$actual" ] || continue
  name=$(basename "$actual" .png)
  base="$BASE/$name.png"
  if [ ! -e "$base" ]; then
    report "$name" "🆕 new, no baseline yet" "$actual"
    continue
  fi
  # compare quietly matches overlapping regions when sizes differ, so check that first
  if [ "$(im identify -format '%wx%h' "$base")" != "$(im identify -format '%wx%h' "$actual")" ]; then
    report "$name" "⚠️ size changed" "$base" "$actual"
    continue
  fi
  pixels=$(im compare -metric AE -fuzz "$FUZZ" "$base" "$actual" "$DIFF/$name.png" 2>&1 >/dev/null)
  pixels=${pixels%% *} # ImageMagick 7.1+ appends "(normalized)"
  if ! [[ "$pixels" =~ ^[0-9.e+]+$ ]]; then
    report "$name" "⚠️ unreadable"
    continue
  fi
  total=$(im identify -format '%[fx:w*h]' "$base")
  if awk -v p="$pixels" -v t="$total" -v m="$MAX_RATIO" 'BEGIN { exit !(p / t > m) }'; then
    pct=$(awk -v p="$pixels" -v t="$total" 'BEGIN { printf "%.2f", 100 * p / t }')
    report "$name" "⚠️ $pct% of pixels differ" "$base" "$actual" "$DIFF/$name.png"
  else
    rm -f "$DIFF/$name.png"
  fi
done

for base in "$BASE"/*.png; do
  [ -e "$base" ] || continue
  name=$(basename "$base" .png)
  [ -e "$ACTUAL/$name.png" ] || report "$name" "❓ not captured (scenario failed or removed)"
done

{
  echo "<!-- visual-snapshots -->"
  echo "## Visual snapshots"
  if [ "$changed" -eq 0 ]; then
    echo "✅ All snapshots match their baselines."
  else
    echo "$changed snapshot(s) differ from their baselines."
    echo
    echo "| Snapshot | Status |"
    echo "| --- | --- |"
    printf '%s' "$rows"
    echo
    if [ -n "$details" ]; then
      echo "Each image: **before | after | diff** (changed pixels in red)."
      echo
      printf '%s' "$details"
    else
      echo "Download the **visual-snapshots** artifact to see them (\`strips/\`: before | after | diff)."
    fi
    echo
    echo "**Intended?** Accept the new looks: \`tests/visual/accept.sh ${PR_NUMBER:-<pr>}\`, then push."
    echo "**Noise?** Ignore it; this check never blocks a merge."
  fi
} >"$REPORT"
cat "$REPORT" >>"${GITHUB_STEP_SUMMARY:-/dev/stdout}"
echo "changed=$changed" >>"${GITHUB_OUTPUT:-/dev/null}"
exit 0
