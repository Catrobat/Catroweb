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

Serve `build/dist/js/webapp` with any static server; `index.html#<project id>` loads that project from share's
`/project-files/<id>/assets/code.xml`. `./scripts/smoke.sh` checks in headless Chrome that the engine runs and that the
loader reads all of Catroid's `catrobatTests` projects, plus those in `fixtures/`, like the app does
(`index.html#check`, compared by `scripts/check_loader.py`). `./scripts/export-mapping.sh` dumps Catroid's XStream mapping.

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
- Project loader: Catroid's own `XstreamSerializer` setup reads `code.xml` through `loader/BrowserDomReader`, an XStream
  reader over the browser's `DOMParser`. XStream builds objects by reflection, so `compiler/CatroidReflectionPolicy`
  grants it Catroid's model, and `compiler/ReflectionLinks` (a TeaVM plugin) works around TeaVM 0.15 leaving the
  names, constructors and fields of most of those classes out.
- Looks: before the stage starts, `ProjectLoader.loadFiles` fetches each look from share into gdx-teavm's local file
  storage, switched to memory only (Catroid's `Files.absolute` calls are redirected there), as `setFileReferences`
  does on Android.
- Sounds: `ProjectLoader.loadSounds` fetches them before the stage starts and `loader/Sounds` decodes them with Web
  Audio, because Catroid reads their durations while it builds the scripts. `android.media.MediaPlayer` and
  `MediaMetadataRetriever` are remapped to small web versions in `android/`. `StartScreen` waits for the click browsers
  require before they play sound; `?autostart` skips it.
- Catroid's test projects report their result through `StageActivity.finishTestWithResult`; the player writes it to
  `data-test-result` on the page (7777 = success), which `smoke.sh` checks for a few test projects with looks.

## Deployment

The deployment workflow builds the player in its own job, without secrets, and hands only the built files to the
deploy job; if the build fails, share deploys without it. `deploy:player` uploads it to `public/player/<hash of app.js>/`,
a new folder per build because nginx caches `.js` for a year. `/player/index.html` forwards to the current one.

## Status

`index.html#<project id>` plays share projects from Catrobat language 0.9991 on (older ones need Catroid's legacy
conversion) with their looks and sounds, after a click on the start screen (browsers only play sound after one).
Collision outlines are each look's bounding box for now, and acceleration, inclination, compass and location are
unavailable, as on a device without those sensors. Not yet: all of `catrobatTests` on the stage as a conformance suite;
the player's own host; the Play button.
