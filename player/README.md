# Browser player

Plays Pocket Code projects in the browser with **Catroid's own engine**. Nothing is reimplemented: the web build takes
Catroid's compiled classes at the commit in `catroid.commit` (a `develop` commit) and compiles them to JavaScript with
[gdx-teavm](https://github.com/xpenatan/gdx-teavm). Catroid stays the single source of truth; updating the player means
bumping that commit.

## Build

Needs JDK 21 and the Android SDK (`ANDROID_HOME`).

```bash
./scripts/prepare.sh   # clone + compile Catroid at catroid.commit into build/catroid-input
./scripts/build.sh     # patch it for the browser and compile build/dist/js/webapp
```

Serve `build/dist/js/webapp` with any static server.

## How it works

TeaVM compiles JVM bytecode, but Catroid is an Android app. `scripts/build.sh` patches every jar with
`compat/Rewrite` (ASM, at build time only):

| Rule     | What                                                                                 | Examples                                                                    |
| -------- | ------------------------------------------------------------------------------------ | --------------------------------------------------------------------------- |
| Remap    | Android or backend classes → small web versions in `src/main/java/org/catrobat/web`  | `AndroidApplication`, `Log`, `SystemClock`, `PointF`, `WebConnectionHolder` |
| Redirect | JVM/Android calls TeaVM lacks → `WebCompat`                                          | `getAppContext()`, `Resources.getSystem()`, `ConcurrentHashMap.keys()`      |
| Stub     | any method using something TeaVM reports missing → throws "Not available on the web" | camera, NFC, Bluetooth, file export                                         |

`build.sh` repeats patch → TeaVM until nothing new is reported, so new Catroid code that touches something
unavailable is stubbed instead of breaking the build. `build/web/stub-report.tsv` lists every stubbed method and why.

Other pieces:

- Android base layer: `compat/MockableJar` turns the SDK `android.jar` into one whose methods return defaults (like
  Android unit tests); value classes such as `PointF` still need real web versions.
- Physics: libGDX's pure-Java Box2D emulation (`gdx-box2d-gwt` sources), since native Box2D has no browser version.
- Actions: `compat/GenActions` generates `ActionRegistry`, because the web backend cannot pool actions by reflection.
- `WebLauncher` does what `StageActivity` and `StageLifeCycleController` do on Android.

## Deployment

The deployment workflow builds the player (about 8 minutes) and `deploy:player` uploads it to
`public/player/<commit>/`, a new folder per release because nginx caches `.js` for a year.
`/player/index.html` forwards to the current one.

## Status

The engine runs a project built in code (a pen drawing a circle). Not yet: loading `.catrobat` files, looks and sounds,
Catroid's `catrobatTests` as a conformance suite, and the Play button on share.
