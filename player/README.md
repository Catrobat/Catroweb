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
(`index.html#check`, compared by `scripts/check_loader.py`). `./scripts/export-mapping.sh` dumps Catroid's XStream
mapping. `./scripts/conformance.sh` plays every `catrobatTests` project on the stage and compares the results with
`conformance.tsv`, where each test is `pass` or `fail` with its reason: a test that passes there must keep passing (CI
runs it in the Player workflow). After making more tests pass, record them with `./scripts/conformance.sh --update`.

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
- Older projects: as `XstreamSerializer.loadProject`, `ProjectLoader` reads projects without scenes (before Catrobat
  language 0.992) and those before 0.9991 into Catroid's legacy classes and converts them, then applies
  `ProjectManager`'s version updates. `Rewrite` makes the private `prepareXstream` and
  `modifyXMLToSupportUnknownFields` callable; the latter upgrades the XML of projects before 0.93 and, in that class
  only, edits the browser's DOM through `java/Dom` instead of `org.w3c.dom`. Projects without scenes keep their files
  in `images/` and `sounds/` without a scene folder, where share serves them.
- Looks: before the stage starts, `ProjectLoader.loadFiles` fetches each look from share into gdx-teavm's local file
  storage, switched to memory only (Catroid's `Files.absolute` calls are redirected there), as `setFileReferences`
  does on Android.
- Sounds: `ProjectLoader.loadSounds` fetches them before the stage starts and `loader/Sounds` decodes them with Web
  Audio, because Catroid reads their durations while it builds the scripts. `android.media.MediaPlayer` and
  `MediaMetadataRetriever` are remapped to small web versions in `android/`.
- Start screen: `web/start.js` (put before `app.js` in `index.html` by `build.sh`) shows the project's screenshot
  (from its files, as a blob, since the player's CSP allows no images from share), its name (share's API) and what
  the player is doing: loading the player, downloading `code.xml` (which it starts at once, while `app.js` loads),
  reading it, downloading the files (N of M), preparing the sounds. `StartScreen` drives it and waits for the click
  browsers require before they play sound; `?autostart` has no start screen. The click also goes full screen and, on
  Android, locks the screen to the project's orientation (iPhones have no full screen for pages); after leaving full
  screen, a small button in the corner goes back. A project that cannot be loaded says why, with a link back to
  share. `QuickStart`, in gdx-teavm's package, skips the two waits of gdx-teavm's start (its preloader fills its bar
  at 0.9 per second, then it renders only from the 61st frame on), about two seconds.
- Open URL and save/share plot (and save/share laser, which subclass them): what the app hands to Android goes to the
  browser (`catroid/BrowserHandoff`). The link opens in a new tab (http and https only, `noopener,noreferrer`); the
  plot is Catroid's own SVG (`SVGPlotGenerator`, caught by a redirect of `writeToSVGFile`) offered as a download.
  Browsers allow both only right after a click, so otherwise a prompt at the bottom of the stage asks first; the player
  page never navigates. The open URL brick skips the app's web request and trust dialog.
- Screen: the canvas has the project's aspect ratio at the window's size in device pixels (at most 2 per CSS pixel,
  1920 on the long side) and CSS keeps it fitted when the window changes; the demo and `?autostart` keep 480x800.
- Catroid's test projects report their result through `StageActivity.finishTestWithResult`; the player writes it to
  `data-test-result` on the page (7777 = success), which `smoke.sh` checks for a few test projects with looks.

## Deployment

The deployment workflow builds the player in its own job, without secrets, and hands only the built files to the
deploy job; if the build fails, share deploys without it. `deploy:player` uploads it to `public/player/<hash of app.js>/`,
a new folder per build because nginx caches `.js` for a year. `/player/index.html` forwards to the current one.

The player is meant to run on its own host, `play.catrobat.org`, whose nginx vhost (in the infrastructure repository)
serves share's `public/player/`: project code then never shares an origin with share's login cookies, which share's API
accepts. From a `play.<domain>` host the player reads project files from `share.<domain>`, which allows that origin
without credentials (`PLAYER_ORIGIN`, `App\Project\EventListener\PlayerCorsListener`; nginx sets the same header on
the files it serves via `X-Accel-Redirect`). Only projects a logged-out visitor may see can be played there.

## Status

`index.html#<project id>` plays share projects from Catrobat language 0.9 on, as the app does, with their looks and
sounds, after a click on the start screen (browsers only play sound after one).
Collision outlines are each look's bounding box for now, and acceleration, inclination, compass and location are
unavailable, as on a device without those sensors. `conformance.tsv` lists which of Catroid's test projects pass on
the stage. Project pages link to it ("Play in browser", `PLAYER_ORIGIN`) for projects it can load, outside the
app and only where the browser has WebGL; downloading for the app stays the alternative.
