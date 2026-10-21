# Catroweb - Project Guide for Claude

Catroweb is the share/communication platform for the Catrobat community: Symfony PHP app with a Vite frontend.

Add real gotchas here when you hit them (not per-feature change notes; nothing the code already says). When code, tooling or setup commands change, update `docs/`, `README.md` and `.github/*.md` in the same PR. Run `yarn run fix-asset` after editing markdown.

## Tech Stack

- **Backend**: PHP 8.5, Symfony 8.0
- **Frontend**: JavaScript (ES6+), plain CSS, Vite 8 (via `pentatrion/vite-bundle`), Stimulus
- **Database**: MariaDB 10.11; **Search**: Elasticsearch 9
- **CSS**: Bootstrap 5, Material Design Components
- **Package Managers**: Composer, Yarn 4 via Corepack (`corepack enable`, `nodeLinker: node-modules`)

## Command Execution

- **yarn commands**: run locally.
- **PHP commands**: native first (`bin/console`, `bin/phpunit`, `bin/phpstan`, `bin/psalm`, `bin/php-cs-fixer`). Fall back to `docker exec app.catroweb ...` only when Docker is confirmed running — the maintainer does not always run Docker.
- CI: `yarn install --immutable`; avoid `actions/setup-node` with `cache: yarn` (calls system Yarn before Corepack).

## Key Commands

```bash
composer install && yarn install
yarn run dev          # Dev build (one-shot)
yarn run build        # Prod build (PurgeCSS + Bugsnag sourcemap upload)
yarn run watch        # vite build --watch
yarn run dev-server   # Vite dev server with HMR on :5173

yarn test / yarn run fix                 # all linters / all fixers
yarn run test-js   / fix-js              # Biome (JS/JSON)
yarn run test-css  / fix-css             # Biome (CSS)
yarn run test-asset / fix-asset          # Prettier (MD/YAML)
yarn run test-php  / fix-php             # PHP CS Fixer
yarn run test-twig / fix-twig            # Twig CS Fixer

yarn run reset        # bin/console catro:reset --hard --limit 20
```

After dependency changes: `composer install` / `yarn install`, then rebuild the Docker image if you use Docker.

## Docker

```bash
docker compose -f docker/docker-compose.dev.yaml up -d
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb   # after dependency changes
```

Ports: app 8080, phpMyAdmin 8081, MariaDB 3306 (`db.catroweb.dev` / `catroweb_dev`, `root`/`root`), Elasticsearch 9200, headless Chrome 9222, Mailpit 8025 (catches all dev mail).

- Volume-mounted: `assets/`, `src/`, `templates/`, `config/`, `tests/`. **Not** mounted: `node_modules/`, `vendor/`, `public/build/`, `behat.yaml.dist`.
- After JS changes for Behat: `yarn run dev && docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/ && docker exec app.catroweb bin/console cache:clear --env=test`.
- When `yarn run dev-server` runs, `pentatrion/vite-bundle` auto-detects it and serves via HMR; otherwise it reads `public/build/.vite/manifest.json`.
- **ES major upgrades**: ES refuses a data volume from a non-adjacent major. Wipe: `docker compose -f docker/docker-compose.dev.yaml rm -sf elasticsearch && docker volume rm docker_esdata1`, restart, `bin/console fos:elastica:populate`.
- **Second stack from a worktree**: `-p <project>` plus a `docker-compose.override.yaml` with `container_name` overrides (base compose hardcodes names) and `ports: !override`.

## Frontend

- New page-level UI behavior goes into Stimulus controllers. Migrate existing vanilla files incrementally when touching them; no new `DOMContentLoaded` bootstrapping where a controller can own the lifecycle.
- Event delegation: use `.closest()`, not `.matches()`.
- Twig → JS data: `data-trans-*` for translations, `data-base-url` for the API prefix; escape with `escapeHtml()`/`escapeAttr()`.
- Write API calls must parse a 403 body: `"Email verification required."` vs `"Your account has been suspended."` vs generic.
- Guest → login → action: store the pending action in `sessionStorage`, validate context (same project ID), remove it immediately after reading.
- Web → API auth: JS sends `Authorization: 'Bearer ' + getCookie('BEARER')`; the `api` firewall is stateless.

### CSS

