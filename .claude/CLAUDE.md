# Catroweb - Project Guide for Claude

## Important: Keep This Document Updated

**Always update CLAUDE.md with new learnings** discovered during development sessions. This includes:

- Gotchas and non-obvious behaviors
- Patterns that work vs patterns that don't
- Correct tool/command usage discovered through trial and error
- API quirks and workarounds
- Documentation gaps or stale instructions found during implementation

This ensures future sessions benefit from past discoveries. Also run `yarn run fix-asset` after updating.

## Documentation Maintenance

When code, tooling, workflows, or setup commands change, review related docs in `docs/`, `README.md`, and `.github/*.md`.
Update any stale guidance in the same PR/branch instead of deferring doc fixes.

## Overview

Catroweb is the share/communication platform for the Catrobat community. It's a Symfony-based PHP application with a modern frontend using Webpack Encore.

## Tech Stack

- **Backend**: PHP 8.5, Symfony 7.4
- **Frontend**: JavaScript (ES6+), SCSS, Webpack Encore
- **Database**: MariaDB 10.11
- **Search**: Elasticsearch 7.17
- **CSS Framework**: Bootstrap 5, Material Design Components
- **Package Managers**: Composer (PHP), Yarn (JS)

## Command Execution Environment

- **yarn commands**: Run **locally** (yarn, eslint, stylelint, prettier, webpack)
- **PHP commands**: Try **native first** (`bin/phpstan`, `bin/psalm`, etc.), fall back to **Docker** if not available

### Yarn (Berry) Notes

- Use Corepack (`corepack enable`) to manage Yarn 4.12.0.
- Keep `.yarnrc.yml` with `nodeLinker: node-modules` for compatibility.
- Prefer `yarn install --immutable` in CI/Docker.
- In GitHub Actions, avoid `actions/setup-node` with `cache: yarn` because it calls the system Yarn before Corepack; use manual cache + Corepack instead.

```bash
# Prefer native if available
bin/php-cs-fixer fix src/Path/To/File.php
bin/phpstan analyse src/Path/To/File.php
bin/psalm src/Path/To/File.php
bin/phpunit --filter ClassName

# Fall back to Docker if native commands don't work
docker exec app.catroweb bin/php-cs-fixer fix src/Path/To/File.php
docker exec app.catroweb bin/phpstan analyse src/Path/To/File.php
# etc.
```

## Key Commands

### Build & Development

```bash
# Install dependencies
composer install
yarn install

# Development build
yarn run dev

# Production build
yarn run build

# Watch mode (auto-rebuild on changes)
yarn run watch

# Development server with hot reload
yarn run dev-server
```

### Testing & Linting

```bash
# Run all tests
yarn test

# Individual test commands
yarn run test-js      # ESLint
yarn run test-css     # Stylelint for SCSS
yarn run test-asset   # Prettier
yarn run test-php     # PHP CS Fixer
yarn run test-twig    # Twig CS Fixer

# Fix commands
yarn run fix          # Fix all
yarn run fix-js       # Fix JS
yarn run fix-css      # Fix SCSS
yarn run fix-asset    # Fix assets
yarn run fix-php      # Fix PHP
yarn run fix-twig     # Fix Twig
```

### Symfony Console

```bash
# Reset database with sample data (limit 20 projects)
yarn run reset
# or
bin/console catro:reset --hard --limit 20
```

## Docker Setup

### Start Development Environment

```bash
# Build and start all containers
docker compose -f docker/docker-compose.dev.yaml up -d

# Build with no cache (after dependency changes)
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
docker compose -f docker/docker-compose.dev.yaml up -d
```

### Container Services

| Service                 | Port | Description                 |
| ----------------------- | ---- | --------------------------- |
| app.catroweb            | 8080 | Main application            |
| db.catroweb.dev         | 3306 | MariaDB (dev)               |
| db.catroweb.test        | 3306 | MariaDB (test)              |
| phpmyadmin.catroweb.dev | 8081 | phpMyAdmin                  |
| elasticsearch           | 9200 | Elasticsearch               |
| chrome.catroweb         | 9222 | Headless Chrome (E2E tests) |

### Docker Volumes

The docker-compose.dev.yaml mounts these directories for live editing:

- `assets/` - Frontend assets (SCSS, JS)
- `src/` - PHP source code
- `templates/` - Twig templates
- `config/` - Symfony configuration
- `tests/` - Test files

