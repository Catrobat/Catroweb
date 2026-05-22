---
name: bundle-analysis
description: Generate a one-off frontend bundle size report (treemap + per-chunk gzip sizes). Use when asked about "bundle analysis", "bundle size", "what's making the bundle big", "/bundle-analyse", or anything similar.
---

# Bundle analysis (Vite)

Project-specific: we **cannot** keep `rollup-plugin-visualizer` in `package.json` long-term because it drags in `is-inside-container` (flagged `ossf-unmaintained` by the repo's SafeDep CI gate). The skill installs it, runs the report, reverts.

## Steps

1. **Verify clean state first** — `git status --short` must be empty. If not, ask before continuing. The skill mutates `package.json`, `yarn.lock`, and `vite.config.mjs` temporarily and a dirty tree would smuggle changes through.

2. **Temp-install the analyzer:**

   ```bash
   yarn add -D rollup-plugin-visualizer
   ```

3. **Wire it into `vite.config.mjs`:**
   - Add at the top: `import { visualizer } from 'rollup-plugin-visualizer'`
   - Append to the `plugins: [...]` array (after `symfony(...)`):

     ```js
     visualizer({
       filename: 'bundle-report.html',
       gzipSize: true,
       brotliSize: true,
       template: 'treemap',
     }),
     ```

4. **Build:** `rm -rf public/build && yarn run build`. Treemap lands at `bundle-report.html` (gitignored already via `*.html`-style root patterns — confirm with `git status` before stepping further).

5. **Extract findings without opening a browser** — combine these in your report:
   - `du -sh public/build/{js,css,fonts,images}` — totals per category.
   - `du -sh public/build/js/chunks/*.js public/build/js/*.js | sort -hr | head -15` — biggest raw chunks.
   - For headline gzip numbers on the top 5–7 chunks:

     ```bash
     for f in $(du -sh public/build/js/chunks/*.js public/build/js/*.js | sort -hr | head -7 | awk '{print $2}'); do
       printf "%6.1f KB raw  %6.1f KB gz  %s\n" \
         "$(echo "scale=1; $(wc -c < $f)/1024" | bc)" \
         "$(echo "scale=1; $(gzip -9 -c $f | wc -c)/1024" | bc)" \
         "$(basename $f)"
     done
     ```

6. **Revert. All of it.** Three commands:

   ```bash
   yarn remove rollup-plugin-visualizer
   rm -f bundle-report.html
   # Then manually undo the two edits to vite.config.mjs (import + plugin entry).
   ```

   Verify `git status --short` is empty before reporting back.

7. **Report** to the user: top chunks (raw + gzip), totals, and 1–3 candidate optimizations (lazy-load opportunities, oversized vendor libs, font subsets). Keep it ~20 lines.

## What this skill does NOT do

- License / vulnerability / outdated-package checks → use `yarn npm audit`, `composer audit`, SafeDep CI.
- Unused deps → `npx -y depcheck` is one command.
- CI-time analysis → bundle inspection is a manual one-off, not a gate.
