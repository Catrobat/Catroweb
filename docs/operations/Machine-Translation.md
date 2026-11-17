# Machine Translation

The translate buttons on project pages and comments use a self-hosted [LibreTranslate](https://libretranslate.com) (`LibreTranslateApi`). It is the only provider and needs no API key.

- One text per request, `source: auto` unless the user picked a source language: with `auto`, LibreTranslate detects a single language for a whole batch.
- Projects are translated in the background: LibreTranslate needs about 1 s per 30 characters on the production container, so a long description takes far longer than a web request may wait. `GET /api/projects/{id}/translation` answers from `project_machine_translation` (200); without a cached translation it queues a `TranslateProject` job on its own `translation` queue (a backlog never delays moderation scans) and answers 202 with `Retry-After: 2`. The page asks again every 3 s for up to 2 minutes. A failed job answers 503 once, then the next request queues a new one. The job state (queued or failed, 3 minutes) is kept in `cache.app` (Redis in production), which web requests and workers share.
- Cached translations are per project, source and target language. Each row stores a hash of the translated texts, so a project edit (even one during a running job) makes it stale. Requests without a source language only use rows whose source LibreTranslate detected (`auto_detected`), so a wrong source picked by one user is never shown to others.
- Comments are short and are translated within the request (5 s per call), without a cache.
- The language dropdowns only offer languages from LibreTranslate's `GET /languages`, cached for a day. While LibreTranslate is unreachable the list is empty (retried after 5 minutes) and the dropdowns offer every site language. When LibreTranslate has no model for the site language, translations go to English.
- LibreTranslate runs on the web host's CPU, so `config/packages/rate_limiter.php` limits both translate endpoints per IP (`translation_burst`, 30 requests a minute, then 429) and all LibreTranslate calls site-wide (`translation_global`, 100 a minute; cache hits are free; over the limit a translation fails like an outage). In a web request each call times out after 5 s, in the project job after 120 s. Only a request that queues a new project job counts against `translation_burst`; polls and cache hits are free.
- A failed translation answers 503 and logs the error `Machine translation failed: <reason>, …`; the infra watchdog counts these lines.

## Configuration

| Variable             | Default                 | Notes                          |
| -------------------- | ----------------------- | ------------------------------ |
| `LIBRETRANSLATE_URL` | `http://127.0.0.1:5005` | Base URL, without `/translate` |

Keep it on loopback (or HTTPS): requests carry user-written project texts and comments.

Dev: `docker run -d -p 127.0.0.1:5005:5000 libretranslate/libretranslate` (the first start downloads the language models). Without it, translating fails with 503 and the rest of the site works. Project translations need a worker for the `translation` queue (`bin/console messenger:consume translation`); without one they stay at 202. The production container is set up in the [infrastructure repo](https://github.com/Catrobat/infrastructure).
