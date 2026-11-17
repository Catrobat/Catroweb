# Machine Translation

The translate buttons on project pages and comments use a self-hosted [LibreTranslate](https://libretranslate.com) (`LibreTranslateApi`). It is the only provider and needs no API key.

- One text per request, `source: auto` unless the user picked a source language: with `auto`, LibreTranslate detects a single language for a whole batch.
- A project's translated name, description and credits are cached in `project_machine_translation` on the first successful translation, per source and target language. Editing the project clears its cache. Comments are not cached.
- The language dropdowns only offer languages from LibreTranslate's `GET /languages`, cached for a day. While LibreTranslate is unreachable the list is empty (retried after 5 minutes) and the dropdowns offer every site language. When LibreTranslate has no model for the site language, translations go to English.
- A failed translation answers 503 and logs the warning `Machine translation failed: <reason>, …`; the infra watchdog counts these lines.

## Configuration

| Variable             | Default                 | Notes                          |
| -------------------- | ----------------------- | ------------------------------ |
| `LIBRETRANSLATE_URL` | `http://127.0.0.1:5005` | Base URL, without `/translate` |

Keep it on loopback (or HTTPS): requests carry user-written project texts and comments.

Dev: `docker run -d -p 127.0.0.1:5005:5000 libretranslate/libretranslate` (the first start downloads the language models). Without it, translating fails with 503 and the rest of the site works. The production container is set up in the [infrastructure repo](https://github.com/Catrobat/infrastructure).
