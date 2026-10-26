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

## Bumping Catroid

The Player Catroid pin workflow (`.github/workflows/player-catroid-pin.yaml`) does it nightly: when Catroid's
`develop` has moved past `catroid.commit`, it runs the Player workflow's build and checks at `develop`'s HEAD
(`prepare.sh` builds `CATROID_COMMIT` instead of the pin when set). If they pass, it opens or updates the pull request
from `bot/catroid-pin`, with the new pin, `conformance.tsv` rewritten by `conformance.py --update`, the Catroid commits
in between and the changes to `build/web/stub-report.tsv` (newly stubbed methods are what no longer works on the
web). If they fail, the run fails and nothing is opened. Pull requests from the workflow's token start no PR
workflows, so it dispatches the required checks on the branch itself. Dispatched from another branch, with an
optional `catroid-commit` to try, it only writes the PR body to the run's summary.

By hand: `CATROID_COMMIT=<commit> ./scripts/prepare.sh`, build and run the checks, then put the commit in
`catroid.commit`.

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
  names, constructors and fields of most of those classes out. The reader lists each element's children once per reading: XStream returns to a
  parent after every child, which made projects with thousands of bricks quadratic.
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
  (from its files, as a blob; otherwise share's thumbnail from its API, which the play vhost's CSP must allow in
  `img-src`), its name (share's API) and what
  the player is doing: loading the player, downloading `code.xml` (which it starts at once, while `app.js` loads),
  reading it, downloading the files (N of M), preparing the sounds. Once the project is read, it also says what the
  project needs, from Catroid's `Project.getRequiredResources()` as the app checks it: the device sensors it reads
  (so people know it is played on a phone) and, as the app's "Component not available" dialog, what the browser
  cannot do (camera, face detection, NFC, Bluetooth robots, ...) with a link to the app; playing anyway stays possible.
  These texts, the statuses and the player's prompts (where Catroid has words for them) are Catroid's own translations (`scripts/export_strings.py --start` writes `strings/<language>.json` for
  every language Catroid ships) in the browser's language, English otherwise. `StartScreen` drives it and waits for the click
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
- Web and look requests: Catroid's `WebAction` runs as in the app. `catroid/WebConnection` (remapped) sends the request
  with `fetch()` (no credentials, Catroid's 60 s timeout) and answers with the OkHttp `Response` the actions read, so
  `WebRequestAction` stores the text and `LookRequestAction` turns the image into a look (in gdx's local storage, see
  Looks). Catroid's trust list (`trustedDomains.json`, copied by `prepare.sh`) decides as in the app; an untrusted URL
  is denied as there without a dialog (511). The play vhost's CSP (`connect-src`) then lets only requests to share
  through, everything else fails as without network (500). Share's media library (`/resources/media/`) sends
  `Access-Control-Allow-Origin: *` (infrastructure repository), so its images work as web looks. The copy look
  brick copies looks in the same storage (`File.exists`/`createNewFile`, `duplicateFile` and `PixmapIO.writePNG` are
  redirected there).
- Screen: the canvas has the project's aspect ratio at the window's size in device pixels (at most 2 per CSS pixel,
  1920 on the long side) and CSS keeps it fitted when the window changes; the demo and `?autostart` keep 480x800.
- Sensors: Catroid's `SensorHandler` reads them through Android classes that `Rewrite` remaps to web versions in
  `android/`, so its own code turns sensor events into formula values. `SensorManager` offers the two sensors it
  prefers, linear acceleration (`devicemotion`) and the rotation vector (`deviceorientation`, absolute where the
  browser has it), with Android's matrix math; `LocationManager` is `navigator.geolocation`; `MediaRecorder` gives
  `SensorLoudness` the microphone's peak (`getUserMedia`), polled with the browser's timers (a `Rewrite` redirect for
  that class only); `Vibrator` is `navigator.vibrate`, and the vibrate brick finds Catroid's `VibrationManager` through
  `WebCompat.vibrationStage`. `WebLauncher` starts only what the project needs (`getRequiredResources`), so the browser
  asks for no permission a project does not use; iPhones ask for motion in the start click.
- Colour sensors: "touching color", "color touches color" and "color at x y" draw the stage into a small frame buffer
  through a camera whose viewport Catroid never sizes in projects that are not stretched (`MAXIMIZE`, like converted
  Scratch projects), so nothing was drawn and they never matched. `Rewrite` redirects that viewport's `apply` in
  those two classes to `WebCompat.applyColorViewport`, which gives it the size Catroid asks for first.
- Catroid's test projects report their result through `StageActivity.finishTestWithResult`; the player writes it to
  `data-test-result` on the page (7777 = success), which `smoke.sh` checks for a few test projects with looks.

## Code view export

share's code view (`App\Project\CodeView`, `docs/operations/Code-View.md` there) shows a project's code as the app
does, made by Catroid itself: `build.sh` also compiles `codeview/CodeExport`, a second TeaVM entry (`codeExport` in
`build.gradle.kts`) with the loader and Catroid's bricks but no stage, into `build/dist/js/webapp/code-export/`, which
`deploy:player` puts next to the player, outside the web root (`var/code-export/`). share runs it with Node 22 or newer,
one process per project, with an empty environment and Node's permission model:
`node --permission --allow-fs-read=<code-export> --allow-fs-read=<code.xml> code-export/export.mjs <code.xml>` writes
the project's code as JSON (its format is in `CodeExport`'s comment). On Node 22 the permission model stops reading
other files, writing, child processes and workers, not network access (that needs Node 25's `--allow-net`); with an
empty environment the export has no secrets to send anywhere.

- The loader reads `code.xml` as the player does (`ProjectLoader.parse`), so every project from Catrobat language 0.9
  on, through xmldom instead of the browser's `DOMParser` (`web/code-export/export.mjs`, which also adds the
  `TreeWalker` the upgrade of projects before 0.93 needs). `export.mjs` takes the markers' control characters out of
  `code.xml` first (also as character references), so no name can show as one of Catroid's strings.
- A brick that cannot be shown (its `getView`, or reading what it put in its views, fails) shows as category `unknown`
  with an `error`; the project's other bricks show as usual.
- Each brick's text comes from its own `getView`: `codeview/CodeLayoutInflater` builds the brick's layout (exported by
  `scripts/export_code_resources.py`) from `codeview/CodeViews`, subclasses of the mockable SDK widgets that keep what
  `getView` puts in them, and `CodeExport` reads them in order: labels, formula fields (`BrickEditText` style, Catroid's
  own formula text) and drop-downs (their selection's name), and the category from the layout's `BrickContainer` style.
  `Rewrite` makes Catroid's own code create the web subclasses (`SUBSTITUTE`: `TextView`, `ArrayAdapter`,
  `SpannableStringBuilder`) and redirects `LayoutInflater.from` and the fragment lookups of an activity there is none of.
- Strings are markers (`\x01` kind `:` name, arguments after `\x1f`, `\x02`) for Catroid's strings, plurals and
  arrays, which share turns into the viewer's language with Catroid's translations (`code-export/strings/`). Plurals
  come from `WebResources.getQuantityString`, which only the export has. Java code must not build a marker with `+`:
  TeaVM reads `\u0001` and `\u0002` in a string concatenation as `StringConcatFactory`'s own tags.
- `scripts/check_code_export.py` exports Catroid's `catrobatTests` and the fixtures and fails on any project that does
  not export or brick that shows wrong (getView failed, a layout or category missing, an empty formula field, a
  drop-down without a selection, a string marker that Catroid's code cut or joined); the Player workflow runs it.

## Deployment

The deployment workflow builds the player in its own job, without secrets, and hands only the built files to the
deploy job; if the build fails, share deploys without it. `scripts/build_version.py` hashes the tracked build inputs
and the Catroid commit actually built into `version.txt`. `deploy:player` uploads it to `public/player/<version>/`:
identical inputs keep the same URL despite TeaVM's varying output order; source changes get a new URL because nginx
caches `.js` for a year. The code export uses the same version. `/player/index.html` forwards to the current one.

The player is meant to run on its own host, `play.catrobat.org`, whose nginx vhost (in the infrastructure repository)
serves share's `public/player/`: project code then never shares an origin with share's login cookies, which share's API
accepts. From a `play.<domain>` host the player reads project files from `share.<domain>`, which allows that origin
without credentials (`PLAYER_ORIGIN`, `App\Project\EventListener\PlayerCorsListener`; nginx sets the same header on
the files it serves via `X-Accel-Redirect`). Only projects a logged-out visitor may see can be played there.

## Status

`index.html#<project id>` plays share projects from Catrobat language 0.9 on, as the app does, with their looks and
sounds, after a click on the start screen (browsers only play sound after one).
Collision outlines are each look's bounding box for now. On phones, acceleration, inclination, compass, location,
loudness and vibration work as in the app (no vibration on iPhones; desktop browsers report 0 for the motion sensors);
camera, face/pose/text/object detection, NFC, Bluetooth devices, Chromecast and speech recognition do not, which the
start screen says. Web and look requests reach share only (the play vhost's CSP); others end as on a device without
network, and toasts are not shown. `conformance.tsv` lists which of Catroid's test projects pass on the
stage: all but five. Four expect `#ffffff` from "color at x y", which Catroid returns in upper case since its #5041,
and `testNumberOfLooks` loads its web looks from `catrob.at` until the Catroid pin includes Catrobat/Catroid#5262
(share URLs; `testDeleteWebLook` then passes too). Project pages link to it ("Play in browser", `PLAYER_ORIGIN`) for projects it can load, outside the
app and only where the browser has WebGL; downloading for the app stays the alternative.
