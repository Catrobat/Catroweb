# How to Test

This page covers practical local testing for Catroweb.

## Test Types

- Dynamic tests: PHPUnit + Behat
- Static analysis: ESLint, Stylelint, Prettier, PHP-CS-Fixer, PhpStan, Psalm, and Symfony linters

For CI implementation details, see [GitHub Actions FAQ (Workflow Automation)](<../references/GitHub-Actions-FAQ-(Workflow-Automation).md>) and `.github/workflows/`.

## Prerequisites

Use one of:

- Docker setup: [Docker](../setup/Docker.md)
- Native setup: [Setup guide Ubuntu](../setup/Setup-guide-Ubuntu.md) or [Setup guide macOS](<../setup/Setup-guide-macOS-(native,-no-Docker).md>)

If you use Docker, run tests inside `app.catroweb`.

## Dynamic Tests

### PHPUnit

Where files live:

- Configuration: `phpunit.xml.dist`
- Tests: `tests/PhpUnit/`

Run all:

```bash
bin/phpunit tests
```

Run a single test:

```bash
bin/phpunit tests --filter <method-name> <file-path>
```

Generate coverage:

```bash
bin/phpunit --coverage-html tests/TestReports/CoverageReports/PhpUnit \
  --coverage-clover=tests/TestReports/CoverageReports/PhpUnit/coverage.xml
```

### Behat

Where files live:

- Configuration: `behat.yaml.dist`
- Feature files: `tests/BehatFeatures/`
- Context logic: `src/System/Behat/Context/`

Run all suites:

```bash
bin/behat
```

Run one suite:

```bash
bin/behat -s web-admin
```

Run one scenario:

```bash
bin/behat -s web-admin tests/BehatFeatures/web/admin/<file-name>.feature:<line-number>
```

Rerun failed scenarios:

```bash
bin/behat --rerun
```

Useful failure artifacts:

- Screenshots: `tests/TestReports/TestScreenshots/`
- Behat logs: `tests/TestReports/Behat/`

### Browser Comparison Suites

Experimental browser comparisons for the `web/general` suite:

- Configuration: `playwright.config.js`
- Tests: `tests/Playwright/web-general/`
- Shared Gherkin features: `tests/Gherkin/web-general/`
- Cucumber.js + Playwright glue: `tests/CucumberPlaywright/`
- WebdriverIO + Cucumber glue: `tests/WdioCucumber/`
- Reports: `tests/TestReports/Playwright/`, `tests/TestReports/CucumberPlaywright/`, `tests/TestReports/WdioCucumber/`

Run the plain Playwright suite:

```bash
yarn test-e2e:playwright:web-general
```

Run the Gherkin suite on top of Playwright:

```bash
yarn test-e2e:cucumber-playwright:web-general
```

Run the Gherkin suite on top of WebdriverIO:

```bash
yarn test-e2e:wdio:web-general
```

Install the shared Chromium browser locally if needed:

```bash
yarn test-e2e:playwright:install
```

Notes:

- All three comparison suites assume the Docker test stack is already running on `http://127.0.0.1:8080`.
- They prepare and seed test data by calling test-only PHP scripts inside `app.catroweb`.
- The current migration focuses on browser-facing `web/general` behavior so it can be compared against the existing Behat `web-general` matrix job.

## Static Analysis

### One-by-one

```bash
yarn test-js
yarn test-css
yarn test-asset
yarn test-php
yarn test-twig
```

### All at once

```bash
yarn test
```

### Auto-fix supported checks

```bash
yarn fix
```

## Symfony Linters

```bash
bin/console lint:twig templates/
bin/console lint:yaml translations/ config/ .github/ docker/ behat.yaml.dist
bin/console lint:container
```

## Docker Examples

```bash
docker exec -it app.catroweb bin/phpunit tests
docker exec -it app.catroweb bin/behat -s web-general
docker exec -it app.catroweb php bin/console cache:clear --env=test
```
