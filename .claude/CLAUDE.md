# Catroweb - Project Guide for Claude

## Important: Keep This Document Updated

**Always update CLAUDE.md with new learnings** discovered during development sessions. This includes:

- Gotchas and non-obvious behaviors
- Patterns that work vs patterns that don't
- Correct tool/command usage discovered through trial and error
- API quirks and workarounds

This ensures future sessions benefit from past discoveries. Also run `npm run fix-asset` after updating.

## Overview

Catroweb is the share/communication platform for the Catrobat community. It's a Symfony-based PHP application with a modern frontend using Webpack Encore.

## Tech Stack

- **Backend**: PHP 8.5, Symfony 7.4
- **Frontend**: JavaScript (ES6+), SCSS, Webpack Encore
- **Database**: MariaDB 10.11
- **Search**: Elasticsearch 7.17
- **CSS Framework**: Bootstrap 5, Material Design Components
- **Package Managers**: Composer (PHP), npm (JS)

## Command Execution Environment

- **npm commands**: Run **locally** (npm, eslint, stylelint, prettier, webpack)
- **PHP commands**: Try **native first** (`bin/phpstan`, `bin/psalm`, etc.), fall back to **Docker** if not available

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
npm install

# Development build
npm run dev

# Production build
npm run build

# Watch mode (auto-rebuild on changes)
npm run watch

# Development server with hot reload
npm run dev-server
```

### Testing & Linting

```bash
# Run all tests
npm test

# Individual test commands
npm run test-js      # ESLint
npm run test-css     # Stylelint for SCSS
npm run test-asset   # Prettier
npm run test-php     # PHP CS Fixer
npm run test-twig    # Twig CS Fixer

# Fix commands
npm run fix          # Fix all
npm run fix-js       # Fix JS
npm run fix-css      # Fix SCSS
npm run fix-asset    # Fix assets
npm run fix-php      # Fix PHP
npm run fix-twig     # Fix Twig
```

### Symfony Console

```bash
# Reset database with sample data (limit 20 projects)
npm run reset
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

**Note**: `node_modules/` and `vendor/` are NOT shared - they're built inside the container. After npm/composer changes, rebuild the container.

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
└── package.json           # npm dependencies
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

### After changing package.json

```bash
# Local development
rm -rf node_modules package-lock.json
npm install

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

**CRITICAL:** After making JavaScript changes in `assets/`, you MUST run `npm run dev` to compile the changes before running Behat tests. The Docker container serves the compiled assets from `public/build/`, not the source files.

```bash
# Always do this after JS changes:
npm run dev
docker exec app.catroweb bin/behat -f pretty -s web-reactions "tests/BehatFeatures/..."
```

If Behat tests fail after JavaScript changes but the logic looks correct, check if you forgot to rebuild the assets.

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

- `npm run fix-js` - ESLint
- `npm run fix-css` - Stylelint
- `npm run fix-asset` - Prettier

**Rebuild required**: SCSS/JS changes require rebuilding assets:

- Run `npm run dev` after changes, OR
- Keep `npm run watch` running in the background (auto-rebuilds on save)

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

Scripts loaded with `defer` (Webpack Encore default) run AFTER DOMContentLoaded fires. Use this pattern:

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
- Generate API client: `npm run generate-api`

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

### HTTP Status Codes for Validation

- **400 Bad Request**: Query parameter validation failures (enum values in URL)
- **422 Unprocessable Entity**: Request body validation failures (JSON body content)

## PHPUnit Testing Best Practices

**See [CLAUDE_PHPUNIT_MOCKING.md](CLAUDE_PHPUNIT_MOCKING.md) for comprehensive guidelines on:**

- When to use `createStub()` vs `createMock()`
- Common patterns and anti-patterns
- Refactoring checklist for mock warnings
- Real examples from the codebase

**Quick Rule:** Use `createStub()` when you need return values. Use `createMock()` only when you verify behavior with `expects()`.