**Note**: `node_modules/` and `vendor/` are NOT shared - they're built inside the container. After yarn/composer changes, rebuild the container.

## Project Structure

```
Catroweb/
├── assets/                 # Frontend source files
│   ├── Layout/            # Global SCSS (Variables, Base)
│   ├── Components/        # Reusable component styles
│   ├── User/              # User-related pages
│   ├── Project/           # Project-related pages
│   └── Themes/            # Theme-specific overrides
├── config/                # Symfony configuration
├── docker/                # Docker configuration
│   ├── Dockerfile
│   └── docker-compose.dev.yaml
├── migrations/            # Database migrations
├── public/                # Web root
│   └── build/            # Compiled assets (generated)
├── src/                   # PHP source code
├── templates/             # Twig templates
├── tests/                 # Test files
├── translations/          # i18n files
├── webpack.config.js      # Webpack Encore config
└── package.json           # js dependencies
```

## SCSS/Sass

- Uses **Dart Sass** (`sass` package, v1.97.2)
- 61 SCSS files in `assets/` directory
- sass-loader 16+ auto-detects the sass compiler
- Bootstrap 5 variables imported via `assets/Layout/Variables.scss`

### Deprecation Warnings

Bootstrap 5.x uses some deprecated Sass features (global functions like `mix()`, `shade-color()`). These show warnings but don't break the build. They'll be fixed in Bootstrap 6.

## Themes

The application supports 9 themes (configured in webpack.config.js):

1. pocketcode (default)
2. arduino
3. create@school
4. embroidery
5. luna
6. phirocode
7. pocketalice
8. pocketgalaxy
9. mindstorms

## Environment Files

- `.env` - Base environment variables
- `.env.dev` - Development overrides
- `.env.test` - Test overrides
- `.env.prod` - Production overrides

## Database

Default dev credentials (from docker-compose):

- Host: `db.catroweb.dev`
- Database: `catroweb_dev`
- User: `root`
- Password: `root`

## Common Issues

### Doctrine DQL Uses PHP Property Names (Not Column Names)

In DQL queries, always use the PHP property name, not the database column name:

```php
// WRONG: $isReported property has column name "isReported" but DQL needs property name
'c.is_reported'  // ❌ fails with "no field named is_reported"

// CORRECT: use the PHP property name
'c.isReported as is_reported'  // ✓ works, aliases to is_reported for array access
```

Rule: DQL uses PHP property names (e.g., `isReported`) not snake_case column names.

### Forcing IDs in Behat Fixtures (MySQL AUTO_INCREMENT)

`setId()` after `persist()` is ignored by Doctrine's `AUTO` strategy. To force specific IDs in test fixtures, use raw DBAL SQL:

```php
$em->getConnection()->executeStatement(
    'INSERT INTO user_comment (id, ...) VALUES (:id, ...)',
    ['id' => $forced_id, ...]
);
$entity = $em->find(UserComment::class, $forced_id);
```

See `insertUserComment()` in `ContextTrait.php` for the implementation pattern.

### Twig path() Inherits Theme from Current Request Context

Routes with `/{theme}/...` prefix require explicit `theme` in `path()` when rendering from a non-themed context (e.g., API):

```twig
{# WRONG: inherits "api" from URL /api/... which fails validation #}
{{ path('project_comment', {id: comment.id}) }}

{# CORRECT: explicit theme with fallback #}
{{ path('project_comment', {id: comment.id, theme: request_theme|default('pocketcode')}) }}
```

When rendering Twig from an API controller, the router context has no valid theme, so the path generator inherits the URL prefix (e.g., "api"), which fails route validation.

### index_test.php Always Uses debug=false

`public/index_test.php` hardcodes `new Kernel('test', false)` — the second arg is debug mode. Setting `APP_DEBUG=1` in `.env.test` does NOT affect this. Error responses always show minimal info (no stack traces). For debugging 500s, add temporary `file_put_contents('/tmp/debug.txt', ...)` logging.

### POST Endpoints Require Content-Type in Behat

`ApiContext::iHaveTheFollowingJsonRequestBody()` only sets the request body, NOT the Content-Type header. The CommentsController returns 415 if Content-Type is not `application/json`. Always add:

```gherkin
And I have a request header "CONTENT_TYPE" with value "application/json"
```

### PHP-CS-Fixer Cache Hides Issues (Use --using-cache=no)

