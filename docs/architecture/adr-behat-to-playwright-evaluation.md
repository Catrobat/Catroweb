# ADR: Behat → Playwright + Cucumber for Browser Tests (Evaluation)

**Date**: 2026-10-19
**Status**: Decided: not now
**Issue**: [#6495](https://github.com/Catrobat/Catroweb/issues/6495)

## Context

A proof of concept ([#6374](https://github.com/Catrobat/Catroweb/pull/6374), closed) ported `web-general` to Playwright and ran ~4× faster locally. Measured on `main` (15 nightlies, 2026-10): Behat costs ~173 job-minutes per run, of which web suites are ~86. Wall clock is bound by the slowest matrix jobs, three of five being API suites. Every nightly needed a rerun, but the worst offenders were Elasticsearch index races (`api-search` 15/15), not the browser driver.

## Decision

Do not migrate. Fix flake sources in Behat first (search-index waits now do a real index refresh). Never migrate the API suites: they run in-process via `KernelBrowser`, are not browser tests, and would get slower.

## Why

- A full web migration saves at most ~64 job-minutes per run and barely moves wall clock.
- Web fixtures are written in-process through Doctrine; Playwright needs an out-of-process seeding path for all 137 web features. A seeder class per suite (the PoC approach) does not scale.
- Behat, chrome-mink-driver and the Symfony extension are maintained.

## When to revisit

If browser-test DX is still the goal: pilot one browser-heavy suite (`web-search web-top-bar` or `web-general`) with `playwright-bdd`, reusing the `.feature` files, seeding through a test-only fixture endpoint (`APP_ENV=test` only, 404 in prod) or a `bin/console` seeder. Continue only if it is ≥ 3× faster on the CI runner, needs zero reruns over two weeks of nightlies, and porting a suite takes ≤ 2 days once seeding exists.
