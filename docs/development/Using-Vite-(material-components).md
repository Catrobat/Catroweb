# Adding a JS/CSS entry (Vite)

Material components come from `@material/*` packages (`yarn add @material/<package>`, then rebuild the Docker image).

1. Put the module next to its feature in `assets/` (e.g. `assets/Project/MyFeature.js`).
2. Register it in `jsEntries` (or `cssEntries`) in `vite/entries.mjs`.
3. In the Twig template: `{{ vite_entry_script_tags('my_feature') }}` / `{{ vite_entry_link_tags('my_feature') }}`.
4. Build with `yarn dev`, or run `yarn run dev-server` for HMR. New page behavior should be a Stimulus controller in `assets/controllers/` instead of a new entry where possible.
