# User docs

The user documentation is written in `user-docs/` of this repo (Markdown, reviewed in pull requests, same layout as the archived Catrobat/docs repo: `en/`, `de_DE/`, `reference/bricks/`, `media/`, …) and shown on share at `/{theme}/docs`. Share keeps a rendered copy in the `doc_page` table. The plan behind it is epic #7772.

## Sync

`bin/console catrobat:docs:sync` reads `user-docs/` (`catrobat.user_docs.dir`) and imports its Markdown files in one transaction: new and changed files are rendered, pages whose file is gone are deleted. It runs

- on every deploy (`deploy.php`, task `update:docs`, after the migrations): the database is written **before** `deploy:symlink`, so for a moment the old release shows the new docs (harmless: the table layout is the same). `deploy:rollback` does **not** sync again: after a rollback run `bin/console catrobat:docs:sync` in the current release.
- from the docs publish workflow (added by the content-move PR): it copies `user-docs/` into the current release and then syncs, so a docs change goes live, media included, without a full deploy.

Details:

- `bin/console catrobat:docs:sync path/to/folder` imports another folder with the same layout. Without a docs folder the command does nothing.
- A folder without `en/index.md` is refused, so a broken checkout never wipes the docs.
- A lock (`docs-sync`) keeps two syncs from running at once (a deploy and the publish workflow); the second one waits.
- A file with broken front matter or no `title` is skipped and named in the output; the rest is imported.
- A file is rendered again only when it changed, or when it embeds a project (`::project`), whose name and picture can change, or a brick or formula element (`::brick`, `::formula`), which a newer catalog can show differently.

Code: `src/Docs/` (`DocsSync`, `DocsMarkdown`, `DocsLibrary`, `DocsCatalog`), entity `DocPage`. The table is a cache of the folder and also the place a later editor in the database would write to: the API and the pages only read it.

## What is imported