Local `bin/php-cs-fixer fix` uses a `.php-cs-fixer.cache` file. If generated files were already cached as "clean", changes to those files won't be re-checked. CI runs without cache, so it catches issues that local runs miss.

**Always use `--using-cache=no` when checking generated OpenAPI files:**

```bash
bin/php-cs-fixer fix src/Api/OpenAPI/Server/ --using-cache=no
```

Common issue: after `yarn run generate-api`, the generated files may have `string_implicit_backslashes` violations that the cached local fixer won't detect.

### Doctrine DQL INSTANCE OF Does NOT Support Parameters

DQL's `INSTANCE OF` operator requires class names directly in the query string — it does NOT support bound parameters:

```php
// WRONG: INSTANCE OF does not support parameters
$qb->andWhere('n INSTANCE OF :type')->setParameter('type', LikeNotification::class);

// CORRECT: concatenate class name directly
$qb->andWhere('n INSTANCE OF '.LikeNotification::class);
```

### TranslatorAwareTrait: Use trans(), Not $translator

`AbstractResponseManager` uses `TranslatorAwareTrait`. The `$translator` property is **private** in the trait — you cannot access it directly. Use the trait's `trans()` method instead:

```php
// WRONG: $translator is private in TranslatorAwareTrait
$this->translator->trans('key', [], 'catroweb');

// CORRECT: use the trait method (no domain argument — 3rd arg is locale, not domain)
$this->trans('catro-notifications.like.message');
```

Note: The `trans()` method signature is `trans(string $id, array $parameters = [], ?string $locale = null)` — there is no `$domain` parameter. The domain is set elsewhere.

### After changing package.json

```bash
# Local development
rm -rf node_modules yarn.lock
yarn install

# Docker - rebuild container
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
docker compose -f docker/docker-compose.dev.yaml up -d
```

### After changing composer.json

```bash
# Local
composer install

# Docker - rebuild container
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
```

### Check if app is running

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
```

## PHP Static Analysis & Testing

When making PHP changes, run these tools before committing:

```bash
# PHP CS Fixer - code style (run first, auto-fixes issues)
docker exec app.catroweb bin/php-cs-fixer fix
docker exec app.catroweb bin/php-cs-fixer fix src/Path/To/File.php

# PHPStan - static analysis (level max)
docker exec app.catroweb bin/phpstan analyse
docker exec app.catroweb bin/phpstan analyse src/Path/To/File.php

# Psalm - type checking
docker exec app.catroweb bin/psalm
docker exec app.catroweb bin/psalm src/Path/To/File.php

# PHPUnit - unit tests
docker exec app.catroweb bin/phpunit --filter ClassName
```

Local equivalents (non-Docker):

```bash
composer run fix
./bin/phpstan analyse
./bin/psalm
./bin/phpunit
```

Notes:

- PHPUnit 13: avoid `expects($this->any())` and don't use `with()` without `expects()`. Prefer `method()` on stubs/mocks; if you need argument matching, use `createMock()` with explicit expectations.
- PHPStan is configured to analyze `src/` only (tests are excluded to avoid PHPUnit static-analysis noise).

**All four tools must pass before merging.**

## Behat Testing

### Running Behat Tests

**IMPORTANT: Always use the `-s <suite>` flag when running Behat tests.** Without it, hooks run once for each suite (20+ times) even for a single test, making tests extremely slow.

```bash
# Run all tests for a specific suite
docker exec app.catroweb bin/behat -f pretty -s web-admin

# Run a specific feature file (ALWAYS include -s <suite>)
docker exec app.catroweb bin/behat -f pretty -s web-profile tests/BehatFeatures/web/profile/profile_user_projects.feature

