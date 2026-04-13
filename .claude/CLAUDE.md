# Catroweb - Project Guide for Claude

## Important: Keep This Document Updated

**Always update CLAUDE.md with new learnings** discovered during development sessions (gotchas, patterns, API quirks, workarounds). Run `yarn run fix-asset` after updating.

## Documentation Maintenance

When code, tooling, workflows, or setup commands change, update related docs in `docs/`, `README.md`, and `.github/*.md` in the same PR.

## Overview

Catroweb is the share/communication platform for the Catrobat community. Symfony-based PHP app with Webpack Encore frontend.

## Tech Stack

- **Backend**: PHP 8.5, Symfony 7.4
- **Frontend**: JavaScript (ES6+), SCSS, Webpack Encore
- **Database**: MariaDB 10.11
- **Search**: Elasticsearch 8.17
- **CSS**: Bootstrap 5, Material Design Components
- **Package Managers**: Composer (PHP), Yarn (JS)

## Frontend JS Pattern

- Prefer Stimulus controllers for all new page-level UI behavior.
- For existing vanilla files, migrate incrementally when touching related code (no big-bang rewrites).
- Keep page entrypoints lean: avoid new `DOMContentLoaded` bootstrapping when a Stimulus controller can own the lifecycle.

## Command Execution

- **yarn commands**: Run **locally**
- **PHP commands**: Try **native first** (`bin/phpstan`, `bin/psalm`, etc.), fall back to **Docker** (`docker exec app.catroweb ...`)

### Yarn (Berry)

- Corepack (`corepack enable`) manages Yarn 4.12.0
- `.yarnrc.yml` with `nodeLinker: node-modules`
- CI: `yarn install --immutable`; avoid `actions/setup-node` with `cache: yarn` (calls system Yarn before Corepack)

## Key Commands

```bash
# Build
composer install && yarn install
yarn run dev          # Dev build
yarn run build        # Prod build
yarn run watch        # Auto-rebuild on save
yarn run dev-server   # HMR

# Test & Lint
yarn test             # All
yarn run test-js      # ESLint
yarn run test-css     # Stylelint
yarn run test-asset   # Prettier
yarn run test-php     # PHP CS Fixer
yarn run test-twig    # Twig CS Fixer

# Fix
yarn run fix          # All
yarn run fix-js       # JS
yarn run fix-css      # SCSS
yarn run fix-asset    # Assets/Prettier
yarn run fix-php      # PHP
yarn run fix-twig     # Twig

# Database reset
yarn run reset        # or: bin/console catro:reset --hard --limit 20
```

## Docker

```bash
docker compose -f docker/docker-compose.dev.yaml up -d
# After dependency changes:
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
```

| Service                 | Port | Description      |
| ----------------------- | ---- | ---------------- |
| app.catroweb            | 8080 | Main app         |
| db.catroweb.dev         | 3306 | MariaDB (dev)    |
| db.catroweb.test        | 3306 | MariaDB (test)   |
| phpmyadmin.catroweb.dev | 8081 | phpMyAdmin       |
| elasticsearch           | 9200 | Elasticsearch    |
| chrome.catroweb         | 9222 | Headless Chrome  |
| mailpit.catroweb        | 8025 | Email testing UI |

**Volume-mounted**: `assets/`, `src/`, `templates/`, `config/`, `tests/`
**NOT mounted**: `node_modules/`, `vendor/` (built inside container), `public/build/`

After dependency changes, rebuild the container. After JS changes, copy built assets:

```bash
yarn run dev
docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/
docker exec app.catroweb bin/console cache:clear --env=test
```

**Dev DB**: host `db.catroweb.dev`, database `catroweb_dev`, user/password `root`/`root`

**Email testing**: Mailpit catches all outgoing emails in dev. Open http://localhost:8025 to view them (verification emails, parental consent emails, etc.). Configured via `MAILER_DSN=smtp://mailpit:1025` in `.env.dev`.

## Project Structure

