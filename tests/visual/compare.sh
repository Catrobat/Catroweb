#!/usr/bin/env bash
# Compares the web-visual Behat screenshots against the committed baselines.
# Never fails: differences become GitHub warnings + a job summary table.
# Needs ImageMagick (`compare`). See docs/contributor-guide/Visual-Regression.md.
set -uo pipefail
export LC_ALL=C # awk printf must use "." as the decimal separator

ACTUAL=tests/TestReports/Visual
BASE=tests/visual/baselines
DIFF="$ACTUAL/diff"
SUMMARY="${GITHUB_STEP_SUMMARY:-/dev/stdout}"
# A pixel counts as changed when it differs by more than FUZZ; a snapshot counts as changed when
# more than MAX_RATIO of its pixels did. Raise these if anti-aliasing noise causes warnings.
FUZZ="${VISUAL_FUZZ:-3%}"
MAX_RATIO="${VISUAL_MAX_RATIO:-0.001}"

# ImageMagick 7 may ship only `magick`; 6 only `compare`/`identify`
im() { if command -v magick >/dev/null; then magick "$@"; else "$@"; fi; }

mkdir -p "$DIFF"
rows=""
changed=0

for actual in "$ACTUAL"/*.png; do
  [ -e "$actual" ] || continue
  name=$(basename "$actual" .png)
  base="$BASE/$name.png"
  if [ ! -e "$base" ]; then
    rows+="| \`$name\` | 🆕 new, no baseline yet |"$'\n'
    changed=$((changed + 1))
    continue
  fi
  # compare quietly matches overlapping regions when sizes differ, so check that first
  if [ "$(im identify -format '%wx%h' "$base")" != "$(im identify -format '%wx%h' "$actual")" ]; then
    rows+="| \`$name\` | ⚠️ size changed |"$'\n'
    echo "::warning title=Visual change::$name: size changed"
    changed=$((changed + 1))
    continue
  fi
  pixels=$(im compare -metric AE -fuzz "$FUZZ" "$base" "$actual" "$DIFF/$name.png" 2>&1 >/dev/null)
  pixels=${pixels%% *} # ImageMagick 7.1+ appends "(normalized)"
  if ! [[ "$pixels" =~ ^[0-9.e+]+$ ]]; then
    rows+="| \`$name\` | ⚠️ unreadable |"$'\n'
    echo "::warning title=Visual change::$name: unreadable"
    changed=$((changed + 1))
    continue
  fi
  total=$(im identify -format '%[fx:w*h]' "$base")
  if awk -v p="$pixels" -v t="$total" -v m="$MAX_RATIO" 'BEGIN { exit !(p / t > m) }'; then
    pct=$(awk -v p="$pixels" -v t="$total" 'BEGIN { printf "%.2f", 100 * p / t }')
    rows+="| \`$name\` | ⚠️ $pct% of pixels differ |"$'\n'
    echo "::warning title=Visual change::$name: $pct% of pixels differ from the baseline"
    changed=$((changed + 1))
  else
    rm -f "$DIFF/$name.png"
  fi
done

for base in "$BASE"/*.png; do
  [ -e "$base" ] || continue
  name=$(basename "$base" .png)
  if [ ! -e "$ACTUAL/$name.png" ]; then
    rows+="| \`$name\` | ❓ not captured (scenario failed or removed) |"$'\n'
    changed=$((changed + 1))
  fi
done

{
  echo "## Visual snapshots"
  if [ "$changed" -eq 0 ]; then
    echo "✅ All snapshots match their baselines."
  else
    echo "$changed snapshot(s) differ. Download the **visual-snapshots** artifact to see them (\`diff/\` marks changed pixels in red)."
    echo
    echo "| Snapshot | Status |"
    echo "| --- | --- |"
    printf '%s' "$rows"
    echo
    echo "**Intended?** Accept the new looks: \`tests/visual/accept.sh ${PR_NUMBER:-<pr>}\`, then push."
    echo "**Noise?** Ignore it; this check never blocks a merge."
  fi
} >>"$SUMMARY"
exit 0
