#!/usr/bin/env bash
# Builds the browser player from build/catroid-input (see prepare.sh) into build/dist/js/webapp.
# Catroid's bytecode is patched for TeaVM (compat/Rewrite) until TeaVM reports nothing missing.
set -euo pipefail
cd "$(dirname "$0")/.."
IN=build/catroid-input
W=build/web
mkdir -p $W/patched $W/tools
JAVA=${JAVA_HOME:+$JAVA_HOME/bin/}java
JAVAC=${JAVA_HOME:+$JAVA_HOME/bin/}javac
GDX_VERSION=1.14.2
ASM_VERSION=9.9
M2=https://repo1.maven.org/maven2

fetch() { [ -s "$2" ] || { curl -sfL --retry 3 -o "$2.part" "$1" && mv "$2.part" "$2"; }; }
for a in asm asm-tree asm-commons; do fetch $M2/org/ow2/asm/$a/$ASM_VERSION/$a-$ASM_VERSION.jar $W/tools/$a.jar; done
TOOLS=$W/tools/asm.jar:$W/tools/asm-tree.jar:$W/tools/asm-commons.jar
$JAVAC -nowarn -d $W/tools/classes -cp $TOOLS compat/src/main/java/org/catrobat/web/compat/*.java
TOOLS=$W/tools/classes:$TOOLS

# The SDK android.jar throws "Stub!" everywhere; the mockable copy returns defaults, like Android unit tests.
[ -s $IN/android.jar ] || $JAVA -cp $TOOLS org.catrobat.web.compat.MockableJar "${ANDROID_HOME:?set ANDROID_HOME}/platforms/android-$(grep -oE "compileSdk = [0-9]+" build/catroid/catroid/build.gradle | grep -oE "[0-9]+")/android.jar" $IN/android.jar

# libGDX's GWT emulation of Box2D is pure Java (jbox2d); the native gdx-box2d has no browser counterpart.
fetch $M2/com/badlogicgames/gdx/gdx/$GDX_VERSION/gdx-$GDX_VERSION.jar $W/tools/gdx.jar
if [ ! -s $W/box2d-web.jar ]; then
  fetch $M2/com/badlogicgames/gdx/gdx-box2d-gwt/$GDX_VERSION/gdx-box2d-gwt-$GDX_VERSION-sources.jar $W/tools/box2d-gwt.jar
  rm -rf $W/box2d && mkdir -p $W/box2d/src $W/box2d/classes && (cd $W/box2d/src && jar xf ../../tools/box2d-gwt.jar)
  EMU=$W/box2d/src/com/badlogic/gdx/physics/box2d/gwt/emu
  $JAVAC -nowarn -encoding UTF-8 -d $W/box2d/classes -cp $W/tools/gdx.jar $(find $EMU/com $EMU/org -name '*.java')
  (cd $W/box2d/classes && jar cf ../../box2d-web.jar .)
fi

# Everything the reflection policy (CatroidReflectionPolicy.isBuiltByXStream, keep the two in step) lets XStream
# build: TeaVM only keeps constructors of preserved classes.
{ unzip -Z1 $IN/catroid-classes.jar | grep -E '^org/catrobat/catroid/(content|formulaeditor|common|userbrick)/' \
    | grep -vE '/actions/|/brickspinner/BrickSpinner' | grep '\.class$' | sed 's|\.class$||; s|/|.|g'
  printf '%s\n' org.catrobat.catroid.devices.mindstorms.nxt.sensors.NXTSensor\$Sensor \
    org.catrobat.catroid.devices.mindstorms.ev3.sensors.EV3Sensor\$Sensor \
    com.thoughtworks.xstream.mapper.EnumMapper com.thoughtworks.xstream.mapper.LambdaMapper \
    com.thoughtworks.xstream.mapper.AnnotationMapper com.thoughtworks.xstream.converters.reflection.FieldUtil15 java.util.ArrayList java.util.LinkedList java.util.HashMap \
    java.util.LinkedHashMap java.util.TreeMap java.util.HashSet java.util.LinkedHashSet java.util.TreeSet java.util.Vector \
    java.util.concurrent.ConcurrentHashMap java.util.concurrent.CopyOnWriteArrayList
} > $W/preserved-classes.txt

$JAVA -cp $TOOLS org.catrobat.web.compat.GenActions $IN/catroid-classes.jar $W/tools/gdx.jar src/main/java/org/catrobat/web/ActionRegistry.java

jars() {
  echo $IN/catroid-classes.jar; echo $IN/android.jar; echo $IN/R.jar; echo $W/box2d-web.jar
  grep -vE 'gdx-1\.13|gdx-box2d-1\.13|gdx-backend-android|jacoco' $IN/runtime-classpath.txt
}

patch() {
  : > $W/patched-classpath.txt; : > $W/stub-report.tsv
  while read -r jar; do
    [ -n "$jar" ] || continue
    STUB_REPORT=$W/stub-report.tsv $JAVA -cp $TOOLS org.catrobat.web.compat.Rewrite $W/missing.txt "$jar" $W/patched/$(basename "$jar") > /dev/null
    echo "$PWD/$W/patched/$(basename "$jar")" >> $W/patched-classpath.txt
  done < <(jars)
}

touch $W/missing.txt
for round in $(seq 1 12); do
  patch
  ./gradlew -q compileJava
  (cd build/classes/java/main && jar cf ../../../web/own-classes.jar .)
  { echo $W/own-classes.jar; cat $W/patched-classpath.txt; find "${GRADLE_USER_HOME:-$HOME/.gradle}/caches/modules-2" -name '*.jar' \
      \( -path '*gdx-teavm*' -o -path '*org.teavm*' -o -path "*gdx/$GDX_VERSION*" \) ! -name '*sources*'; } > $W/all-jars.txt
  $JAVA -cp $TOOLS org.catrobat.web.compat.FindLdc $W/all-jars.txt > $W/missing-ldc.txt
  if [ -n "$(comm -13 $W/missing.txt <(sort -u $W/missing-ldc.txt))" ]; then sort -u $W/missing.txt $W/missing-ldc.txt -o $W/missing.txt; patch; fi
  # The player and the code view's export (codeview/CodeExport) from the same patched jars.
  if ./gradlew gdx_teavm_web_js_build gdx_teavm_web_js_code_export_build > $W/teavm.log 2>&1; then
    echo "Player built (round $round): $(grep -c '^org/catrobat/catroid' $W/stub-report.tsv) Catroid methods unavailable on the web, see $W/stub-report.tsv"
    python3 scripts/export_strings.py ${JAVA_HOME:+$JAVA_HOME/bin/}javap $IN/R.jar build/dist/js/webapp/strings.json \
      build/catroid/catroid/src/main/res/values build/catroid/catroid/src/catroid/res/values
    python3 scripts/export_strings.py --start build/dist/js/webapp/strings \
      build/catroid/catroid/src/main/res build/catroid/catroid/src/catroid/res
    # The code view's export (README.md, "Code view export"), which share runs with Node: next to the player, so
    # deploy:player ships both. version.txt changes with its inputs, so share makes its stored code views again.
    E=build/dist/js/webapp/code-export
    rm -rf $E && mkdir -p $E
    cp build/dist/js/code_export/webapp/code-export.js web/code-export/export.mjs web/code-export/package.json \
      web/code-export/package-lock.json $E/
    python3 scripts/export_code_resources.py ${JAVA_HOME:+$JAVA_HOME/bin/}javap $IN/R.jar build/catroid/catroid/src/main/res \
      build/catroid/catroid/src/catroid/res $E
    (cd $E && npm ci --omit=dev --ignore-scripts --no-audit --no-fund --silent && rm package.json package-lock.json)
    # From what the export is built from, not from code-export.js: TeaVM orders its output differently on every build.
    git ls-files -z catroid.commit build.gradle.kts settings.gradle.kts gradle compat src web/code-export scripts/build.sh \
      scripts/prepare.sh scripts/export_code_resources.py scripts/catroid-classpath.init.gradle | xargs -0 shasum \
      | shasum | cut -c1-12 > $E/version.txt
    # The start screen shows up before app.js is in (web/start.js).
    cp web/start.js build/dist/js/webapp/start.js
    python3 - build/dist/js/webapp/index.html <<'EOF'
import sys
page = open(sys.argv[1]).read()
app = '<script type="text/javascript" charset="utf-8" src="app.js">'
assert app in page, "gdx-teavm's index.html changed: load start.js before app.js"
open(sys.argv[1], "w").write(page.replace(app, '<script src="start.js"></script>' + app))
EOF
    exit 0
  fi
  # Any other TeaVM failure (memory, network, a crash) finds nothing new here and prints the log tail below.
  { grep -E 'was not found|has no implementation|which is missing in the classpath' $W/teavm.log || true; } \
    | sed -E 's/^[[:space:]]+//' | sort -u > $W/missing-new.txt
  new=$(comm -13 $W/missing.txt $W/missing-new.txt | wc -l | tr -d ' ')
  echo "round $round: $new newly missing"
  if [ "$new" = 0 ]; then grep -vE '^\| |^[[:space:]]+at ' $W/teavm.log | tail -20; exit 1; fi
  sort -u $W/missing.txt $W/missing-new.txt -o $W/missing.txt
done
exit 1
