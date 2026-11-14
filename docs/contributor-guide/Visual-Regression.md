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

The job is **advisory**: differences show up as warnings and a table in the job summary, never as a
failed check. It is not part of the `CI gate`.

## A warning on your PR

Download the `visual-snapshots` artifact from the run. It holds the new screenshots and, in `diff/`,
the changed pixels marked red.

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
