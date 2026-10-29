# Catroweb Coding Standard

A project should look like it was written by one developer. CI checks style on every PR and fails on errors without fixing them, so run the checks before pushing:

| Scope              | Check             | Fix              |
| ------------------ | ----------------- | ---------------- |
| PHP (PHP-CS-Fixer) | `yarn test-php`   | `yarn fix-php`   |
| JS/JSON (Biome)    | `yarn test-js`    | `yarn fix-js`    |
| CSS (Biome)        | `yarn test-css`   | `yarn fix-css`   |
| Markdown/YAML      | `yarn test-asset` | `yarn fix-asset` |
| Twig               | `yarn test-twig`  | `yarn fix-twig`  |
| All                | `yarn test`       | `yarn fix`       |

All CSS, JS and image sources belong in `assets/`; never put them into `public/` directly (the Vite build does that).

Markdown and YAML are excluded from Biome because Prettier handles their formatting. When updating Biome, update the schema version in `biome.json` alongside the package and lockfile.
