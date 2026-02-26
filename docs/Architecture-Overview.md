# Architecture Overview

This page gives a practical map of the Catroweb codebase for contributors.

## High-Level Stack

- Backend: PHP + Symfony
- Frontend assets: Webpack Encore + Sass + JavaScript
- Database: MariaDB
- Search: Elasticsearch
- Test stack: PHPUnit + Behat (+ headless Chrome for UI flows)

## Main Repository Areas

- `src/`: application source code
- `config/`: Symfony configuration
- `templates/`: Twig templates
- `assets/`: frontend source files (SCSS/JS/images)
- `public/`: web root (compiled assets are generated under `public/build/`)
- `tests/`: PHPUnit and Behat test suites
- `docker/`: Docker dev/test environment

## `src/` Module Map

- `src/Application`: controllers, forms, framework-level application logic
- `src/Api`: API endpoints, OpenAPI generation, API services
- `src/DB`: entities, repositories, and Doctrine integration
- `src/Project`: domain logic around projects, remixing, scratch integration, events
- `src/User`: user profile, achievements, notifications, password reset
- `src/System`: console commands, system controllers, logging, mail, test helpers
- `src/Admin`: admin panel modules and feature areas
- `src/Security`: authentication and OAuth-related code
- `src/Storage`: storage-related integrations
- `src/Translation`: translation/localization logic
- `src/Utils`: shared utility helpers

## Runtime Environments

- Dev web app: usually `http://localhost:8080` (Docker)
- Test environment: used by CI and local test runs
- Production-like deployments: handled via `deploy.php` / Deployer docs

## Typical Change Paths

### API change

1. Update endpoint/service code under `src/Api`.
2. Update or regenerate OpenAPI definitions if needed.
3. Add/update API tests in Behat and/or PHPUnit.

### Web feature change

1. Update backend controller/service/entity code.
2. Update Twig templates in `templates/`.
3. Update frontend assets in `assets/` and run asset checks/build.
4. Add/update tests (`tests/PhpUnit`, `tests/BehatFeatures`).

### Database model change

1. Modify entity classes in `src/DB/Entity`.
2. Generate and run doctrine migrations.
3. Verify `catrobat:reset --hard` and test suite behavior.

## Quality Gates (CI)

- Static analysis: ESLint, Stylelint, Prettier, PHP-CS-Fixer, PhpStan, Psalm, Twig/YAML/Container lints
- Dynamic analysis: PHPUnit + Behat suites in Docker-based jobs

See `.github/workflows/` for the latest exact CI implementation.