# Run a specific scenario by line number (ALWAYS include -s <suite>)
docker exec app.catroweb bin/behat -f pretty -s web-admin "tests/BehatFeatures/web/admin/featured_programs.feature:206"
```

### Common Suites

| Suite               | Path                                    |
| ------------------- | --------------------------------------- |
| web-admin           | tests/BehatFeatures/web/admin           |
| web-profile         | tests/BehatFeatures/web/profile         |
| web-general         | tests/BehatFeatures/web/general         |
| web-translation     | tests/BehatFeatures/web/translation     |
| web-project-details | tests/BehatFeatures/web/project-details |
| web-reactions       | tests/BehatFeatures/web/reactions       |
| api-projects        | tests/BehatFeatures/api/projects        |
| api-authentication  | tests/BehatFeatures/api/authentication  |
| api-comments        | tests/BehatFeatures/api/comments        |
| api-notifications   | tests/BehatFeatures/api/notifications   |
| api-achievements    | tests/BehatFeatures/api/achievements    |
| web-notifications   | tests/BehatFeatures/web/notifications   |
| web-achievements    | tests/BehatFeatures/web/achievements    |
| api-moderation      | tests/BehatFeatures/api/moderation      |
| web-reports         | tests/BehatFeatures/web/reports         |

Suite configuration is in `behat.yaml.dist`.

### Behat Assertion Contexts

Different contexts use different assertion step definitions:

- **API tests** (`ApiContext`): Use `the client response should contain` for JSON response assertions
- **Web tests** (`CatrowebBrowserContext`): Use `the response should contain` for HTML page assertions

```gherkin
# API test (JSON)
And the client response should contain "total"
And the client response should not contain "Catrobat"

# Web test (HTML)
And the response should contain "Welcome"
```

### Debugging Behat Tests

After running a Behat scenario, you can browse the test state live at:
`http://localhost:8080/index_test.php/`

This helps debug failing tests by seeing the actual page state.

### JavaScript Changes and Behat Tests

**CRITICAL:** After making JavaScript changes in `assets/`, you MUST run `yarn run dev` to compile the changes before running Behat tests. The Docker container serves the compiled assets from `public/build/`, not the source files.

**IMPORTANT:** `public/build/` is NOT volume-mounted into the Docker container — only specific files from `public/` are shared (like `index.php` and `index_test.php`). After building JS locally with `yarn run dev`, you must copy the built assets into the container:

```bash
# Always do this after JS changes:
yarn run dev
docker cp public/build/. app.catroweb:/var/www/catroweb/public/build/
docker exec app.catroweb bin/console cache:clear --env=test
docker exec app.catroweb bin/behat -f pretty -s web-reactions "tests/BehatFeatures/..."
```

Also clear the Symfony cache (especially `--env=test` for Behat) after copying — stale cache can cause template/routing issues even when the assets are correct.

If Behat tests fail after JavaScript changes but the logic looks correct, check if you forgot to rebuild the assets or copy them to the container.

### Preventing Flaky Behat Tests

Flaky tests are tests that intermittently pass and fail without code changes. Follow these guidelines to prevent race conditions and timing issues:

#### 1. Always Wait for DOM Updates After AJAX/Async Operations

**Bad (flaky):**

```gherkin
When I click ".swal2-confirm"
And I wait for the element ".loading-spinner-backdrop" to appear and if so to disappear again
Then the ".visibility-text" element should contain "private"
```

**Good (robust):**

```gherkin
When I click ".swal2-confirm"
And I wait for the element ".loading-spinner-backdrop" to appear and if so to disappear again
And I wait for AJAX to finish
Then I wait for the element ".visibility-text" to contain "private"
And the ".visibility-text" element should contain "private"
```

**Why:** After a spinner disappears, the DOM might still be updating. Add explicit waits for the final state.

#### 2. Layer Your Wait Steps

Use multiple complementary wait strategies:

1. **Spinner wait:** `I wait for the element ".loading-spinner" to appear and if so to disappear again`
2. **AJAX wait:** `I wait for AJAX to finish`
3. **Content wait:** `I wait for the element ".result" to contain "expected text"`
4. **Final assertion:** `the ".result" element should contain "expected text"`

#### 3. Wait for Content Changes, Not Just Existence

**Bad:**

```gherkin
When I click button
Then the ".status" element should contain "updated"
```

**Good:**

```gherkin
When I click button
And I wait for AJAX to finish
Then I wait for the element ".status" to contain "updated"
And the ".status" element should contain "updated"
```

#### 4. Always Include -s <suite> Flag

Running Behat without the suite flag causes hooks to run 20+ times, making tests extremely slow and prone to timeouts.

**Bad:** `bin/behat tests/BehatFeatures/web/profile/profile_edit.feature:268`

**Good:** `bin/behat -s web-profile tests/BehatFeatures/web/profile/profile_edit.feature:268`

#### 5. Use Fixed Delays as a Last Resort

When other wait strategies don't work (e.g., AJAX wait doesn't capture DOM mutations), add a small fixed delay:

```gherkin
And I wait for AJAX to finish
And I wait 1000 milliseconds
Then the ".result" element should contain "expected"
```

**Note:** Only use fixed delays when:

