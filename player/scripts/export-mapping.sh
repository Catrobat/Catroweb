#!/usr/bin/env bash
# Exports Catroid's own XStream mapping from the prepared checkout (run prepare.sh first) into
# build/catroid-input/mapping.tsv. MappingExportTest runs inside Catroid and is never committed there.
# The web project loader will be built from this mapping.
set -euo pipefail
cd "$(dirname "$0")/.."
OUT=build/catroid-input
SRC=build/catroid

mkdir -p $SRC/catroid/src/test/java/org/catrobat/catroid/web
cp scripts/MappingExportTest.java $SRC/catroid/src/test/java/org/catrobat/catroid/web/
(cd $OUT/classes && find org/catrobat/catroid/content org/catrobat/catroid/formulaeditor org/catrobat/catroid/common org/catrobat/catroid/userbrick -name '*.class' ! -name '*$*' | sed 's|\.class$||; s|/|.|g' | sort) > $OUT/model-classes.txt
(cd $SRC && MAPPING_OUT="$PWD/../catroid-input/mapping.tsv" MAPPING_CLASSES="$PWD/../catroid-input/model-classes.txt" ./gradlew -q :catroid:testCatroidDebugUnitTest --tests org.catrobat.catroid.web.MappingExportTest)
rm -r $SRC/catroid/src/test/java/org/catrobat/catroid/web
(cd $SRC && ./gradlew -q --stop)
