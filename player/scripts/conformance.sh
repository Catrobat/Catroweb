#!/usr/bin/env bash
# Plays every Catroid test project (catrobatTests) on the stage in headless Chrome, a few at a time, and compares the
# results with conformance.tsv: a test that passed there must still pass. `--update` rewrites conformance.tsv instead.
# Each project reports like on Android (data-test-result: 7777 = success, 8888 = an assertion failed); one that never
# reports within a minute counts as "no result". Needs build/catroid from prepare.sh and the built player.
set -euo pipefail
cd "$(dirname "$0")/.."
CHROME=${CHROME:-$(command -v google-chrome || command -v chromium || echo "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")}
SERVER=${CONFORMANCE_SERVER:-scripts/smoke_server.py}  # which address the browser reaches it at: CONFORMANCE_HOST
export CHROME SERVER HOST=${CONFORMANCE_HOST:-127.0.0.1}
export TESTS=build/catroid/catroid/src/androidTest/assets/catrobatTests OUT=build/web/conformance
LIMIT=$(command -v timeout || command -v gtimeout || true)
export LIMIT

rm -rf $OUT
mkdir -p $OUT/results
(cd $TESTS && find . -name '*.catrobat' | sed 's|^\./||' | sort) | nl -w1 -s$'\t' > $OUT/list.tsv

run() { # <number> <project path under catrobatTests>
  local dir=$OUT/projects/$1
  mkdir -p "$dir/project-files/$1/assets"
  unzip -q "$TESTS/$2" -d "$dir/project-files/$1/assets"
  python3 $SERVER build/dist/js/webapp "$dir/port" 60 "$dir" > /dev/null 2>&1 &
  local server=$!
  for _ in $(seq 50); do [ -s "$dir/port" ] && break; sleep 0.2; done
  local page
  page=$(${LIMIT:+$LIMIT 150} "$CHROME" --headless=new --disable-gpu-sandbox --use-angle=swiftshader --enable-unsafe-swiftshader \
    --autoplay-policy=no-user-gesture-required --window-size=480,800 --timeout=120000 \
    --dump-dom "http://$HOST:$(cat "$dir/port")/?autostart#$1" 2> /dev/null | tr '\n' ' ' || true)
  { kill $server && wait $server; } 2> /dev/null || true
  printf '%s\t%s\n' "$2" "$(grep -o 'data-test-result="[^"]*"' <<< "$page" | head -1)" > "$OUT/results/$1"
  rm -rf "$dir"
}
export -f run
xargs -P "${CONFORMANCE_JOBS:-4}" -L 1 bash -c 'run "$@"' _ < $OUT/list.tsv
cat $OUT/results/* | sort > $OUT/results.tsv
if [ "${1:-}" != --update ]; then
  # A loaded runner can starve one test of time: give those that passed before one more go, alone.
  python3 scripts/conformance.py $OUT/results.tsv conformance.tsv --regressions | while read -r path; do
    [ -n "$path" ] && run "$(awk -F'\t' -v p="$path" '$2 == p {print $1}' $OUT/list.tsv)" "$path"
  done
  cat $OUT/results/* | sort > $OUT/results.tsv
fi
python3 scripts/conformance.py $OUT/results.tsv conformance.tsv "$@"
