# Translations

Translations are checked into the repository and changed through pull requests, like any other code.
Crowdin is no longer used.

## Where they live

`translations/catroweb.<locale>.yaml`, one file per locale. `catroweb.en.yaml` is the source of truth:
every key is added there first.

## Adding or changing a string

1. Add or change the key in `translations/catroweb.en.yaml`.
2. Add the translations for **all** other locales in the same pull request. AI-generated translations are
   fine; mention it in the PR description so reviewers know to spot-check them. Keep placeholders like
   `%username%` exactly, and address users informally where the language distinguishes (most are kids).
3. Remove a key from every locale when the code stops using it.

`tests/PhpUnit/Translation/TranslationFilesTest.php` fails when a locale misses an English key, has a key
English no longer has, or changes a placeholder. At runtime a missing key silently falls back to English,
which is how about 94 keys once went untranslated in every locale.

## Improving a translation

Native speakers are welcome to fix wording directly: edit the locale file and open a pull request.