```
Catroweb/
├── assets/           # Frontend (SCSS, JS) — Layout/, Components/, User/, Project/, Themes/
├── config/           # Symfony config
├── docker/           # Docker config
├── migrations/       # DB migrations
├── public/           # Web root (build/ = compiled assets)
├── src/              # PHP source
├── templates/        # Twig templates
├── tests/            # Tests
├── translations/     # i18n
├── webpack.config.js # Webpack Encore config
└── package.json      # JS dependencies
```

## SCSS/Sass

- **Dart Sass** v1.97.2, sass-loader 16+ auto-detects compiler
- Bootstrap 5 variables via `assets/Layout/Variables.scss`
- Bootstrap 5.x deprecation warnings (global `mix()`, `shade-color()`) are harmless; fixed in Bootstrap 6

## Themes

9 themes in `webpack.config.js`: pocketcode (default), arduino, create@school, embroidery, luna, phirocode, pocketalice, pocketgalaxy, mindstorms

## Environment Files

`.env` (base), `.env.dev`, `.env.test`, `.env.prod`

## Common Issues

### Doctrine DQL: Use PHP Property Names

DQL uses PHP property names, NOT column names: `c.isReported` (not `c.is_reported`).

### Forcing IDs in Behat Fixtures

`setId()` after `persist()` is ignored with AUTO_INCREMENT. Use raw DBAL SQL instead. See `insertUserComment()` in `ContextTrait.php`.

### Twig path() Inherits Theme from Request Context

Routes with `/{theme}/...` need explicit theme from API context:

```twig
{{ path('route', {id: x, theme: request_theme|default('pocketcode')}) }}
```

### index_test.php Always Uses debug=false

Hardcoded `new Kernel('test', false)`. `APP_DEBUG=1` has no effect. Debug with `file_put_contents('/tmp/debug.txt', ...)`.

### POST Endpoints Require Content-Type in Behat

Always add: `And I have a request header "CONTENT_TYPE" with value "application/json"`

### PHP-CS-Fixer Cache

Always use `--using-cache=no` on generated OpenAPI files. CI runs without cache and catches issues local cache hides.

### DQL INSTANCE OF: No Parameters

```php
// WRONG: $qb->andWhere('n INSTANCE OF :type')->setParameter('type', Foo::class);
// CORRECT: $qb->andWhere('n INSTANCE OF '.Foo::class);
```

### TranslatorAwareTrait

`$translator` is private. Use `$this->trans('key')` (no domain arg; signature: `trans(string $id, array $params = [], ?string $locale = null)`).

### After Changing Dependencies

```bash
# package.json: rm -rf node_modules yarn.lock && yarn install
# composer.json: composer install
# Then rebuild Docker container if using Docker
```

### Never set `Location` on a 2xx non-201 response

PHP's CGI/FastCGI SAPI rewrites `200 OK + Location: ...` into `302 Found`, even though the framework set status 200. This silently breaks client-side `fetch()` that tries `response.json()` after following the redirect to an HTML page. Symfony's PHP built-in server (cli-server SAPI) doesn't do this rewrite, so dev works while prod is broken.

Use `Content-Location` for descriptive metadata on 2xx non-201 responses. Keep `Location` only on `201 Created`.

### PHP-CS-Fixer: Multiple Files

Run on each file separately: `bin/php-cs-fixer fix file1 && bin/php-cs-fixer fix file2`

## PHP Static Analysis & Testing

Run before committing (all four must pass):

1. `bin/php-cs-fixer fix <file>` -- auto-fix style
2. `bin/phpstan analyse <file>` -- static analysis (level max, `src/` only)
3. `bin/psalm <file>` -- type checking
4. `bin/phpunit --filter ClassName` -- unit tests

Use `docker exec app.catroweb` prefix if native commands fail.

**PHPUnit 13**: avoid `expects($this->any())`; don't use `with()` without `expects()`. Prefer `method()` on stubs. See [CLAUDE_PHPUNIT_MOCKING.md](CLAUDE_PHPUNIT_MOCKING.md) for full guidelines.

**Quick Rule**: `createStub()` for return values, `createMock()` only with `expects()`.

## Behat Testing

### Running Tests

**Always use `-s <suite>`** -- without it, hooks run 20+ times per test.