- Plain CSS only (no Sass). Bootstrap is imported precompiled in `assets/Layout/Base.css`; shared custom properties live in `assets/Layout/Variables.css`.
- Dark mode: use `light-dark(light, dark)`, not `@media (prefers-color-scheme)` (Bootstrap's `data-bs-theme` overrides the media query).
- The `:root { color-scheme: light; }` rule in BootstrapOverwrite.css is **required**: Vite 8's LightningCSS `light-dark()` polyfill only injects toggle vars into rules declaring `color-scheme`; without it every `light-dark()` value is garbage in light mode.
- `--primary` adapts to dark mode (text/borders/links); `--primary-bg` is always dark for filled surfaces with `--on-primary` (#fff) text. MDC uses `--mdc-theme-primary: var(--primary-bg, var(--primary))`.
- WCAG AA: Bootstrap `text-muted` (#6c757d on white, 4.48:1) fails. Use `light-dark(#5a6374, #a0aec0)`. Bootstrap `*-subtle` badges can fail in dark mode; override with explicit `light-dark()` colors.
- No empty rule blocks.

### Themes

9 themes in `vite.config.mjs`. phirocode/pocketalice/mindstorms/pocketgalaxy are content-identical copies of pocketcode; Rollup dedupes them to one CSS file, and the virtual-entry wrapper in `vite/plugins.mjs` keeps a distinct manifest entry per theme.

## PHP

Before committing (all must pass): `bin/php-cs-fixer fix <file>` → `bin/phpstan analyse <file>` → `bin/psalm <file>` → `bin/phpunit --filter ClassName`.

- PHP-CS-Fixer: one file per invocation; use `--using-cache=no` on generated OpenAPI files (CI runs without cache).
- PHPUnit 13: `createStub()` for return values, `createMock()` only with `expects()`. See [CLAUDE_PHPUNIT_MOCKING.md](CLAUDE_PHPUNIT_MOCKING.md).
- Don't stub `Request` (http-foundation ≥ 8.1): request bags are property hooks, a stub with a reflection-injected bag silently bypasses the mock. Use `Request::create($uri)` and assert resulting state.
- `RateLimiterFactory` is `final`: in tests use a real instance with `no_limit` policy + `InMemoryStorage`.
- Doctrine DQL uses property names (`c.isReported`), not column names. `INSTANCE OF` takes no parameters: `'n INSTANCE OF '.Foo::class`.
- Avoid raw SQL in repositories: Behat creates tables via the Doctrine schema tool, names can differ.
- `TranslatorAwareTrait`: `$translator` is private; use `$this->trans('key')` (no domain arg).
- Never set `Location` on a 2xx non-201 response: FastCGI rewrites `200 + Location` into `302` (the dev cli-server doesn't, so only prod breaks). Use `Content-Location`.
- `kernel.terminate` fires in-process in tests; its exceptions propagate.

## API

- OpenAPI spec in `src/Api/OpenAPI/`. After `yarn run generate-api`: `bin/rector process src/Api/OpenAPI/Server/ && bin/php-cs-fixer fix src/Api/OpenAPI/Server/ --using-cache=no`, then fix calling code.
- **Never `$ref` enums in response models** — JMS Serializer emits objects. Inline `type: string` + `enum: [...]`.
- Feature services live in `src/Api/Services/{Feature}/` (Facade, Loader, Processor, RequestValidator, ResponseManager).
- **Register every API handler** in `config/services.php`: `$services->set(XxxApi::class)->tag('open_api_server.api', ['api' => 'xxx'])`. Tag = lowercase OpenAPI tag; missing → 500.
- Cursor pagination: fetch `$limit + 1`, `has_more` if the extra row exists; cursor = `base64_encode((string) $last_id)`; response `{ data, next_cursor, has_more }`.
- Validation: 400 for query parameters, 422 for request bodies.
- `config/packages/security.php`: admin rules and explicit `PUBLIC_ACCESS` GET rules must come BEFORE the `^/api` catch-all.
- `AccountStateEventListener` blocks write calls from unverified/suspended users with 403 (auth, registration, password reset, appeal exempt).
- Rate limiters: `config/packages/rate_limiter.php`, trait `src/Api/RateLimitTrait.php`. Config key `report_burst` → constructor param `$reportBurstLimiter`; admins usually exempt; 429 with null body.

## Moderation

`src/Moderation/`. Non-obvious rules:

- `ContentVisibilityManager` does NOT flush; callers own the transaction.
- Resolving one report resolves all pending reports for the same content; rejected reports don't whitelist content.
- `auto_hidden` (community) and `visible` (admin) are independent flags.
- Polymorphic content uses `content_type` + `content_id` (VARCHAR) instead of Doctrine STI: UUIDs for projects/users/studios, int-as-string for comments.
- Usernames must not contain links (`LinkDetector`, OAuth-derived names too).

## Behat

**Always pass `-s <suite>`** — without it, hooks run 20+ times per test. Suites are defined in `behat.yaml.dist`; a new suite also needs the matrix in `.github/workflows/tests.yaml`, and in Docker a `docker cp behat.yaml.dist app.catroweb:/var/www/catroweb/behat.yaml`.

```bash
docker exec app.catroweb bin/behat -f pretty -s web-admin "tests/BehatFeatures/web/admin/file.feature:206"
```

- Assertions: API (`ApiContext`) `the client response should contain`; web (`CatrowebBrowserContext`) `the response should contain`.
- `the JSON response field :path should be :expected` compares JSON-encoded values: `should be '"member"'`.
- POST endpoints need `And I have a request header "CONTENT_TYPE" with value "application/json"`; JSON bodies go in heredocs (`"""`), not tables.
- Fixtures: admins via `there are admins:`; `created_at` via `the users are created at:`; give studios an `id` for deterministic UUIDs. `setId()` after `persist()` is ignored with AUTO_INCREMENT — insert via DBAL.
- Doctrine ManyToMany inverse collections (`$user->getFollowers()`) come back empty due to identity-map caching; query with DQL JOINs.
- `index_test.php` hardcodes `new Kernel('test', false)`; `APP_DEBUG=1` has no effect. Inspect state at `http://localhost:8080/index_test.php/` after a scenario.
- Mink `locatePath` prepends the front-controller segment even for leading-slash paths; to test that a static file is denied, request the raw origin over HTTP.
- Twig `path()` for `/{theme}/...` routes needs `theme: request_theme|default('pocketcode')` when rendered from API context.
- Flakiness: after async ops wait spinner → `I wait for AJAX to finish` → wait for the element content, then assert. Use `should not exist` when JS calls `element.remove()`. Fixed delays only as last resort.

## Git

- Main branch `main`; feature branches via PR; CI is GitHub Actions.
- New issue in a worktree: `git worktree add .claude/worktrees/<name> -b feature/<issue>-<desc> origin/main`.

## Deploy Gotcha

Protected project files (`/project-files/{id}/assets/{path}`): the nginx `^~ /project-files/` routing prefix must be live before deploying. See `docs/operations/Protected-Project-Files.md`.