- The DOM update happens after AJAX completes
- There's no reliable element/condition to wait for
- Other wait strategies have proven insufficient

#### 6. Test Scenarios That Often Need Extra Waits

- Project visibility toggles (privacy changes)
- Like/reaction buttons (guest → login → action)
- Form submissions with validation
- Modal dialogs with animations
- Infinite scroll / lazy loading
- Any operation that shows a loading spinner

#### 7. Debugging Flaky Tests

If a test passes locally but fails in CI:

1. Check if there are missing wait steps after async operations
2. Verify all AJAX calls have completed before assertions
3. Look for DOM mutations that happen after spinners disappear
4. Check network timing differences (CI might be slower)
5. Use `http://localhost:8080/index_test.php/` to inspect test state after failure

## Development Workflow Checklist

### After PHP Changes (src/)

Always run (in order):

1. `bin/php-cs-fixer fix <file>` - auto-fix style
2. `bin/phpstan analyse <file>` - static analysis
3. `bin/psalm <file>` - type checking
4. Run relevant unit tests if applicable

### After PHP Test Changes (tests/PhpUnit/)

Always run (in order):

1. `bin/php-cs-fixer fix <file>` - auto-fix style
2. `bin/phpstan analyse <file>` - static analysis
3. `bin/psalm <file>` - type checking
4. `bin/phpunit <file>` - run the tests you changed

### After JS/CSS/Asset Changes

Always run:

- `yarn run fix-js` - ESLint
- `yarn run fix-css` - Stylelint
- `yarn run fix-asset` - Prettier

**Rebuild required**: SCSS/JS changes require rebuilding assets:

- Run `yarn run dev` after changes, OR
- Keep `yarn run watch` running in the background (auto-rebuilds on save)

### When to Run Tests

| Change Type        | Run These                           |
| ------------------ | ----------------------------------- |
| PHP business logic | PHPUnit (`bin/phpunit --filter`)    |
| PHPUnit test files | PHPUnit (`bin/phpunit <test-file>`) |
| API endpoints      | PHPUnit API tests + Behat API suite |
| UI/Templates       | Behat web tests                     |

Note: Use `docker exec app.catroweb` prefix if native commands don't work.

## JavaScript Patterns

### DOMContentLoaded with Deferred Scripts

Webpack Encore loads scripts with `defer`. Per HTML spec, deferred scripts execute before `DOMContentLoaded` fires, so `document.addEventListener('DOMContentLoaded', ...)` works correctly — this is the standard pattern used throughout the codebase. If you encounter edge cases where it doesn't fire, use this fallback:

```javascript
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFunction)
} else {
  initFunction()
}
```

### Event Delegation with Child Elements

When using event delegation, use `.closest()` instead of `.matches()` to handle clicks on child elements:

```javascript
// Bad - won't work if clicking on child elements
if (event.target.matches('.my-button')) { ... }

// Good - works for clicks anywhere inside the button
const button = event.target.closest('.my-button')
if (button) { ... }
```

### Handling Guest User Actions (Redirect to Login Pattern)

When implementing features where guests need to log in before performing an action (e.g., liking a project):

**Pattern:** Store pending action in `sessionStorage`, redirect to login, then execute action after successful login.

```javascript
// Before redirecting guest to login
if (userRole === 'guest') {
  sessionStorage.setItem(
    'pendingAction',
    JSON.stringify({
      projectId: projectId,
      actionType: 'reaction',
      actionData: { type: reactionType, action: 'add' },
    }),
  )
  window.location.href = loginUrl
  return false
}

// After successful login, in page initialization
const pendingAction = sessionStorage.getItem('pendingAction')
if (pendingAction && userRole !== 'guest') {
  try {
    const action = JSON.parse(pendingAction)
    if (action.projectId === projectId) {
      sessionStorage.removeItem('pendingAction')
      // Execute the pending action immediately
      executePendingAction(action)
    }
  } catch (e) {
    console.error('Failed to process pending action', e)
    sessionStorage.removeItem('pendingAction')
  }
}
```

**Important notes:**

- Use `sessionStorage` (not `localStorage`) so the pending action doesn't persist across browser sessions
- Always validate the stored data matches the current context (e.g., same project ID)
- Remove the item immediately after reading to prevent duplicate execution
- Execute the action synchronously during page initialization to ensure it completes before tests check the results
- Browser globals like `sessionStorage` are already defined in `eslint.config.js` via `globals.browser`

## Git Workflow