```bash
docker exec app.catroweb bin/behat -f pretty -s web-admin
docker exec app.catroweb bin/behat -f pretty -s web-profile tests/BehatFeatures/web/profile/file.feature
docker exec app.catroweb bin/behat -f pretty -s web-admin "tests/BehatFeatures/web/admin/file.feature:206"
```

### Common Suites

| Suite               | Path / Description                                                  |
| ------------------- | ------------------------------------------------------------------- |
| api-achievements    | api/achievements                                                    |
| api-authentication  | api/authentication                                                  |
| api-comments        | api/comments                                                        |
| api-followers       | api/followers                                                       |
| api-media-library   | api/media-library                                                   |
| api-moderation      | api/moderation                                                      |
| api-notifications   | api/notifications                                                   |
| api-projects        | api/projects                                                        |
| api-projects-get    | api/projects GET\_\* dirs (split)                                   |
| api-projects-write  | api/projects POST,DELETE,PUT,reactions (split)                      |
| api-search          | api/search                                                          |
| api-studio          | api/studio                                                          |
| api-translation     | api/translation                                                     |
| api-user            | api/user                                                            |
| api-utility         | api/utility                                                         |
| web-achievements    | web/achievements                                                    |
| web-admin           | web/admin                                                           |
| web-admin-1         | admin split: projects, featured, approve, login, moderation, maint. |
| web-admin-2         | admin split: db_updater, example, flags, mail, media, survey etc    |
| web-general         | web/general                                                         |
| web-notifications   | web/notifications                                                   |
| web-profile         | web/profile                                                         |
| web-profile-1       | profile split: edit, profile, image, data_export                    |
| web-profile-2       | profile split: follow, user_projects, verification, suspended       |
| web-project-details | web/project-details                                                 |
| web-reactions       | web/reactions                                                       |
| web-reports         | web/reports                                                         |
| web-translation     | web/translation                                                     |

Suite config in `behat.yaml.dist`. Adding a new suite requires updating: `behat.yaml.dist`, `.github/workflows/tests.yaml` (matrix), and this table.

### Assertion Contexts

- **API** (`ApiContext`): `the client response should contain`
- **Web** (`CatrowebBrowserContext`): `the response should contain`

### Debugging

Browse test state at `http://localhost:8080/index_test.php/` after a scenario.

### JS Changes + Behat

After JS changes: `yarn run dev` -> `docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/` -> clear cache -> run tests.

### Preventing Flaky Tests

1. **Layer waits after async ops**: spinner wait -> `I wait for AJAX to finish` -> `I wait for the element ".x" to contain "y"` -> assertion
2. **Wait for content changes**, not just element existence
3. **`should not exist`** when JS calls `element.remove()` (not `should not be visible`)
4. **Fixed delays** (`I wait 1000 milliseconds`) only as last resort
5. High-risk scenarios: visibility toggles, reactions, form submissions, modals, infinite scroll

### Behat Fixtures

- **JSON request body**: use heredoc (`"""`), NOT Gherkin tables
- **Admin users**: `there are admins:` step (not a column in `there are users:`)
- **`created_at`**: use `the users are created at:` step separately
- **Studio IDs**: UUIDs via `MyUuidGenerator`; specify `id` in fixture for deterministic UUIDs
- **SweetAlert2**: radio `#report-cat-{value}`, textarea `#report-note`, confirm `.swal2-confirm`
- **Docker**: `behat.yaml.dist` NOT volume-mounted; copy with `docker cp behat.yaml.dist app.catroweb:/var/www/catroweb/behat.yaml` after adding suites

## Development Workflow

| Change Type   | Run                                                 |
| ------------- | --------------------------------------------------- |
| PHP src/      | php-cs-fixer -> phpstan -> psalm -> phpunit         |
| PHP tests/    | php-cs-fixer -> phpstan -> psalm -> phpunit         |
| JS/CSS/Assets | fix-js -> fix-css -> fix-asset, then `yarn run dev` |
| API endpoints | PHPUnit + Behat API suite                           |
| UI/Templates  | Behat web tests                                     |

