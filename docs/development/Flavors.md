# Flavors

Flavors are themed variants of the site (`pocketcode`, `luna`, `pocketgalaxy`, ...). Every web route is prefixed with a theme; `pocketcode` is the default.

Adding a flavor:

1. Add it to `src/DB/Entity/Flavor.php` and to `themeRoutes` in `config/packages/themes.php`.
2. Create `assets/Theme/<flavor>.css` (overrides colors, sizes, spacing) and register it in `cssEntries` in `vite/entries.mjs`.
3. Theme-specific Twig content: `{% if theme() == 'luna' %}`.

Flavors are stored as plain strings; SonataAdmin select fields may need updating separately.
