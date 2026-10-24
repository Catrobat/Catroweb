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
python3 scripts/check_loader.py prepare $CHECK build/catroid/catroid/src/androidTest/assets/catrobatTests fixtures

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

# Catroid test projects with looks, played on the stage: each reports its result like on Android (7777 = success).
# testColorAtXY reads the stage's pixels, so it fails if looks are not drawn.
STAGE=build/web/stage-tests
rm -rf $STAGE
for test in colorAtXY/testColorAtXY bricks/looks/testSetTransparency touchesColor/testColorTouchesColor; do
  mkdir -p "$STAGE/project-files/$(basename $test)/assets"
  unzip -q "build/catroid/catroid/src/androidTest/assets/catrobatTests/$test.catrobat" -d "$STAGE/project-files/$(basename $test)/assets"
done
for test in $(ls $STAGE/project-files); do
  serve 90 $STAGE  # released as soon as the test reports its result
  RESULT=$("$CHROME" --headless=new --disable-gpu-sandbox --use-angle=swiftshader --enable-unsafe-swiftshader \
    --window-size=480,800 --timeout=120000 --dump-dom "http://127.0.0.1:$PORT/#$test" 2> /dev/null \
    | tr '\n' ' ' | grep -o 'data-test-result="[^"]*"' || true)
  kill $SERVER
  echo "$test: ${RESULT:-no result}"
  [[ "$RESULT" == 'data-test-result="7777 '* ]]
done

# The loader check runs synchronously once the page has loaded; --virtual-time-budget keeps headless Chrome from
# dumping the page before it has finished. timeout: a hung renderer must not hold the job until its time limit (macOS
# has no timeout unless coreutils provides gtimeout).
serve 0 $CHECK
LIMIT=$(command -v timeout || command -v gtimeout || true)
${LIMIT:+$LIMIT 900} "$CHROME" --headless=new --virtual-time-budget=600000 --timeout=660000 \
  --dump-dom "http://127.0.0.1:$PORT/#check" > $CHECK/page.html 2> $CHECK/chrome.log
python3 scripts/check_loader.py compare $CHECK $CHECK/page.html
