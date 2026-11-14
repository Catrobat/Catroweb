# Visual Regression Snapshots

The **Visual snapshots** CI job catches design breaks that functional tests miss, such as CSS that
only the production build strips (PurgeCSS) or a broken dark mode.

## How it works

1. CI builds the **production** frontend (`npx vite build`) and swaps it into the test container.
2. The `web-visual` Behat suite (`tests/BehatFeatures/web/visual/`) opens key pages with fixed
   fixtures, in light and dark mode, at mobile and desktop width, and saves a screenshot per step
   `Then I take a visual snapshot "<name>"`. CSS animations are frozen first, so runs are repeatable.
3. `tests/visual/compare.sh` compares every screenshot with `tests/visual/baselines/<name>.png`
   (ImageMagick, 3 % colour fuzz, warns when more than 0.1 % of pixels differ).

4. On PRs from this repository, `tests/visual/publish.sh` pushes a **before | after | diff** image per
   changed snapshot to the `visual-snapshots` branch (`pr-<n>/<run>/`) and posts one PR comment
   showing them. Later runs edit that comment instead of adding new ones. Fork PRs get a read-only
   token, so they only get the job summary and artifact.

The job is **advisory**: differences show up as warnings and a table in the job summary, never as a
failed check. It is not part of the `CI gate`.

## A warning on your PR

The PR comment shows each changed snapshot as **before | after | diff** (changed pixels in red). The
`visual-snapshots` artifact holds the same images (`strips/`) plus the raw screenshots.

- **Intended change:** accept the new looks, review the images, commit and push:

  ```bash
  tests/visual/accept.sh <pr-number>
  ```

- **Noise or an unrelated flake:** ignore it. If a snapshot is noisy every time, tune `VISUAL_FUZZ` /
  `VISUAL_MAX_RATIO` in the workflow or make the page content deterministic in the scenario.

## Adding a page

Add a scenario to `tests/BehatFeatures/web/visual/pages.feature` ending in
`Then I take a visual snapshot "<page>-<scheme>-<size>"`. Its first CI run reports it as _new_;
accept it to create the baseline.

Baselines must come from CI (Linux Chrome container), never from a local machine: font rendering
differs between operating systems.

The `visual-snapshots` branch only holds comparison images, never code. Its history grows with every
changed run; delete it whenever it gets big, the next run recreates it.
