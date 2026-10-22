# How to Test

Setup: [Developer Quickstart](../setup/Developer-Quickstart.md). With Docker, prefix commands with `docker exec -it app.catroweb`. CI runs the same checks (`.github/workflows/`).

## PHPUnit

Config `phpunit.xml.dist`, tests in `tests/PhpUnit/`.

```bash
bin/phpunit tests
bin/phpunit tests --filter <method-name> <file-path>
bin/phpunit --coverage-html tests/TestReports/CoverageReports/PhpUnit \
  --coverage-clover=tests/TestReports/CoverageReports/PhpUnit/coverage.xml
```

## Behat

Config `behat.yaml.dist`, features in `tests/BehatFeatures/`, contexts in `src/System/Testing/Behat/Context/`. Always pass a suite (`-s`); without it hooks run for every suite.

```bash
bin/behat -s web-admin
bin/behat -s web-admin tests/BehatFeatures/web/admin/<file-name>.feature:<line-number>
bin/behat -s web-admin --rerun      # only previously failed scenarios
```

Failure artifacts: `tests/TestReports/TestScreenshots/`, `tests/TestReports/Behat/`. After JS changes in Docker, rebuild and copy `public/build/` into the container and clear the test cache (`bin/console cache:clear --env=test`).

## Static analysis

```bash
yarn test     # Biome (JS/JSON/CSS), Prettier (MD/YAML), PHP-CS-Fixer, Twig CS Fixer
yarn fix
bin/phpstan analyse
bin/psalm
bin/console lint:twig templates/
bin/console lint:yaml translations/ config/ .github/ docker/ behat.yaml.dist
bin/console lint:container
```
