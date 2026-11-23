# User docs

The user documentation is written in the public repo [Catrobat/docs](https://github.com/Catrobat/docs) (Markdown, reviewed in pull requests) and shown on share at `/{theme}/docs`. Share keeps a rendered copy in the `doc_page` table; merging a docs pull request is publishing, no share deploy is needed. The plan behind it is epic #7772.

## Sync

`bin/console catrobat:docs:sync` downloads the tarball of `DOCS_REPOSITORY` at `DOCS_REF` (`.env`: `Catrobat/docs`, `main`), reads the Markdown files straight from the archive (nothing is extracted) and imports them in one transaction: new and changed files are rendered, pages whose file is gone are deleted. A periodic task runs it every hour (`CronTasks`, see [Background-Jobs.md](./Background-Jobs.md)).

- `bin/console catrobat:docs:sync path/to/checkout` (or a `.tar.gz`) imports a local copy, e.g. while writing docs.
- An archive without `en/index.md` is refused, so a broken download never wipes the docs.
- A file with broken front matter or no `title` is skipped and named in the output; the rest is imported.
- A file is rendered again only when it changed, or when it embeds a project (`::project`), whose name and picture can change.

Code: `src/Docs/` (`DocsSync`, `DocsMarkdown`, `DocsLibrary`), entity `DocPage`. The table is a cache of the repo and also the place a later editor in the database would write to: the API and the pages only read it.

## What is imported

| Repo file                                              | Row                                              |
| ------------------------------------------------------ | ------------------------------------------------ |
| `en/index.md`                                          | guide `index`, locale `en` (`/docs`)             |
| `en/tutorials/index.md`                                | guide `tutorials` (a folder's page)              |
| `en/tutorials/jump.md`, `de/tutorials/jump.md`         | guide `tutorials/jump` in `en` and `de`          |
| `reference/bricks/SetXBrick.md` (`de/reference/...`)   | kind `brick`, path `SetXBrick`, for brick pages  |
| anything else (`README.md`, `media/`, `scripts/`, ...) | not imported                                     |

Front matter: `title` (required for guides), `description`, `order` (default 1000), `flavors`, `reviewed`, and for a translation `source_hash`: the sha256 (or a prefix of at least 7 hex digits, quoted) of the English file it was translated from. A translation is `current` while that hash matches today's English file and `stale` after the English page changed; English pages are `source`. Pages show "translated automatically" for current, unreviewed translations and a stale note for stale ones.

## Rendering

[league/commonmark](https://commonmark.thephpleague.com/) with GitHub-flavoured Markdown, `html_input: escape` (raw HTML in a page shows as text) and `allow_unsafe_links: false`. Headings get ids; level 2 and 3 headings make the table of contents.

- **Links between pages** become URLs relative to the page's own URL, so the same HTML works on every theme (`/pocketcode/docs/...`, `/embroidery/docs/...`).
- **Images** point at `raw.githubusercontent.com` at `DOCS_REF`, **other repo files** (PDFs) at their GitHub page. Nothing from the repo is served from share's origin, so a file in the repo can never run as share's content. SVG images do not display this way (GitHub serves them as text): use PNG.
- **Embeds**, each on a line of its own:
  - `::brick{type=SetXBrick}` and `::formula{name=SIN}`: a placeholder carrying the type or name (`data-brick-type`, `data-formula-name`) that shows the name until the brick reference (M2) renders the real brick.
  - `::project{id=…}`: a card with the project's name, author and thumbnail, linking to the project page; a hidden or missing project shows a plain link.
  - `::video{youtube=…}`: a link to the video. The page's Stimulus controller swaps in a `youtube-nocookie.com` player only when the reader clicks it, so nothing loads from YouTube before (CSP `frame-src`).
  - An embed with an unknown kind or invalid value stays visible as text.

## API and pages

- `GET /api/docs?locale=&flavor=`: the navigation tree. A page's parent is the nearest folder above it that has a page.
- `GET /api/docs/pages/{path}?locale=`: title, HTML, table of contents, translation state, `fallback` (true when the English page stands in for a missing translation), the "Improve this page" URL and when the sync last saw the file change.
- `/{theme}/docs` and `/{theme}/docs/{path}` render the same data in Twig: navigation (pages for the theme's flavour first, collapsed on phones), table of contents, previous and next, the fallback or translation note and "Improve this page" (the English file in GitHub's editor). The language is the visitor's, or `?locale=`. `rel=canonical` points at `/app/docs/...` (with `?locale=` for a translation), `hreflang` alternates list every language the page exists in, and the sitemap lists every page in every language.

Tests: `tests/PhpUnit/Docs/`, Behat suites `api-docs` and `web-docs`. Both import `tests/TestData/Docs/`, a small copy of the repo layout.