## JavaScript Patterns

### DOMContentLoaded

Webpack Encore's `defer` scripts run before `DOMContentLoaded` -- the standard `addEventListener('DOMContentLoaded', fn)` pattern works. Fallback for edge cases:

```javascript
document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn()
```

### Event Delegation

Use `.closest()` not `.matches()` to handle clicks on child elements.

### Guest -> Login -> Action Pattern

Store pending action in `sessionStorage`, redirect to login, execute after login. Key rules:

- `sessionStorage` (not `localStorage`) -- doesn't persist across sessions
- Validate context (e.g., same project ID) before executing
- Remove item immediately after reading
- Execute synchronously during page init

### JS 403 Error Handling

All write API calls must parse 403 body to distinguish:

- `"Email verification required."` -> verify email message
- `"Your account has been suspended."` -> suspension message
- Other -> generic error

Data attributes: `data-trans-account-suspended`, `data-trans-report-suspended`

## Git Workflow

- Main branch: `develop`
- Feature branches merged via PR
- CI: GitHub Actions (static + dynamic analysis)

### Worktrees for New Issues

```bash
git fetch origin develop
git worktree add .claude/worktrees/<name> -b feature/<issue>-<desc> origin/develop
# Work in .claude/worktrees/<name>/
git worktree remove .claude/worktrees/<name>  # cleanup
```

### Second Docker from Worktree

Use `-p <project>` with `docker-compose.override.yaml` for unique container names and non-conflicting ports. The `container_name` overrides are required (base compose has hardcoded names). Use `ports: !override` (compose v5+).

## API

- OpenAPI spec in `src/Api/OpenAPI/`
- Generate: `yarn run generate-api`

### After `yarn run generate-api`

```bash
bin/rector process src/Api/OpenAPI/Server/
bin/php-cs-fixer fix src/Api/OpenAPI/Server/ --using-cache=no
```

Then update calling code (method signatures may change).

### OpenAPI Enum Serialization

**Never use `$ref` for enums in response models** -- JMS Serializer serializes them as objects. Use inline `type: string` + `enum: [...]` instead.

### Architecture

Facade pattern per feature in `src/Api/Services/{Feature}/`:

- `{Feature}ApiFacade.php` -- orchestration
- `{Feature}ApiLoader.php` -- data loading
- `{Feature}ApiProcessor.php` -- business logic
- `{Feature}RequestValidator.php` -- validation
- `{Feature}ResponseManager.php` -- response formatting

### Registering API Handlers (Critical!)

```php
$services->set(XxxApi::class)->tag('open_api_server.api', ['api' => 'xxx']);
```

Tag value must match OpenAPI tag name (lowercase). Without this -> 500.

### Cursor-Based Pagination

Fetch `$limit + 1`; if extra exists, `has_more = true` + `array_pop()`. Cursor = `base64_encode((string) $last_id)`. Response: `{ data, next_cursor, has_more }`. See `CommentsApi`, `NotificationsApi`.

### HTTP Validation Status Codes

- **400**: query parameter failures
- **422**: request body failures

### Security Config

Admin endpoint rules MUST come BEFORE the `^/api` catch-all in `config/packages/security.php`. Public GET endpoints need explicit `PUBLIC_ACCESS` before the catch-all.

### AccountStateEventListener

`kernel.request` listener blocks write API calls (POST/PUT/DELETE/PATCH) for unverified/suspended users -> 403. Exempt: auth, registration, password reset, appeal endpoints.

### Polymorphic Content Pattern

Moderation uses `content_type` (enum) + `content_id` (VARCHAR) instead of Doctrine STI. UUIDs for projects/users/studios, int-as-string for comments.

### Template Whitelist/Hidden Conditions

Report buttons hidden when whitelisted OR auto-hidden:

- **Project**: `not my_project and not is_whitelisted and not project.autoHidden`
- **User**: `not profile.approved`
- **Comment**: `not comment.user_approved|default(false)`

## Moderation System

Located in `src/Moderation/`. Key services: `TrustScoreCalculator`, `ReportProcessor`, `AppealProcessor`, `AutoModerationService`, `ContentVisibilityManager`.