- Main branch: `develop`
- Feature branches merged via PR
- CI runs on GitHub Actions (Static analysis, Dynamic analysis)

## API

- OpenAPI spec in `src/Api/OpenAPI/`
- Generate API client: `yarn run generate-api`

### OpenAPI Enum Serialization (Important!)

**Never use `$ref` to a separate schema for enums in response models.** The OpenAPI generator creates PHP backed enums that JMS Serializer serializes as objects instead of strings:

```
// BAD - using $ref: '#/components/schemas/ReactionType'
"types": [{"name": "LOVE", "value": "love"}]
// GOOD - using inline enum
"types": ["love"]
```

**Pattern that works:** Use inline enum definitions directly where needed:

```yaml
# GOOD - inline enum (serializes as string)
type: string
enum:
  - 'thumbs_up'
  - 'smile'
  - 'love'
  - 'wow'

# BAD - separate schema reference (serializes as object)
$ref: '#/components/schemas/ReactionType'
```

This applies to:

- Response model properties (arrays of enums)
- Request body properties
- Query parameters

For query parameters, you can still define reusable parameters, just use inline enum in the schema.

### API Architecture

The API follows a facade pattern with these components per feature:

```
src/Api/Services/{Feature}/
├── {Feature}ApiFacade.php           # Orchestrates the feature
├── {Feature}ApiLoader.php           # Data loading/queries
├── {Feature}ApiProcessor.php        # Business logic
├── {Feature}RequestValidator.php    # Input validation
└── {Feature}ResponseManager.php     # Response formatting
```

The main `ProjectsApi.php` delegates to these facades.

### Cursor-Based Pagination Pattern

API list endpoints use cursor-based pagination (not offset). Pattern:

1. **Repository**: Fetch `$limit + 1` results; if count > limit, `has_more = true` and `array_pop()` the extra
2. **Response**: Cursor is `base64_encode((string) $last_item_id)`; decode with `base64_decode($cursor, true)` + validation
3. **Response model**: `{ data: [...], next_cursor: string|null, has_more: boolean }`

See `CommentsApi` and `NotificationsApi` for reference implementations.

### After Running `yarn run generate-api`

Always run Rector and CS fixer on the generated files:

```bash
yarn run generate-api
bin/rector process src/Api/OpenAPI/Server/
bin/php-cs-fixer fix src/Api/OpenAPI/Server/ --using-cache=no
```

Then update any existing PHP code that calls the regenerated interface methods (check tests too — method signatures change).

### Registering New API Handlers (Critical!)

After creating a new `{Feature}Api.php` that implements a generated interface, you **must** register it in `config/services.php`:

```php
use App\Api\AchievementsApi;

$services->set(AchievementsApi::class)
    ->tag('open_api_server.api', ['api' => 'achievements']);
```

The `'api'` tag value must match the OpenAPI tag name (lowercase). Without this, the generated controller's `getApiHandler()` will fail to find the handler and return 500.

### HTTP Status Codes for Validation

- **400 Bad Request**: Query parameter validation failures (enum values in URL)
- **422 Unprocessable Entity**: Request body validation failures (JSON body content)

### Behat JSON Request Body Uses Heredoc (PyStringNode), NOT Tables

The `I have the following JSON request body:` step expects a `PyStringNode` (heredoc with `"""`), NOT a Gherkin `TableNode`. This is a common mistake:

```gherkin
# WRONG - table format (parsed as TableNode, causes step definition mismatch)
And I have the following JSON request body:
  | key      | value |
  | category | spam  |

# CORRECT - heredoc format (parsed as PyStringNode)
And I have the following JSON request body:
  """
  {"category": "spam", "note": "This is spam"}
  """
```

### Security Config: Admin Endpoint Rules Must Come BEFORE Catch-All

In `config/packages/security.php`, the `^/api` catch-all requires `IS_AUTHENTICATED_FULLY`. Admin-only rules like `/api/moderation/` must be placed BEFORE the catch-all or they'll never be evaluated:

```php
// Admin endpoints (MUST be before ^/api catch-all)
['path' => '^/api/moderation/', 'roles' => 'ROLE_ADMIN', 'methods' => ['GET', 'PUT']],
// ... other specific rules ...
// Catch-all (last)
['path' => '^/api', 'roles' => 'IS_AUTHENTICATED_FULLY'],
```

### Behat Fixture: `there are admins:` Step

To create admin users in Behat tests, use the dedicated step (NOT a column in `there are users:`):

