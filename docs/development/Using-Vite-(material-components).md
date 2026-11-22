# Adding a JS/CSS entry (Vite)

Material components come from `@material/*` packages (`yarn add @material/<package>`, then rebuild the Docker image).

1. Put the module next to its feature in `assets/` (e.g. `assets/Project/MyFeature.js`).
2. Register it in `jsEntries` (or `cssEntries`) in `vite/entries.mjs`.
3. In the Twig template: `{{ vite_entry_script_tags('my_feature') }}` / `{{ vite_entry_link_tags('my_feature') }}`.
4. Build with `yarn dev`, or run `yarn run dev-server` for HMR. New page behavior should be a Stimulus controller in `assets/controllers/` instead of a new entry where possible.

Stimulus controllers are not registered globally: the page entry that renders a controller's markup registers it (`app.register('<identifier>', Controller)` with `app` from `assets/bootstrap.js`, see `assets/Studio/StudiosPage.js`), so a controller costs nothing on other pages. Keep heavy libraries out of `base_layout` for the same reason, and import them dynamically (`await import(...)`) when first paint does not need them: everything an entry imports statically downloads alongside the page's LCP image.
