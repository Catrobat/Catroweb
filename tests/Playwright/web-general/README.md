# Playwright `web/general` comparison suite

This directory mirrors the browser-facing part of `tests/BehatFeatures/web/general/` so we can compare:

- runtime against the existing `Behat (web-general)` CI job
- test authoring style
- failure output and debugging ergonomics

Included in this first pass:

- `app_version.feature`
- `cookie_consent.feature`
- `help.feature`
- browser-facing scenarios from `homepage.feature`
- browser-facing scenarios from `language_switcher.feature`
- `sidebar.feature`
- footer rendering from `statistics.feature`

Intentionally not ported yet:

- `flavors_db_updater_command.feature`
- the registration/upload mutation scenarios from `statistics.feature`

Those are still better covered by Symfony/PHP tests than by a browser runner.
