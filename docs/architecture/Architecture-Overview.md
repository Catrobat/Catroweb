# Architecture Overview

Symfony (PHP) backend, Twig templates, Vite-built JS/CSS in `assets/`, MariaDB, Elasticsearch. Tests: PHPUnit (`tests/PhpUnit`) and Behat (`tests/BehatFeatures`).

## `src/` Module Map

- `Api`: OpenAPI-generated server (`src/Api/OpenAPI/`) plus per-feature services in `src/Api/Services/{Feature}/`
- `Application`: controllers, forms, framework-level glue
- `DB`: Doctrine entities and repositories
- `Project`: projects, remixes, Scratch integration, search
- `Studio`: studios
- `User`: profiles, achievements, notifications
- `Moderation`: reports, appeals, trust score, auto-hiding
- `Security`: authentication, JWT, OAuth
- `Admin`: admin panel (Sonata)
- `System`: console commands, cron jobs, mail, test helpers
- `Storage`, `Translation`, `Twig`, `Utils`: supporting infrastructure

CI is defined in `.github/workflows/`; deployment in `deploy.php` (see [How to Deploy](../operations/How-To-Deploy.md)).
