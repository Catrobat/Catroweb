# Code view

The project page's code view (`GET /api/projects/{id}/code`) shows a project's code as the Catroid app does. Catroid
itself makes it: the browser player's code export (`player/README.md`, "Code view export") reads `code.xml` with the
player's loader, so every project from Catrobat language 0.9 on, and writes each brick from the brick's own `getView`.
share only adds the translations (Catroid's own, in the viewer's language) and the URLs of looks and sounds. There is no
brick list on share to keep in step with Catroid: updating the player (`player/catroid.commit`) updates the code view.

## How it runs

- `App\Project\CodeView\CodeExportRunner` runs `export.mjs` of the player's code export, which `deploy:player` puts
  outside the web root (`var/code-export/`, `catrobat.code_export.dir`), with Node 22 or newer. `code.xml` is
  untrusted, so Node runs with an empty environment (none of share's secrets) and its permission model: it may read
  only the export and that one file, and cannot write, start processes or workers. On Node 22 the permission model
  does not restrict the network (Node 25's `--allow-net` does). 60 s and 1 GB of heap at most (the largest projects,
  20,000 bricks, need about 700 MB); `/usr/bin/timeout` (where there is one) also ends Node when PHP-FPM kills the
  worker that started it.
- `CodeViewExportStore` keeps each project's export in `var/code-view/<id>.json` (`catrobat.code_view.dir`, shared
  between releases), keyed by the hash of `code.xml` and the export's version (`code-export/version.txt`, the hash of
  what the export is built from: the Catroid pin and the player's sources and build scripts, not of the built
  `code-export.js`, which TeaVM orders differently on every build; translations are put in per view and not part of
  it). An export that says the project cannot be shown (Node ran and wrote `{"error": ...}`, e.g. for a broken
  `code.xml`) is kept too, so such a project does not start Node on every view; if a newer export says so where an
  older one worked, the older one stays. An export that could not run or finish (Node missing, crashed, timed out, no
  export deployed) is not kept: the view gets 503, as below.
- Views never wait for a newer export: an outdated one is shown as it is and made again after the response
  (`kernel.terminate`). Only the first view of a project without an export for its `code.xml` waits for Node (median
  about 0.9 s). Uploads never wait: the export runs after the upload's response (`CodeViewExportEventListener`).
- At most two exports run at once per server (`CodeViewExportStore::SLOTS`, lock files `var/code-view/.slot-N.lock`),
  next to PHP-FPM's few workers. A view that needs an export when both slots (or that project's lock) are taken gets
  503 with `Retry-After` at once, which the page shows as "Could not load code view" with "Try again"; it never waits.
  After the response, busy slots skip the outdated export; the next view queues it again.
- A project whose code cannot be shown gets a 422, which the page shows as "This project's code can't be shown".
- `CodeViewLocalizer` puts Catroid's strings into the export: it writes them as markers, which become the viewer's
  language (`code-export/strings/<locale>.json`, English where a translation is missing).

## Operations

- Requirements on the server: Node 22 or newer at `/usr/local/bin/node` or `/usr/bin/node` (or in PHP's `PATH`),
  `proc_open` allowed for PHP-FPM, `/usr/bin/env`, `/usr/bin/timeout`. A deploy whose player build failed keeps the
  previous release's player and code export (`deploy:player`).
- Nothing to run after a deploy: outdated code views are made again as they are viewed.
  `bin/console catro:backfill:code-view [--limit N] [ids...]` makes the missing or outdated ones of recently extracted
  projects (or the given ones) ahead of their viewers, one at a time, waiting for a free slot; safe alongside
  production traffic.

## Development

Build the player (`player/README.md`) and link its export: `ln -s ../player/build/dist/js/webapp/code-export var/code-export`.
Tests use a stand-in export in `tests/TestData/CodeExport`, which lists objects, scripts and bricks by their XML types.