Key rules:

- Auto-hide at cumulative trust >= 10.0; min trust to report = 0.5
- `ContentVisibilityManager` does NOT flush (callers own transaction)
- Batch resolution: accepting/rejecting one report resolves ALL pending reports for same content
- Cascade: banning user hides all their projects and comments (bulk DQL UPDATE)
- Trust accuracy: +1.5 accepted, -2.0 rejected, clamped [-5.0, +5.0]
- Rejected reports don't whitelist content
- `auto_hidden` (community) and `visible` (admin) are independent

## Rate Limiting

Config: `config/packages/rate_limiter.php` (sliding_window). Trait: `src/Api/RateLimitTrait.php`.

| Limiter                | Limit | Interval | Key  |
| ---------------------- | ----- | -------- | ---- |
| `report_burst`         | 3     | 15min    | User |
| `report_daily`         | 10    | 24h      | User |
| `comment_burst`        | 5     | 5min     | User |
| `comment_daily`        | 50    | 24h      | User |
| `reaction_burst`       | 30    | 5min     | User |
| `follow_burst`         | 20    | 5min     | User |
| `appeal_daily`         | 3     | 24h      | User |
| `upload_daily`         | 10    | 24h      | User |
| `auth_burst`           | 10    | 15min    | IP   |
| `registration_burst`   | 3     | 1h       | IP   |
| `password_reset_burst` | 5     | 1h       | IP   |
| `search_burst`         | 30    | 1min     | IP   |
| `studio_create_daily`  | 5     | 24h      | User |

Wiring: config key `report_burst` -> constructor param `$reportBurstLimiter`. Admins typically exempt. Return 429 with null body.

**Testing**: `RateLimiterFactory` is `final` -- use real instance with `no_limit` policy + `InMemoryStorage` in tests.

## Web -> API: JWT Authentication

Web pages use `BEARER` cookie. API calls from JS need `Authorization: 'Bearer ' + getCookie('BEARER')`. The `api` firewall is stateless.

After form login, `FormLoginSuccessHandler` sets BEARER+REFRESH_TOKEN cookies (preserves `_target_path` redirect). The `/app/login` page uses `LoginTokenHandler.js` which POSTs to `/api/authentication` directly.

### Login Redirect

`LoginTokenHandler.js` reads `#target-path` value. Accepts absolute same-origin URLs via `new URL(rawValue, origin)` + isSameOrigin check.

## Server-to-Client Migration Pattern

1. Add OpenAPI endpoints + schemas -> `yarn run generate-api` -> CS fix
2. Create `src/Api/Services/{Feature}/` (Facade, Loader, Processor, ResponseManager, Validator)
3. Create `src/Api/{Feature}Api.php` implementing generated interface
4. **Register in `config/services.php`** (easy to forget!)
5. Strip controller to thin shell, strip Twig to containers with `data-*` attributes
6. Rewrite JS: fetch API, render DOM client-side
7. Update sidebar badge to use API count endpoint
8. Delete dead templates, update Behat tests (add AJAX waits)
9. Write PHPUnit + API Behat tests

## Miscellaneous Gotchas

- **`kernel.terminate` in tests**: fires in-process, exceptions propagate. Use `$json['key'] ?? null` for optional keys.
- **Comment pagination**: ASC order (oldest first), cursor uses `gt` condition.
- **Machine translation URL**: extract comment ID with regex `preg_match('/\/comments\/(\d+)\//', $path, $m)` (not last segment).
- **Avoid raw SQL in repositories**: Behat creates tables via Doctrine schema tool with potentially different names. Use DQL with entity class references.
- **Doctrine ManyToMany inverse collections in Behat**: `$user->getFollowers()` returns empty due to identity map caching. Use DQL JOIN queries instead.
- **Sidebar `updateBadge()`**: `apiToCall='old'` reads `data.count`, `'new'` reads `data.total`. `badgeText=null` shows count number.
- **Twig data attributes for client-side**: `data-trans-*` for translations, `data-base-url` for API prefix. Include `escapeHtml()`/`escapeAttr()` in JS.
