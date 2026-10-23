#!/usr/bin/env bash
# Opens the built player in headless Chrome and checks that the demo pen drew its circle.
# Catches what compiling cannot: a Catroid bump whose web build compiles but no longer runs.
set -euo pipefail
cd "$(dirname "$0")/.."
CHROME=${CHROME:-$(command -v google-chrome || command -v chromium || echo "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")}
SHOT=build/web/smoke.png

rm -f build/web/smoke.port
python3 scripts/smoke_server.py build/dist/js/webapp build/web/smoke.port 15 &
SERVER=$!
trap 'kill $SERVER' EXIT
for _ in $(seq 50); do [ -s build/web/smoke.port ] && break; kill -0 $SERVER; sleep 0.2; done
PORT=$(cat build/web/smoke.port)

# WebGL runs on SwiftShader without a GPU; smoke_server holds the load event back so the stage gets 15 s to run.
"$CHROME" --headless=new --disable-gpu-sandbox --use-angle=swiftshader --enable-unsafe-swiftshader \
  --window-size=480,800 --hide-scrollbars --timeout=60000 --screenshot="$PWD/$SHOT" "http://127.0.0.1:$PORT/" > /dev/null 2>&1

PIXELS=$(python3 scripts/pen_pixels.py "$SHOT")
echo "Pen pixels on the stage: $PIXELS"
[ "$PIXELS" -gt 300 ]
