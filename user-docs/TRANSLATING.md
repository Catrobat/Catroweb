# Translating the Catrobat docs

The docs are written in English (`en/` and `reference/bricks/`) and translated by volunteers. You run an AI assistant with your own account (for example Claude Code) and open a pull request. Nothing in the repository calls a translation service: your assistant does the translating, CI only checks the result. Speaking the language well enough to read the result is a big plus, but not required.

## 1. Pick a task

The docs live in the `user-docs/` folder of the [Catroweb](https://github.com/Catrobat/Catroweb) repository, and every path and command below is relative to that folder.

Every language has an issue, "Translate the user docs into <Language> (<locale>)", under the [User docs translations](https://github.com/Catrobat/Catroweb/issues?q=label%3Atranslation) tracking issue. Its task list has one task per section, such as `es_ES: getting-started` or `es_ES: reference/bricks`. One task is one pull request.

1. Pick an unchecked task and comment on the issue that you take it, so nobody does it twice.
2. See which files the task needs: `python3 scripts/translation_status.py es_ES`. Files are `missing` (no translation yet), `stale` (the English page changed after it was translated) or `reviewed-stale` (checked by a person, then the English page changed).
3. The issue updates itself after every merge.

The locale folders are share's locales, listed in [`locales.txt`](locales.txt): `es_ES/`, `pt_BR/`, `zh_TW/` and so on. English variants (`en_GB` …) are not translated; share shows them the English pages. Do not create other folders.

## 2. Where the files go

| English source                  | Translation                           |
| ------------------------------- | ------------------------------------- |
| `en/getting-started/index.md`   | `es_ES/getting-started/index.md`      |
| `reference/bricks/SetXBrick.md` | `es_ES/reference/bricks/SetXBrick.md` |

## 3. The rules

These are the instructions for you and your assistant. CI checks the ones marked (CI).

- **Simple language.** Most readers are children around ten years old, often on a phone and often reading in a second language. Short sentences, everyday words, "you" in the informal form where the language has one. Keep the meaning; do not add or leave out content.
- **Front matter.** Keep every key and value of the English page, except: translate `title` and `description`, and add `source_hash` (below). Quote a value that contains a colon. Brick explanations (`reference/bricks/`) have no title; do not add one. A guide page needs a title (CI).
- **`source_hash`** (CI): the sha256 of the English source file, in quotes, all lowercase: `source_hash: '2551f9dc…'` (the full 64 characters; at least the first 7 are accepted). It tells share which version of the English page you translated, so the page is marked stale when the English page changes. A translation your pull request adds or changes must match today's English file; older translations that are stale may stay as they are (CI). Compute it from the file as it is on `main`:
  - Linux: `sha256sum en/getting-started/index.md`
  - macOS: `shasum -a 256 en/getting-started/index.md`
  - Windows (PowerShell): `(Get-FileHash en/getting-started/index.md).Hash.ToLower()`. Git for Windows may check files out with Windows line endings, which changes the hash; clone with `git clone -c core.autocrlf=false …`.
- **Embeds stay as they are** (CI): `::brick{type=SetXBrick}`, `::formula{name=SIN}`, `::project{id=…}`, `::video{youtube=…}` are copied unchanged, same number, same arguments. They show the real brick in the reader's language by themselves.
- **Links and images keep their targets** (CI): copy the part in `(…)` unchanged and translate only the text in `[…]`. A link to a page not translated yet is fine; share shows the English page until it is. One exception: a brick explanation sits one folder deeper in the translation, so every relative link and image path in `<locale>/reference/bricks/` gets one more `../` (`../../media/…` becomes `../../../media/…`).
- **Alt text is translated**: `![The cat jumps](../media/jump.png)` becomes `![El gato salta](../media/jump.png)`.
- **Code, numbers and Markdown structure stay**: headings stay headings at the same level, lists stay lists, tables keep their columns. Do not translate code, file names, variable names in examples or URLs.
- **Brick names as the app shows them** in that language. They come from Catroid's own translations: [`catroid/src/main/res/values-<language>/strings.xml`](https://github.com/Catrobat/Catroid/tree/develop/catroid/src/main/res) (`values-es`, `values-pt-rBR`, `values-zh-rCN`, …), the strings named `brick_…`. For example "Set x to" is `brick_set_x`. Use that text, in the quotes the language uses. If a string is missing there, keep the English name.
- **App names stay**: Pocket Code, Catrobat, Embroidery Designer, Pocket Paint and the like are not translated.
- **`reviewed: true` pages** were checked by a person. Do not overwrite them as part of a normal task. If one is `reviewed-stale`, update only what changed in the English page (compare with `git log -p en/…`), keep `reviewed: true` only if a person who speaks the language checked your change, and say so in the pull request. CI fails a pull request that changes or deletes a page that is `reviewed: true` on `main`, unless a maintainer adds the label `update-reviewed` to it; a new push removes the label again, so it always covers the latest commit (CI).
- **Reviewed pages without `source_hash`** (the German pages imported from the old website) count as current and never turn stale. To have one retranslated, a maintainer removes its `reviewed: true`; it then shows up as stale in its language's issue. A pull request that changes such a page adds a `source_hash`.

## 4. A prompt for your assistant

Fork and clone [Catroweb](https://github.com/Catrobat/Catroweb), then start your assistant in the clone's `user-docs/` folder and paste this. Replace every `es_ES` with your locale and `getting-started` with your section:

```text
Translate the Catrobat docs task "es_ES: getting-started" in this folder (user-docs/).
Read TRANSLATING.md first and follow section 3 exactly.
Run `python3 scripts/translation_status.py es_ES` and translate every missing or stale file of that
section from English into the language of the locale. Leave reviewed-stale files alone. Write each translation to the matching path under es_ES/, with source_hash set to the sha256
of the English file. Look up brick names in Catroid's strings.xml for that language.
Then run ./check.sh and fix every problem it reports. Do not change any file outside es_ES/.
```

Read a few pages of the result. A strange sentence is worth fixing by hand.

## 5. Check and open the pull request

1. Run `./check.sh` (needs only Python 3). It must print `0 problem(s)` and `check.py self-test passed`. It checks the translations you changed against `upstream/main` (add it with `git remote add upstream https://github.com/Catrobat/Catroweb.git && git fetch upstream`), else `origin/main`.
2. Commit only the files of your task, on a branch of your fork.
3. Open one pull request per task, titled like the task: `es_ES: getting-started`. Say which assistant you used and whether someone who speaks the language read it.

A maintainer merges it once CI is green. The page is live on share a few minutes later, marked "translated automatically" until someone reviews it.

## Reviewing a translation

If you speak the language: read the page on share or in the file, fix what is wrong and add `reviewed: true` to the front matter in a pull request titled `es_ES: review getting-started`. Later changes to a reviewed page need the label `update-reviewed` on the pull request.
