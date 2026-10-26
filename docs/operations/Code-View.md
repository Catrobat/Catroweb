# Code view

The project page's code view (`GET /api/projects/{id}/code`) shows a project's code as the Catroid app does. Catroid
itself makes it: the browser player's code export (`player/README.md`, "Code view export") reads `code.xml` with the
player's loader, so every project from Catrobat language 0.9 on, and writes each brick from the brick's own `getView`.
share only adds the translations (Catroid's own, in the viewer's language) and the URLs of looks and sounds. There is no
brick list on share to keep in step with Catroid: updating the player (`player/catroid.commit`) updates the code view.

## How it runs

- `App\Project\CodeView\CodeExportRunner` runs `code-export/export.mjs` from the deployed player
  (`public/player/current/code-export`, `catrobat.code_export.dir`) with Node 22 or newer. `code.xml` is untrusted, so
  Node runs with its permission model: it may read only the export and that one file; no network, child processes or
  writes. 60 s and 1 GB of heap at most (the largest projects, 20,000 bricks, need about 700 MB).
- `CodeViewExportStore` keeps each project's export in `var/code-view/<id>.json` (`catrobat.code_view.dir`, shared
  between releases), keyed by the hash of `code.xml` and the export's version (`code-export/version.txt`, the hash of
  `code-export.js`, `resources.json` and `export.mjs`: it changes when the export's output can, e.g. with a new Catroid
  commit, not with the translations, which are put in per view). A failed export is kept too, so a broken project does
  not start Node on every view; if a newer export fails where an older one worked, the older one stays.
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
  `proc_open` allowed for PHP-FPM. `deploy:player` links `public/player/current` to the build it uploads; without a
  player build in a release, stored code views stay and new ones say they cannot be shown.
- Nothing to run after a deploy: outdated code views are made again as they are viewed.
  `bin/console catro:backfill:code-view [--limit N] [ids...]` makes the missing or outdated ones of recently extracted
  projects (or the given ones) ahead of their viewers, one at a time, waiting for a free slot; safe alongside
  production traffic.

## Development

Build the player (`player/README.md`) and link its build: `ln -s ../../player/build/dist/js/webapp public/player/current`.
Tests use a stand-in export in `tests/TestData/CodeExport`, which lists objects, scripts and bricks by their XML types.
