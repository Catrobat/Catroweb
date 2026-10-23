#!/usr/bin/env bash
# Builds Catroid at the commit in catroid.commit and collects what the web build compiles from:
# its classes, runtime jars, R.jar and its XStream mapping (which drives the web project loader).
set -euo pipefail
cd "$(dirname "$0")/.."
OUT=build/catroid-input
SRC=build/catroid
COMMIT=$(cat catroid.commit)

[ -d $SRC/.git ] || git clone --filter=blob:none https://github.com/Catrobat/Catroid.git $SRC
git -C $SRC fetch -q origin "$COMMIT" && git -C $SRC checkout -q "$COMMIT"

mkdir -p $OUT
(cd $SRC && ./gradlew -q :catroid:compileCatroidDebugKotlin :catroid:compileCatroidDebugJavaWithJavac \
  :catroid:processCatroidDebugResources \
  && CP_OUT="$PWD/../catroid-input/runtime-classpath.txt" ./gradlew -q -I ../../scripts/catroid-classpath.init.gradle :catroid:printWebClasspath)

rm -rf $OUT/classes && mkdir -p $OUT/classes
cp -R $SRC/catroid/build/intermediates/javac/catroidDebug/compileCatroidDebugJavaWithJavac/classes/. $OUT/classes/
cp -R $SRC/catroid/build/tmp/kotlin-classes/catroidDebug/. $OUT/classes/
(cd $OUT/classes && jar cf ../catroid-classes.jar .)
cp $SRC/catroid/build/intermediates/compile_and_runtime_not_namespaced_r_class_jar/catroidDebug/processCatroidDebugResources/R.jar $OUT/R.jar
# Catroid's own XStream mapping, exported from the pinned commit, drives the web project loader.
mkdir -p $SRC/catroid/src/test/java/org/catrobat/catroid/web
cp scripts/MappingExportTest.java $SRC/catroid/src/test/java/org/catrobat/catroid/web/
(cd $OUT/classes && find org/catrobat/catroid/content org/catrobat/catroid/formulaeditor org/catrobat/catroid/common org/catrobat/catroid/userbrick -name '*.class' ! -name '*$*' | sed 's|\.class$||; s|/|.|g' | sort) > $OUT/model-classes.txt
(cd $SRC && MAPPING_OUT="$PWD/../catroid-input/mapping.tsv" MAPPING_CLASSES="$PWD/../catroid-input/model-classes.txt" ./gradlew -q :catroid:testCatroidDebugUnitTest --tests org.catrobat.catroid.web.MappingExportTest)
rm -r $SRC/catroid/src/test/java/org/catrobat/catroid/web
mkdir -p assets && cp -R $SRC/catroid/src/main/assets/stage assets/
echo "Catroid $COMMIT prepared in $OUT"
