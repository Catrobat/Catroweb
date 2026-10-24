#!/usr/bin/env bash
# Opens the built player in headless Chrome: checks that the demo pen drew its circle, and that the project loader reads
# Catroid's own test projects like the app does. Catches what compiling cannot: a Catroid bump whose web build compiles
# but no longer runs.
set -euo pipefail
cd "$(dirname "$0")/.."
CHROME=${CHROME:-$(command -v google-chrome || command -v chromium || echo "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")}
SHOT=build/web/smoke.png

CHECK=build/web/loader-check
rm -rf build/web/smoke.port $CHECK
python3 scripts/check_loader.py prepare build/catroid/catroid/src/androidTest/assets/catrobatTests $CHECK

serve() { # <wait seconds> [fixtures dir]: starts smoke_server, sets PORT
  rm -f build/web/smoke.port
  python3 scripts/smoke_server.py build/dist/js/webapp build/web/smoke.port "$@" &
  SERVER=$!
  for _ in $(seq 50); do [ -s build/web/smoke.port ] && break; kill -0 $SERVER; sleep 0.2; done
  PORT=$(cat build/web/smoke.port)
}
trap 'kill $SERVER' EXIT

# WebGL runs on SwiftShader without a GPU; smoke_server holds the load event back so the stage gets 15 s to run.
serve 15
"$CHROME" --headless=new --disable-gpu-sandbox --use-angle=swiftshader --enable-unsafe-swiftshader \
  --window-size=480,800 --hide-scrollbars --timeout=60000 --screenshot="$PWD/$SHOT" "http://127.0.0.1:$PORT/" > /dev/null 2>&1
PIXELS=$(python3 scripts/pen_pixels.py "$SHOT")
echo "Pen pixels on the stage: $PIXELS"
[ "$PIXELS" -gt 300 ]
kill $SERVER

# The loader check asks for /project-files/done when it has finished, which releases the load event early. Without the
# throttling flags headless Chrome delays the timers TeaVM's threads yield through until the page has loaded.
serve 600 $CHECK
"$CHROME" --headless=new --disable-background-timer-throttling --disable-renderer-backgrounding \
  --disable-backgrounding-occluded-windows --timeout=660000 --dump-dom "http://127.0.0.1:$PORT/#check" > $CHECK/page.html 2> /dev/null
python3 scripts/check_loader.py compare $CHECK $CHECK/page.html