```gherkin
And there are admins:
  | name  |
  | Admin |
```

This grants `ROLE_ADMIN` to the named user (creates the user if it doesn't exist).

### SweetAlert2 Dialog IDs in Behat Steps

When testing SweetAlert2 dialogs (used by ReportDialog.js, AppealDialog.js), the HTML structure uses:

- Radio buttons: `#report-cat-{category_value}` (e.g., `#report-cat-spam`)
- Text area: `#report-note`
- Confirm button: `.swal2-confirm`

Old legacy IDs like `#report-copyright`, `#report-inappropriate` no longer exist.

### Polymorphic Content Pattern: content_type + content_id

The moderation system uses a polymorphic pattern instead of Doctrine STI:

- `content_type` is a PHP backed enum (`ContentType::Project`, etc.)
- `content_id` is stored as VARCHAR(255) — UUIDs for projects/users/studios, int-as-string for comments
- Cast comment IDs: `(int) $content_id` when looking up `UserComment`
- This avoids Doctrine STI complexity while supporting all 4 content types in a single table

### Adding New Behat Suites to CI

When adding a new Behat suite, you must update **three places**:

1. `behat.yaml.dist` — define the suite with contexts and paths
2. `.github/workflows/tests.yaml` — add the suite name to the `matrix.testSuite` list (alphabetical order within api/web groups)
3. `.claude/CLAUDE.md` — add to the Common Suites table

The CI matrix runs each suite as a separate parallel job. Missing from the matrix = not tested in CI.

### Behat Fixture: `there are users:` Does NOT Set `created_at`

The `there are users:` step only creates users with id/name/password. To set `created_at` (needed for trust score calculations), use the separate step:

```gherkin
Given there are users:
  | id | name  |
  | 1  | User1 |
And the users are created at:
  | name  | created_at          |
  | User1 | 2024-01-01 12:00:00 |
```

Without `the users are created at:`, users get `created_at = now()` which gives trust score ~0.0 (too low to report).

### Behat Fixture: Studio IDs Are UUIDs

Studios use UUID primary keys via `MyUuidGenerator`. To reference studios by predictable ID in Behat tests, specify `id` in the fixture:

```gherkin
And there are studios:
  | id | name    | description |
  | 1  | studio1 | test studio |
```

`MyUuidGenerator::setNextValue()` maps short IDs like `1` to deterministic UUIDs.

### Moderation System Architecture

The trust-weighted moderation system lives in `src/Moderation/` with these key services:

| Service                    | Responsibility                                                                   |
| -------------------------- | -------------------------------------------------------------------------------- |
| `TrustScoreCalculator`     | Compute user trust score (account age + activity + report accuracy + role bonus) |
| `ReportProcessor`          | Create reports, validate, check auto-hide threshold, batch-resolve related       |
| `AppealProcessor`          | Create appeals, validate ownership + hidden state                                |
| `AutoModerationService`    | Hide content + create audit trail + send notifications                           |
| `ContentVisibilityManager` | Polymorphic hide/show/check + cascade hide/show for banned users                 |

Key design decisions:

- **Polymorphic pattern**: `content_type` (enum) + `content_id` (string) — NOT Doctrine STI
- **Auto-hide threshold**: cumulative trust score >= 10.0 triggers auto-hide
- **Min trust to report**: 0.5 (prevents brand-new accounts from reporting)
- **ContentVisibilityManager does NOT flush** — callers own the transaction boundary
- **Studio appeals**: `getContentOwnerId()` returns null for studios (any authenticated user can appeal)
- **Batch resolution**: when admin accepts/rejects one report, ALL pending reports for same content are auto-resolved
- **Cascade hide/show**: banning a user (hiding profile) also hides all their projects and comments via bulk DQL UPDATE
- **Trust score accuracy**: +1.5 per accepted report, -2.0 per rejected, clamped [-5.0, +5.0] — asymmetric to penalize bad reporters
- **No whitelisting on rejection**: rejected reports do NOT whitelist content — it can be reported again, but bad reporters lose trust score
- **Whitelist = report immunity**: `User.approved` / `Program.approved` means immune from community reports (button hidden + API rejects)
- **auto_hidden vs visible**: `auto_hidden` is community-moderation-driven, `visible` is admin-only control — they are independent

### Rate Limiting

**Config**: `config/packages/rate_limiter.php` — uses Symfony's `sliding_window` policy.

**Trait**: `src/Api/RateLimitTrait.php` — provides `checkUserRateLimit(User, Factory)` and `checkIpRateLimit(string $ip, Factory)`.

| Limiter                | Limit | Interval   | Key  | Used In           |
| ---------------------- | ----- | ---------- | ---- | ----------------- |
| `report_burst`         | 3     | 15 minutes | User | ReportProcessor   |
| `report_daily`         | 10    | 24 hours   | User | ReportProcessor   |
| `comment_burst`        | 5     | 5 minutes  | User | CommentsApi       |
| `comment_daily`        | 50    | 24 hours   | User | CommentsApi       |
| `reaction_burst`       | 30    | 5 minutes  | User | ProjectsApi       |
| `follow_burst`         | 20    | 5 minutes  | User | FollowersApi      |
| `appeal_daily`         | 3     | 24 hours   | User | ModerationApi     |
| `upload_daily`         | 10    | 24 hours   | User | ProjectsApi       |
| `auth_burst`           | 10    | 15 minutes | IP   | AuthenticationApi |
| `registration_burst`   | 3     | 1 hour     | IP   | UserApi           |
| `password_reset_burst` | 5     | 1 hour     | IP   | UserApi           |
| `search_burst`         | 30    | 1 minute   | IP   | SearchApi         |
| `studio_create_daily`  | 5     | 24 hours   | User | StudioApi         |

**Wiring pattern**: Symfony autowires `RateLimiterFactory` by camelCase convention — config key `report_burst` → constructor param `$reportBurstLimiter`. Add `use RateLimitTrait;` to the API class, inject the factory via constructor, then call `checkUserRateLimit()` or `checkIpRateLimit()` and return 429 if rejected.

**User-based vs IP-based**: Use IP-based (`checkIpRateLimit`) for unauthenticated endpoints (login, registration). Use user-based (`checkUserRateLimit`) for authenticated endpoints. Admins are typically exempt from user-based limits (check `isGranted('ROLE_ADMIN')` before consuming).

**Response**: Return `Response::HTTP_TOO_MANY_REQUESTS` (429) with `null` body when rate limit is exceeded.

**Testing**: `RateLimiterFactory` is `final` in Symfony 7.4 — it cannot be stubbed/mocked with PHPUnit. In unit tests, create a real instance with `no_limit` policy:

```php
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

new RateLimiterFactory(['id' => 'test', 'policy' => 'no_limit'], new InMemoryStorage())
```

### AccountStateEventListener (Write API Guard)

**File**: `src/Security/AccountStateEventListener.php`

Symfony `kernel.request` listener (priority -10) that blocks write API endpoints for:

1. **Unverified users** (`!isVerified()`) → 403 `"Email verification required."`
2. **Suspended users** (`getProfileHidden()`) → 403 `"Your account has been suspended."`

Exempt paths: `/api/authentication*`, `/api/user` (POST), `/api/user/reset-password*`, `/api/.+/appeal$`

Only applies to POST/PUT/DELETE/PATCH on `/api/` paths. GET/HEAD/OPTIONS always pass through.

### JS 403 Error Handling Pattern

All JS files that make write API calls must distinguish 403 sub-types by parsing the JSON body:

- `"Email verification required."` → show "verify email" message
- `"Your account has been suspended."` → show suspension message
- Other 403s → generic error

Pattern used in `ReportDialog.js`, `Project.js`, `ProjectComments.js`, `FollowerOverview.js`.
Data attributes: `data-trans-account-suspended`, `data-trans-report-suspended` on Twig templates.

### Template Whitelist/Hidden Conditions

Report buttons should be hidden when content is whitelisted OR already auto-hidden:

- **Project**: `not my_project and not is_whitelisted and not project.autoHidden` — use entity getter directly, no extra template var needed
- **User**: `not profile.approved` — User entity getter
- **Comment**: `not comment.user_approved|default(false)` — requires adding `cu.approved as user_approved` to the comment query SELECT

## PHPUnit Testing Best Practices

**See [CLAUDE_PHPUNIT_MOCKING.md](CLAUDE_PHPUNIT_MOCKING.md) for comprehensive guidelines on:**

- When to use `createStub()` vs `createMock()`
- Common patterns and anti-patterns
- Refactoring checklist for mock warnings
- Real examples from the codebase

**Quick Rule:** Use `createStub()` when you need return values. Use `createMock()` only when you verify behavior with `expects()`.
