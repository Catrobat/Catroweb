# ADR: Behat → Playwright + Cucumber for Browser Tests (Evaluation)

**Date**: 2026-10-19
**Status**: Proposed
**Authors**: Daniel, Claude
**Issue**: [#6495](https://github.com/Catrobat/Catroweb/issues/6495)

## Context

The issue proposes moving the browser tests from Behat + Mink + `dmore/chrome-mink-driver` to Playwright
with Cucumber, keeping the `.feature` files. A proof of concept ported `web-general`
([#6374](https://github.com/Catrobat/Catroweb/pull/6374), closed) and measured it ~4.2× faster locally
(30 Playwright tests in 32.3 s vs 29 Behat scenarios in 2 min 16 s).

## Current state (measured on `main`, 2026-10-19)

### Suite size

| Metric                                | Value                                                                                                                                | Source                                            |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------- |
| Feature files                         | 244 (107 `api/`, 137 `web/`)                                                                                                         | `find tests/BehatFeatures -name '*.feature'`      |
| Scenarios                             | 1,428 (760 api, 668 web), 24 of them outlines                                                                                        | `grep -E '^\s*Scenario'`                          |
| Step lines in features                | 13,041 (6,745 api)                                                                                                                   | `grep -E '^\s*(Given\|When\|Then\|And\|But) '`    |
| Suites                                | 43, in 21 CI matrix jobs                                                                                                             | `behat.yaml.dist`, `.github/workflows/tests.yaml` |
| Step definitions                      | 398: ApiContext 150, DataFixturesContext 122, CatrowebBrowserContext 89, BrowserContext 35, SanitizerContext 2 (plus Mink built-ins) | `src/System/Testing/Behat/Context/`               |
| Context code                          | 8,326 lines (incl. `ContextTrait.php`)                                                                                               | `wc -l`                                           |
| Fixed sleeps / AJAX waits in features | 125 × `I wait N milliseconds`, 482 × `I wait for AJAX to finish`                                                                     | `grep`                                            |

Two facts shape the scope:

- **The API suites are not browser tests.** `ApiContext` drives Symfony's `KernelBrowser` in-process, with no
  Chrome involved. They are 760 of the 1,428 scenarios. Playwright would make them slower and would need a
  real HTTP server and out-of-process fixtures. They should stay on Behat whatever is decided here.
- **Web fixtures are written in-process.** `DataFixturesContext` writes through Doctrine in the Behat process,
  and Chrome then loads `http://localhost/index_test.php/` from Apache in the same container (`behat.yaml.dist`).
  A Playwright runner runs outside PHP, so all of those fixture steps need a new seeding path. That is the
  architectural cost the issue names. #6374 solved it with a hand-written seeder per suite
  (`WebGeneralFixtureSeeder.php`), which does not scale to 137 web features.

### CI cost (last 15 nightly `Dynamic analysis` runs on `main`, 2026-10-05 → 2026-10-19)

Measured from `gh run list --workflow tests.yaml --branch main` and the jobs API:

- Wall clock per run: **15.7–26.7 min** (median ≈ 18 min).
- Behat job time per run: median **172.8 job-minutes** (min 166.6, max 180.1) out of 178.1 for the whole
  workflow.
- The `bin/behat` steps themselves: **≈ 86 job-min for web suites and ≈ 47 for API suites** per run. The rest
  is per-job overhead: image pull (median 0.6 min), `compose up` (0.4), cache warmup (0.4), and reruns.
- Slowest jobs (median min): `web-profile-2` 15.1, `api-projects-write` 11.8, the
  `api-achievements…api-studio` group 11.5, `web-studio` 11.2, `api-projects-get` 10.6.

If the PoC's 4× speed-up held for every web suite, a full migration would save at most ≈ 64 of the 172.8
Behat job-minutes per run. Wall clock would barely move: it is bounded by the slowest matrix jobs, and two of
the five slowest are API suites.

### Flakiness signals

- **Every one of the 15 nightly runs needed at least one Behat rerun.** 29 of 315 suite jobs needed the 1st
  rerun, 3 the 2nd, 3 the 3rd (`Rerun Behat Tests (n)` steps in `tests.yaml`, which also have fallbacks for
  missing/pending steps and `StreamReadException`).
- By job: `api-search api-translation` **15/15** runs, `web-search web-top-bar` 6, `web-translation` 5,
  `web-general web-notifications` 2, `web-authentication web-media-library` 1.
- The 15/15 offender is an **API** suite. `api/search/search.feature:100` returns HTTP 500 on the first run
  and passes on rerun, so it is search-index timing (Elasticsearch), not the browser driver.
- Both red nightlies in the window (2026-10-09, 2026-10-19) were `web/search/search.feature:38` failing all
  4 attempts with "project 1 was not found". Its background waits a fixed `500 milliseconds` instead of
  `I wait for the search index to be updated`. That is a search-index race again, which Playwright's auto-wait
  would not fix.
- Driver-level breakage exists but was ours: chrome-mink-driver 2.11 stopped hiding a redundant
  `Session::start()` in `BrowserContext::setup()`, so tabs leaked until Chrome crashed. The fix was a one-line
  guard ([#7363](https://github.com/Catrobat/Catroweb/pull/7363), merged 2026-10-19).
- The tooling is maintained: `behat/behat` 3.34.0 (2026-09-27), `dmore/chrome-mink-driver` 2.11.1
  (2026-10-07), `friends-of-behat/symfony-extension` 2.7.0 (2026-07-16) (`composer.lock`). The stale piece is
  `friends-of-behat/mink-extension` v2.7.5 (2024-01).

## Options

| Option                                                                                                        | Cost                                                                                                                                                            | Gains                                                                            | Risks                                                                                                                        |
| ------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| **A. Stay on Behat, fix the flake sources**                                                                   | Days: replace fixed sleeps in search backgrounds with the index wait, find the `api-search` 500, then work down the 125 fixed sleeps                            | Removes the measured reruns and the red nightlies; no new stack                  | Keeps Mink's weaker waiting model and debugging (no trace viewer)                                                            |
| **B. Playwright + Cucumber (`playwright-bdd` or `@cucumber/cucumber`) for web suites, keep `.feature` files** | Re-implement 124 browser steps plus every fixture step the 137 web features use, behind a new out-of-process seeding API; run both stacks in CI during the move | Faster local runs, auto-waiting locators, trace viewer, parallel workers per job | Two test stacks for months; fixture parity bugs; Node and PHP fixtures drifting apart; the issue's own estimate is 6–8 weeks |
| **C. Plain Playwright specs (no Gherkin), as in #6374**                                                       | Rewrite 668 web scenarios as code                                                                                                                               | Most idiomatic Playwright, fastest                                               | Loses the readable feature files contributors know; biggest rewrite                                                          |
| **D. Hybrid: API on Behat forever, Playwright only for new or very flaky web suites**                         | One seeding path plus the steps one suite needs                                                                                                                 | Lets us measure B on real suites without committing to it                        | Two stacks indefinitely if it stalls halfway                                                                                 |

### Fixture seeding (needed for B, C, D)

Fixture seeding is the decision that drives the cost. Options:

1. **Test-only HTTP endpoint** (`APP_ENV=test` only, e.g. `POST /index_test.php/_test/fixtures`). It takes
   the same table rows the Gherkin steps take and calls the existing `UserDataFixtures` / `ProjectDataFixtures`
   / `DataFixturesContext` logic. This reuses the PHP fixture code, but it is an endpoint that must never ship
   enabled. Guard it with a firewall rule and a kernel-env check, and add a test that asserts it returns 404
   in `prod`.
2. **CLI seeder** (`bin/console catro:test:seed <json>`), called from Playwright through `docker exec`. No
   HTTP surface, but slower per scenario (a PHP boot per call).

Both keep the fixture code in PHP. The #6374 path, a seeder class per suite, should not be repeated.

## Recommendation

**Do not start a full migration now. Do A first, then decide on a D pilot with data.**

1. **A, now (small):** fix the two measured search-index races (`web/search/search.feature`,
   `api/search/search.feature:100`). They cause every nightly rerun and both red runs. Neither is a Behat/Mink
   problem, so neither would disappear in Playwright.
2. **D pilot, only if DX is still the goal after A:** port one browser-heavy suite (`web-search web-top-bar`
   or `web-general`) with `playwright-bdd`, reusing its `.feature` files unchanged. Seed fixtures through the
   test-only endpoint (option 1). Run it next to Behat in CI for two weeks of nightlies.
3. **Go/no-go for more suites:** continue only if the pilot is ≥ 3× faster on the CI runner (not only
   locally), needs zero reruns over those two weeks, and porting a suite takes ≤ 2 days once the seeding
   endpoint exists. Otherwise stop and keep Behat.
4. **Never migrate the API suites.** They are in-process, fast, and are not browser tests.

The 6–8 week estimate in the issue covers browser-test DX and at most ≈ 64 job-minutes per nightly run. It
fixes none of the flakiness measured above, so it is not worth it before the cheap fixes land.

## Migration plan sketch (if the pilot passes)

1. Add `@playwright/test` + `playwright-bdd` as dev dependencies, plus a `playwright.config` that points at the
   test container (`http://app.catroweb/index_test.php/`).
2. Implement the test-only fixture endpoint, plus a "reset DB" call used in `Before` hooks
   (`RefreshEnvironmentContext` equivalent).
3. Port the steps one suite needs. Keep step text identical so the `.feature` file does not change.
4. CI: one extra matrix entry per ported suite. The Behat suite stays until its Playwright twin has 2 green weeks.
5. Remove the Behat suite from `behat.yaml.dist` and the matrix. Update the suite table in `.claude/CLAUDE.md`
   and `docs/contributor-guide/Behat-Test-Guidelines.md`.
6. Repeat per suite, most-rerun first (`web-search web-top-bar`, `web-translation`).
