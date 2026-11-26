# Writing for the Catrobat docs

Most readers are children and teenagers, often using a phone and reading in a second language. Write so a ten-year-old understands it.

## Add or change a page

The docs are the `user-docs/` folder of the [Catroweb](https://github.com/Catrobat/Catroweb) repository; paths here are relative to it.

1. Open the file on [GitHub](https://github.com/Catrobat/Catroweb/tree/main/user-docs) and click the pencil icon, or create a new file under `en/`.
2. Change only the English pages in `en/` and the brick explanations in `reference/bricks/`. The other languages are translated from them, see [TRANSLATING.md](TRANSLATING.md).
3. Open a pull request. A maintainer reviews it; a few minutes after the merge the page is live on share.

## A page

Every page starts with front matter:

```markdown
---
title: Make your cat jump
description: Use "Change Y by" and "Wait" to make an object jump.
order: 30
flavors: [pocketcode]
---

Text in Markdown …
```

- `title` (required) and `description` (one sentence, shown in search results and link previews).
- `order` (optional) sorts pages inside a folder.
- `flavors` (optional) lists the app flavours the page is mainly for (`pocketcode`, `embroidery`, `mindstorms`, `arduino`, `phirocode`, `luna`, `create@school`). Those apps' docs show it first; it is still visible everywhere.
- A folder's own page is `index.md`.

## Bricks, formulas and projects

Show real bricks instead of screenshots. They are rendered by Catroid itself, in the reader's language:

```markdown
::brick{type=SetXBrick}
::formula{name=SIN}
::project{id=PROJECT_ID}
::video{youtube=VIDEO_ID}
```

`type` is the brick's type in a project's `code.xml` (e.g. `SetXBrick`, `IfLogicBeginBrick`). A brick that starts a script uses the type of that script, as it appears in `code.xml`: "When scene starts" is `StartScript`, "When tapped" is `WhenScript`, "When you receive" is `BroadcastScript`. A brick explanation in `reference/bricks/SetXBrick.md` needs no title: the brick page provides it. Its translations in `<locale>/reference/bricks/` have no title either; their front matter holds only the translation state, such as `reviewed: true`.

To feature a finished example in a game tutorial, put its `::project{id=…}` line before the steps and save a square WebP frame in
`media/game-tutorials/examples/<project-id>.webp`. Use a public Share project that demonstrates the lesson. Readers see
the local frame and can choose to load the browser player. Without a local frame or if the browser player does not support the project’s language version, Share uses the ordinary project card.

## Style

- Short sentences. One idea per paragraph. Say "you".
- Explain with an example the reader can try right away.
- Use the brick names as the app shows them, in quotes: "Set x to".
- No jokes or references that only work in one language; the text is translated.

## Images and files

- Put them in `media/<section>/` and link them relatively.
- Allowed: PNG, JPEG, GIF, WebP and PDF. No SVG (CI rejects it): share serves media as plain files and cannot make an SVG opened on its own safe. Export a PNG instead.
- Replacing an image? Overwrite the file under the same name; the page picks up the new one by itself.
- Screenshots: PNG, cropped to what matters, at most 1 MB (CI rejects larger files). Prefer `::brick{…}` over a screenshot of a brick.
- Every image needs alt text: `![The cat in the middle of a jump](../media/tutorials/jump.png)`.

## Translations

Volunteers translate the English pages with their own AI assistant, one pull request per section and language: see [TRANSLATING.md](TRANSLATING.md). A translation stores the hash of the English file it came from (`source_hash`). When the English page changes, the translation is marked stale on share and shows up again in its language's issue.

A human-checked translation gets `reviewed: true` in its front matter. It is not overwritten by a normal translation task, only marked stale, so a reviewer can update it.

## Checks

`./check.sh` (or `user-docs/check.sh` from the repository root) runs the same checks as CI: front matter, links between pages and their `#anchors`, image references, image sizes, the targets in `import/redirects.tsv`, and for translations `source_hash` and embeds.
