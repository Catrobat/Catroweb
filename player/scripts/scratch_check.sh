#!/usr/bin/env bash
# Scratch imports in the player: converts the projects in fixtures/scratch/ with share's own converter
# (convert_scratch.php), checks that the loader reads them like the app does (check_loader.py), and plays each on the
# stage (scratch_check.py). Catches a converter change that writes what Catroid cannot load or run, and a Catroid bump
# that breaks converted projects. Needs the built player, Catroweb's vendor/ and rsvg-convert + sox (fixtures/README.md).
set -euo pipefail
cd "$(dirname "$0")/.."
CHROME=${CHROME:-$(command -v google-chrome || command -v chromium || echo "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")}
OUT=build/web/scratch
rm -rf $OUT && mkdir -p $OUT/converted $OUT/stage

php scripts/convert_scratch.php $OUT/converted

python3 scripts/check_loader.py prepare $OUT/check $OUT/converted
for project in $OUT/converted/*.catrobat; do
  assets="$OUT/stage/project-files/$(basename "$project" .catrobat)/assets"
  mkdir -p "$assets" && unzip -q "$project" -d "$assets"
done

serve() { # <dir>: starts smoke_server on it, sets PORT
  rm -f $OUT/port
  python3 scripts/smoke_server.py build/dist/js/webapp $OUT/port 0 "$1" &
  SERVER=$!
  for _ in $(seq 50); do [ -s $OUT/port ] && break; kill -0 $SERVER; sleep 0.2; done
  PORT=$(cat $OUT/port)
}
trap 'kill ${SERVER:-} 2> /dev/null || true' EXIT

serve $OUT/check  # as in smoke.sh: the loader check runs once the page has loaded
LIMIT=$(command -v timeout || command -v gtimeout || true)
${LIMIT:+$LIMIT 900} "$CHROME" --headless=new --virtual-time-budget=600000 --timeout=660000 \
  --dump-dom "http://127.0.0.1:$PORT/#check" > $OUT/check/page.html 2> /dev/null
python3 scripts/check_loader.py compare $OUT/check $OUT/check/page.html
kill $SERVER

serve $OUT/stage
(cd scripts && python3 scratch_check.py "$CHROME" "http://127.0.0.1:$PORT" $(ls ../$OUT/stage/project-files))
