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
  between releases). It makes it again when `code.xml` changes or a newer export is deployed (`code-export/version.txt`).
  A failed export is kept too, so a broken project does not start Node on every view; if a newer export fails where an
  older one worked, the older one stays.
- Uploads never wait: the export runs after the upload's response (`CodeViewExportEventListener`, `kernel.terminate`),
  and otherwise on the first view. A project whose code cannot be shown gets a 422, which the page shows as "This
  project's code can't be shown".
- `CodeViewLocalizer` puts Catroid's strings into the export: it writes them as markers, which become the viewer's
  language (`code-export/strings/<locale>.json`, English where a translation is missing).

## Operations

- Requirements on the server: Node 22 or newer at `/usr/local/bin/node` or `/usr/bin/node` (or in PHP's `PATH`),
  `proc_open` allowed for PHP-FPM. `deploy:player` links `public/player/current` to the build it uploads; without a
  player build in a release, stored code views stay and new ones say they cannot be shown.
- After a player update, `bin/console catro:backfill:code-view [--limit N] [ids...]` makes the outdated code views of
  recently extracted projects ahead of their viewers; all others are made when first viewed.

## Development

Build the player (`player/README.md`) and link its build: `ln -s ../../player/build/dist/js/webapp public/player/current`.
Tests use a stand-in export in `tests/TestData/CodeExport`, which lists objects, scripts and bricks by their XML types.
