# Translating the Catrobat docs

The docs are written in English (`en/` and `reference/bricks/`) and translated by volunteers. You run an AI assistant with your own account (for example Claude Code) and open a pull request. Nothing in the repository calls a translation service: your assistant does the translating, CI only checks the result. Speaking the language well enough to read the result is a big plus, but not required.

## 1. Pick a task

The docs live in the `user-docs/` folder of the [Catroweb](https://github.com/Catrobat/Catroweb) repository, and every path and command below is relative to that folder.

Every language has an issue, "Translate the user docs into <Language> (<locale>)", under the [User docs translations](https://github.com/Catrobat/Catroweb/issues?q=label%3Atranslation) tracking issue. Its task list has one task per section, such as `es_ES: getting-started` or `es_ES: reference/bricks`. One task is one pull request.

1. Pick an unchecked task. If you like, comment on the issue that you take it; you do not have to: your pull request, which mentions the issue, is enough.
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
- **Front matter.** Keep every key and value of the English page, except: translate `title` and `description`, and add `source_hash` (below). An English page without `description` has none in the translation either; do not add one. Quote a value that contains a colon. Brick explanations (`reference/bricks/`) have no title; do not add one. A guide page needs a title (CI).
- **`source_hash`** (CI): the sha256 of the English source file, in quotes, all lowercase: `source_hash: '2551f9dc…'` (the full 64 characters; at least the first 7 are accepted). It tells share which version of the English page you translated, so the page is marked stale when the English page changes. A translation your pull request adds or changes must match today's English file; older translations that are stale may stay as they are (CI). Compute it from the file as it is on `main`:
  - Linux: `sha256sum en/getting-started/index.md`
  - macOS: `shasum -a 256 en/getting-started/index.md`
  - Windows (PowerShell): `(Get-FileHash en/getting-started/index.md).Hash.ToLower()`. Git for Windows may check files out with Windows line endings, which changes the hash; clone with `git clone -c core.autocrlf=false …`.
- **Embeds stay as they are** (CI): `::brick{type=SetXBrick}`, `::formula{name=SIN}`, `::project{id=…}`, `::video{youtube=…}` are copied unchanged, same number, same arguments. They show the real brick in the reader's language by themselves.
- **Links and images keep their targets** (CI): copy the part in `(…)` unchanged and translate only the text in `[…]`. A link to a page not translated yet is fine; share shows the English page until it is. One exception: a brick explanation sits one folder deeper in the translation, so every relative link and image path in `<locale>/reference/bricks/` gets one more `../` (`../../media/…` becomes `../../../media/…`).
- **Anchors follow the translated headings** (CI): the part after `#` in `(#add-a-sound)` or `(page.md#add-a-sound)` is the id of a heading, made from the heading's text: lowercase, spaces become `-`, everything except letters, digits and `-` is dropped, accents stay. Once you translate the heading, translate the anchor the same way: `### Añadir un sonido` has the id `añadir-un-sonido`. A link to a page that has no translation yet keeps the English anchor, because share shows the English page. CI reports every anchor without a matching heading.
- **Alt text is translated**: `![The cat jumps](../media/jump.png)` becomes `![El gato salta](../media/jump.png)`.
- **Code, numbers and Markdown structure stay**: headings stay headings at the same level, lists stay lists, tables keep their columns. Do not translate code, file names, variable names in examples or URLs.
- **Brick names and other app labels as the app shows them** in that language. They come from Catroid's own translations: [`catroid/src/main/res/values-<language>/strings.xml`](https://github.com/Catrobat/Catroid/tree/develop/catroid/src/main/res) (`values-es`, `values-pt-rBR`, `values-zh-rCN`, …). Bricks are the strings named `brick_…`: "Set x to" is `brick_set_x`. Buttons, menus and screens (Backpack, Pack, Looks, Library, Empty object …) are in the same file: find the English text in [`values/strings.xml`](https://github.com/Catrobat/Catroid/blob/develop/catroid/src/main/res/values/strings.xml), note the string's `name`, and use the text with that name in your language's file, in the quotes the language uses. If your language's file has no such string, keep the English label: the app shows the English one then too.
- **App names stay**: Pocket Code, Catrobat, Embroidery Designer, Pocket Paint and the like are not translated.
- **`reviewed: true` pages** were checked by a person. Do not overwrite them as part of a normal task. If one is `reviewed-stale`, update only what changed in the English page (compare with `git log -p en/…`), keep `reviewed: true` only if a person who speaks the language checked your change, and say so in the pull request. CI fails a pull request that changes or deletes a page that is `reviewed: true` on `main`, unless a maintainer adds the label `update-reviewed` to it; a new push removes the label again, so it always covers the latest commit (CI).
- **Reviewed pages without `source_hash`** (the German pages imported from the old website) count as current and never turn stale. To have one retranslated, a maintainer removes its `reviewed: true`; it then shows up as stale in its language's issue. A pull request that changes such a page adds a `source_hash`.

## 4. Fork, clone and branch

You need a GitHub account and git. The branch is named `docs-translate/<locale>/<section>`, for example `docs-translate/es_ES/getting-started`.

With the [GitHub CLI](https://cli.github.com/) (`gh`):

```sh
gh repo fork Catrobat/Catroweb --clone   # your fork is "origin", Catrobat/Catroweb is "upstream"
cd Catroweb
git fetch upstream
git switch -c docs-translate/es_ES/getting-started upstream/main
```

In the browser: open [Catrobat/Catroweb](https://github.com/Catrobat/Catroweb), click **Fork**, then **Create fork**. Then, with your GitHub user name for `<you>`:

```sh
git clone https://github.com/<you>/Catroweb.git
cd Catroweb
git remote add upstream https://github.com/Catrobat/Catroweb.git
git fetch upstream
git switch -c docs-translate/es_ES/getting-started upstream/main
```

## 5. A prompt for your assistant

Start your assistant in the clone's `user-docs/` folder and paste this. Replace every `es_ES` with your locale and `getting-started` with your section:

```text
Translate the Catrobat docs task "es_ES: getting-started" in this folder (user-docs/).
Read TRANSLATING.md first and follow section 3 exactly.
Run `python3 scripts/translation_status.py es_ES` and translate every missing or stale file of that
section from English into the language of the locale. Leave reviewed-stale files alone. Write each translation to the matching path under es_ES/, with source_hash set to the sha256
of the English file. Look up brick names and other app labels in Catroid's strings.xml for that language.
Translate the anchors of links (#...) to match the translated headings.
Then run ./check.sh and fix every problem it reports. Do not change any file outside es_ES/.
```

Read a few pages of the result. A strange sentence is worth fixing by hand.

## 6. Check and open the pull request

1. In `user-docs/`, run `./check.sh` (needs only Python 3). It must print `0 problem(s)` and `check.py self-test passed`. It checks the translations you changed against `upstream/main`, else `origin/main`. A `warning:` line is about a page you did not change; it does not fail the check. There is no formatter to run: `user-docs/` is excluded from the repository's Prettier check, so `yarn` is not needed.
2. Commit only the files of your task and push the branch to your fork:

   ```sh
   git add es_ES
   git commit -m "es_ES: getting-started"
   git push -u origin docs-translate/es_ES/getting-started
   ```

3. Open one pull request per task against `main` of Catrobat/Catroweb, titled like the task: `es_ES: getting-started`. In the description, mention the language's issue (`Part of #1234`), say which assistant you used and whether someone who speaks the language read it.
   - With `gh`: `gh pr create --repo Catrobat/Catroweb --base main --title "es_ES: getting-started" --body "Part of #1234. Translated with …"`
   - In the browser: open your fork on GitHub, click **Compare & pull request** (or **Contribute**, then **Open pull request**), check that the base is `Catrobat/Catroweb` `main`, fill in title and description and click **Create pull request**.

A maintainer merges it once CI is green. The page is live on share a few minutes later, marked "translated automatically" until someone reviews it.

## Reviewing a translation

If you speak the language: read the page on share or in the file, fix what is wrong and add `reviewed: true` to the front matter in a pull request titled `es_ES: review getting-started`. Later changes to a reviewed page need the label `update-reviewed` on the pull request.
