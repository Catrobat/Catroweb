# How to Test

This page covers practical local testing for Catroweb.

## Test Types

- Dynamic tests: PHPUnit + Behat
- Static analysis: ESLint, Stylelint, Prettier, PHP-CS-Fixer, PhpStan, Psalm, and Symfony linters

For CI implementation details, see [GitHub Actions FAQ (Workflow Automation)](<./GitHub-Actions-FAQ-(Workflow-Automation).md>) and `.github/workflows/`.

## Prerequisites

Use one of:

- Docker setup: [Docker](./Docker.md)
- Native setup: [Setup guide Ubuntu](./Setup-guide-Ubuntu.md) or [Setup guide macOS](<./Setup-guide-macOS-(native,-no-Docker).md>)

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