| File in `user-docs/`                                   | Row                                             |
| ------------------------------------------------------ | ----------------------------------------------- |
| `en/index.md`                                          | guide `index`, locale `en` (`/docs`)            |
| `en/tutorials/index.md`                                | guide `tutorials` (a folder's page)             |
| `en/tutorials/jump.md`, `de_DE/tutorials/jump.md`      | guide `tutorials/jump` in `en` and `de_DE`      |
| `reference/bricks/SetXBrick.md` (`de_DE/reference/…`)  | kind `brick`, path `SetXBrick`, for brick pages |
| anything else (`README.md`, `media/`, `scripts/`, ...) | not imported                                    |

Front matter: `title` (required for guides), `description`, `order` (default 1000), `flavors`, `reviewed`, and for a translation `source_hash`: the sha256 (or a prefix of at least 7 hex digits, quoted) of the English file it was translated from. A translation is `current` while that hash matches today's English file and `stale` after the English page changed; English pages are `source`. Pages show "translated automatically" for current, unreviewed translations and a stale note for stale ones.

Translation folders are named like share's translation files (`de_DE`, `pt_BR`, `sr_Latn`).

## Languages

A visitor's language comes from the `hl` cookie or Accept-Language (`de`, `de_AT`, `zh_TW`), the API's from `?locale=` or Accept-Language. `DocsLibrary::locales()` tries, in order: the locale itself, its `_`/`-` forms, the bare language, share's own locale for that language (the mapping in `TranslatorAwareTrait`: `de` → `de_DE`, `pt` → `pt_BR`, `zh` → `zh_CN`), then English. `de_AT` thus finds `de_DE`. Navigation and pages use the same order; a page shown in English instead of the visitor's language says so.

## Rendering

[league/commonmark](https://commonmark.thephpleague.com/) with GitHub-flavoured Markdown, `html_input: escape` (raw HTML in a page shows as text) and `allow_unsafe_links: false`. Headings get ids (`SlugNormalizer`, duplicates numbered `-1`, `-2`); level 2 and 3 headings make the table of contents. `user-docs/scripts/check.py` (`slug()`, `heading_ids()`) replicates these ids to check link anchors: change both together.

- **Links between pages** become URLs relative to the page's own URL, so the same HTML works on every theme (`/pocketcode/docs/...`, `/embroidery/docs/...`). A link to `reference/bricks/<type>.md` leads to the brick's page on share.
- **Images and PDFs** in `media/` are served by share, `/{theme}/docs/_media/<path>` (a guide path segment never starts with `_`, so media never shadow a page), so a page view sends nothing to a third party. **Other files** link to their GitHub page (`Catrobat/Catroweb`, `main`).
- **Embeds**, each on a line of its own:
  - `::brick{type=SetXBrick}` and `::formula{name=SIN}`: the brick or formula element as the reference shows it, in the page's language, linked to its reference page. A type or name the catalog does not know shows as its name.
  - `::project{id=…}`: a card with the project's name, author and thumbnail, linking to the project page; a hidden or missing project shows a plain link.
  - `::video{youtube=…}`: a link to the video. The page's Stimulus controller swaps in a `youtube-nocookie.com` player only when the reader clicks it, so nothing loads from YouTube before (CSP `frame-src`).
  - An embed with an unknown kind or invalid value stays visible as text.

## Media

Share never loads docs media from a third party: every page view would hand the visitor's IP to it, and most visitors are children.

- `DocsController::media` serves files straight from `user-docs/media/` of the running release, so they always match the deployed pages.
- Only the writer guide's image types and PDF are served (`png`, `jpg`, `jpeg`, `gif`, `webp`, `svg`, `pdf`, `DocsSync::MEDIA_TYPES`). Every path segment must be a plain name (letters, digits, `.`, `_`, `-`, not starting with `.`, no `..`), and the file's real path must lie inside `media/`, so neither a URL nor a linked file or folder reaches outside it.
- Content type of the extension, `nosniff` and a day of public caching. SVG is served as `image/svg+xml` (it works in `<img>`) with `Content-Security-Policy: sandbox; default-src 'none'`, the same policy as SVGs in project files, so opening one directly cannot run script on share's origin.

## API and pages

- `GET /api/docs?locale=&flavor=`: the navigation tree. A page's parent is the nearest folder above it that has a page.
- Without `?locale=` the API answers in the request's language and sends `Cache-Control: private`, so a CDN never hands one visitor's language to another; with it the answer is `public`.
- `GET /api/docs/pages/{path}?locale=`: title, HTML, table of contents, translation state, `fallback` (true when the English page stands in for a missing translation), the "Improve this page" URL and when the sync last saw the file change.
- `/{theme}/docs` and `/{theme}/docs/{path}` render the same data in Twig: navigation (pages for the theme's flavour first, collapsed on phones), table of contents, previous and next, the fallback or translation note and "Improve this page" (the English file in GitHub's editor: `github.com/Catrobat/Catroweb/edit/main/user-docs/…`). The language is the visitor's, or `?locale=`. `rel=canonical` points at `/app/docs/...` (with `?locale=` for a translation), `hreflang` alternates list every language the page exists in, and the sitemap lists every page in every language.

## Comments

Readers can leave comments and tips below a page (`templates/Docs/Comments.html.twig`, Stimulus controller `docs--comments`); good tips go into the guides by a docs pull request.

- A comment is a `user_comment` row with `doc_path` set to the page path (`tutorials/jump`), not a `doc_page` id: the rows are a per-language cache the sync replaces, and one page shares its comments across all languages.
- `GET`/`POST /api/docs/pages/{path}/comments` list and create them (a reply has `parent_id`, on the same page); everything else is the generic comment API (`/api/comments/{id}/replies`, delete, report, translation). Writing follows the project comments: logged in, verified and not suspended (`AccountStateEventListener`), not a minor, the comment rate limits, `TextSanitizer`.
- Reports, the moderation queue, hiding and appeals treat them as any `comment`. The queue's link opens the docs page.
- Every admin is a docs maintainer and gets a notification (`DocCommentNotification`) for each new comment.
- A moderation notification about a docs comment (hidden, warned) links its author to the docs page (`url`).
- **Rollback** to a release before docs comments: run `DELETE FROM CatroNotification WHERE notification_type = 'doc_comment';` (a rollback does not run the migration's `down()`). The old code has no `doc_comment` notification type, so an admin's notification page fails while such rows exist.
- Another docs page shows comments with `{{ include('Docs/Comments.html.twig', {doc_path: …}) }}` and the controller registered in its entry; brick pages do so with `reference/bricks/<type>`. The API accepts the path of a guide that has an English page and `reference/bricks/<type>` for a type the brick catalog knows (`CommentsApi::isCommentableDocPage`).

Tests: `tests/PhpUnit/Docs/`, Behat suites `api-docs` and `web-docs`. Both use `tests/TestData/Docs/`, a small folder with the same layout (`catrobat.user_docs.dir` in the test environment).

## Brick reference

The reference is generated, nothing in it is kept by hand. The player build writes `code-export/catalog.json` ([player/README.md](../../player/README.md), "Brick catalog"): every category of Catroid's brick picker with its bricks, written like the code export, and the formula editor's functions, sensors and operators. Its strings are markers, so `DocsCatalog` localises it with the code view's `CodeViewLocalizer` and `CodeTreeBuilder` and Catroid's own `strings/<locale>.json`: one catalog serves every language. It reads the catalog next to the deployed code export (`catrobat.code_export.dir`) and caches each language's copy (`cache.app`) until a new `catalog.json` is deployed. Without a player build the reference is empty.

- A brick's key is its code.xml type (`SetXBrick`, `StartScript` for a script). A brick may be listed in several categories, as in the app; its own is the one with the most bricks of its colour.
- Bricks look like the code view's: `DocsCatalog::html()` writes the markup of `renderBrick` in `assets/Project/CodeViewInline.js`, styled by `CodeViewInline.css`, and both read the category colours from `assets/Project/CodeViewCategories.json`.
- `GET /api/docs/reference/catalog?locale=`: categories with their localised bricks and colour, and the formula elements. A docs check can validate brick types against it.
- `GET /api/docs/reference/bricks/{type}?locale=`: the brick, its categories, its explanation (`user-docs/reference/bricks/<type>.md`, in the best language as for pages, `fallback` when English stands in) with its translation state, and the "Improve this page" URL: the English file in GitHub's editor, or a new file there when the brick has none. An unknown type is 404, a type that is not a class name (`^[A-Za-z][A-Za-z0-9_]{0,99}$`) is refused. Cache rule as above (`private` without `?locale=`).
- `/{theme}/docs/reference` lists the categories in their colours, `/{theme}/docs/reference/bricks/{type}` shows the brick, its explanation, its example projects ([Brick-Examples.md](./Brick-Examples.md)) and "Improve this page", `/{theme}/docs/reference/formula/{name}` names a formula element and its kind. They exist in every language share speaks: `hreflang` lists all of them, `rel=canonical` points at `/app/docs/...` (with `?locale=` in share's own locale code). The sitemap lists the English pages. The docs navigation links the reference after the guides.

### Old wiki URLs

The old brick help (`wiki.catrobat.org/bin/view/Documentation/BrickDocumentation/<Class>`) named bricks by their Catroid class. `/app/docs/reference/bricks/by-class/<Class>` answers with a 301 to the brick's page: a class that is a type goes to its page, a script brick's class to its script's (`WhenStartedBrick` → `StartScript`, `WhenClonedBrick` → `WhenClonedScript`); an unknown class (a brick Catroid no longer has) gets a 302 to the reference.

To make the old URLs work, `wiki.catrobat.org` needs a DNS record proxied by Cloudflare (no origin needed) and a Cloudflare redirect rule (Rules → Redirect Rules, dynamic):

- When: `http.host eq "wiki.catrobat.org" and starts_with(http.request.uri.path, "/bin/view/Documentation/BrickDocumentation/")`
- Then: 301 to `concat("https://share.catrobat.org/app/docs/reference/bricks/by-class/", substring(http.request.uri.path, 43))`, query string not kept. Every other wiki URL can go to `https://share.catrobat.org/app/docs`.

Tests: `tests/PhpUnit/Docs/DocsCatalogTest.php` (`tests/TestData/CodeExport/catalog.json` is a subset of a real catalog; a real one in `var/code-export/` is checked whole when present) and the Behat features `api/docs/GET_docs_reference.feature` (every brick of the catalog in `en` and `de`: page and API answer exist, no string left as a marker or bare name) and `web/docs/docs_reference.feature`.
