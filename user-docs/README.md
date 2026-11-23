# Catrobat documentation

User documentation for [Pocket Code](https://catrobat.org) and the other Catrobat apps: getting started, tutorials, teacher material and explanations for every brick. It is published on [share.catrobat.org](https://share.catrobat.org/app/docs).

This folder is part of the [Catroweb](https://github.com/Catrobat/Catroweb) repository (it was the separate repo `Catrobat/docs` until it moved here). Paths and commands in these files are relative to `user-docs/`.

Want to fix or add something? Read [CONTRIBUTING.md](CONTRIBUTING.md). A pull request is all it takes, and the GitHub web editor is enough. A pull request that changes only `user-docs/` runs only the docs checks, not Catroweb's test suite.

## How it fits together

- **Guides** live here as Markdown. `en/` is the source, and every other language folder (`de_DE/`, `es_ES/`, …, one per share locale) is translated from it by volunteers, see [TRANSLATING.md](TRANSLATING.md).
- **The brick reference** is generated from Catroid itself, in every language. `reference/bricks/<BrickType>.md` adds an explanation to a generated brick page; a brick without a file still gets its page.
- **Share** renders what is on `main`. Merging a pull request is publishing: the docs publish workflow copies `user-docs/` to the server and syncs it, no full deploy is needed.

```
en/                     English source pages
<locale>/               translations (see TRANSLATING.md, locales in locales.txt)
reference/bricks/       explanations for generated brick pages, one file per brick type
media/                  images and PDFs used by the pages
scripts/check.py        the checks CI runs (also run locally: ./check.sh, or user-docs/check.sh from the repo root)
scripts/translation_status.py   missing and stale translations per locale
import/                 the one-time import of catrobat.org/docs (script, maps, REPORT.md)
```

## License

The content of `user-docs/` is licensed under [CC BY-SA 4.0](LICENSE). By contributing to it you agree to publish your contribution under the same license. This applies to this folder only; the rest of Catroweb keeps its own license (AGPL-3.0, see the repository's `LICENSE`).
