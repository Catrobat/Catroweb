# Translations

Translations are checked into the repository and changed through pull requests, like any other code.
Crowdin is no longer used.

## Where they live

`translations/catroweb.<locale>.yaml`, one file per locale. `catroweb.en.yaml` is the source of truth:
every key is added there first.

## Adding or changing a string

1. Add or change the key in `translations/catroweb.en.yaml`.
2. Add the translations for the other locales in the same pull request. AI-generated translations are
   fine; mention it in the PR description so reviewers know to spot-check them.
3. A key missing in a locale falls back to English, so a PR that only touches `en` still works.

## Improving a translation

Native speakers are welcome to fix wording directly: edit the locale file and open a pull request.
