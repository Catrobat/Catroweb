# ADR: Rebuild Scratch to Catrobat conversion (Phase 0 evaluation)

**Date**: 2026-10-20
**Status**: Proposed; implementation requires explicit maintainer approval
**Issue**: [#7419](https://github.com/Catrobat/Catroweb/issues/7419)
**Related**: [Scratch search card placeholder #7420](https://github.com/Catrobat/Catroweb/issues/7420)

## Context

`ScratchController` → `ScratchManager` → `ProjectManager::createProjectFromScratch` persists Scratch metadata and a thumbnail only. It never creates a Catrobat archive or enters upload validation/scanning, so a redirect to that project page is not a conversion. The archived [ScratchToCatrobat](https://github.com/Catrobat/ScratchToCatrobat/tree/5433ec7fe86375e37cdcd98971c0a6f70cb9c300) converter is a specification and test corpus, not a deployable service: it imports Java Catroid classes through Jython, so Python 3 cannot run it unchanged, and restoring the Jython service is rejected.

Phase 0 was a source-and-data spike: ten public Scratch 3 projects (10,517 block objects, 115 distinct opcodes) were inventoried against the legacy visitor table. 113 opcodes have a legacy visitor; `event_whenstageclicked` and `data_deletealloflist` are missing. Visitor presence is not semantic coverage (sound effects emit notes, procedure `warp` is discarded, broadcast names are lowercased). **No project was converted and nothing was opened in Pocket Code**; compatibility and performance remain unproven.

## Decision (proposed)

### Placement and language

A **PHP converter in a separate, restricted worker container**, orchestrated by Catroweb.

- Catroweb's orchestration owns fetching, authorization, job state, deduplication, persistence and upload policy.
- The worker receives a bounded immutable input bundle and returns an archive plus structured diagnostics. No public ingress, no DB credentials, no outbound network.
- Port into an explicit intermediate model and an allowlisted XML writer. Never execute Scratch JavaScript or deserialize untrusted input through XStream.
- Native raster/audio tools (pinned, under subprocess limits) are reused in every language option.

Why PHP: existing PHPUnit, ingestion integration and XML tooling mean the fewest toolchain boundaries. Every option (PHP, Go, Python 3, Rust) needs a full semantic port; similar syntax doesn't make Python cheaper. A bounded parse/validate benchmark over the corpus showed PHP fastest on warm repeated parsing, Go lower on RSS (~18 MiB less) and process start (~40 ms less); Rust was not measured. That front-end benchmark does not settle full-conversion cost. **Go stays the alternative** if per-job startup or measured full-conversion CPU/RSS outweighs integration effort; profile real mapping, XML/ZIP writing and assets before switching.

### Asynchronous jobs and publication

- Symfony Messenger with the Doctrine transport (not installed today; separate PR), one consumer initially. No Redis just for this.
- A request enqueues or reuses a durable job and returns a status reference. States: queued, fetching, converting, validating, ready, ready-with-warnings, failed. Specify the endpoint in OpenAPI first; a job ID must never be mistaken for a project ID.
- Only a validated, scanned artifact becomes a downloadable Project. Extract a shared ingestion service from the upload path (ownership/attribution, malware scan, sanitization, visibility, events, cleanup) instead of calling a controller.
- **Approved policy:** converted images are scanned and publication is held until required scans pass. Unsafe → reject; unavailable/skipped → hold with retryable status. (`ContentSafetyScanner` is currently only called for avatars and treats skipped as safe, so this needs an explicit integration step.)
- Publish archive and ready status atomically; idempotency key on retries; a crashed worker releases its lease; recheck source revision and visibility before publishing.

### Supported subset for v1

- Supported: events (flag, sprite/stage click, broadcast), control (wait, loops, conditionals, stop, clones), basic motion, costumes/backdrops, show/hide/size/layering, speech, sound playback/volume, operators, variables/lists, mouse/touch, collision/distance/timer, basic pen. Add the two missing visitors.
- Excluded initially: cloud variables, hardware/extensions, video sensing, music, sound effects, custom procedures (they occur in the corpus; those projects are explicitly degraded), graphic effects other than brightness/ghost, unverified sensor variants. Keyboard emulation and coordinate/rotation-center handling need product decisions.
- "Supported" is earned per opcode and input variant by ported tests plus Pocket Code verification.
- Degradation: unsupported stack blocks → `NoteBrick` with opcode/sprite/block id and a structured warning; unsupported reporters → warning note plus a typed neutral value (0, "", false), never a NoteBrick in a formula; unsupported hats keep their script disabled. Invalid graphs, missing indispensable assets and limit violations fail the job. "Ready with warnings" must not imply Scratch-equivalent behavior.

### Assets and sandbox

- Fetch `project_token` from metadata, then JSON from `projects.scratch.mit.edu/{id}?token=…`; never log tokens. Assets only from Scratch's asset host, with hash and MIME verification.
- SVG external references/scripts disabled; rasterize to PNG with a pinned tool honoring bitmapResolution and rotation center; convert unsupported audio with a pinned codec.
- Starting limits (unmeasured hypotheses): one job per worker, 1 GiB memory, 120 s wall / 60 s CPU, 16 MiB JSON, 100,000 blocks, 2,000 assets, 32 MiB per asset, 128 MiB total input, 256 MiB decoded raster, 512 MiB scratch disk. Measure per project before release (the largest corpus project has 803 assets).
- Read-only root FS, non-root, dropped capabilities, no network, no shell interpolation. Validate worker output (paths, ZIP sizes, entry counts, traversal) before ingestion.

### Output and acceptance

- Write Catrobat language **1.14** (Catroid `CURRENT_CATROBAT_LANGUAGE_VERSION` at the inspected commit), conditional on the Pocket Code build chosen for acceptance supporting it.
- XML must follow current Catroid serializer fixtures (scenes, formula trees, references), not the old flat layout.
- Each mapping PR: port legacy cases, add unsupported-input cases, compare normalized XML to golden fixtures exported from the chosen Pocket Code build. A golden end-to-end fixture must pass Catroweb's extractor/sanitizer and import through the current Catroid serializer.
- Before release: convert all ten corpus projects, open and run each in Pocket Code against a written scenario, record differences. Archive a licensed, pinned fixture bundle; live Scratch URLs are not golden fixtures.

### Licensing and caching

- Legacy converter is AGPL-3.0-or-later with a section-7 exception reference: preserve attribution and check that exception and third-party notices before porting code or tests. Preserve Scratch URL, creator, credits and revision in the converted project; check redistribution terms before committing third-party assets as fixtures.
- Cache key: Scratch ID, metadata modified timestamp, JSON SHA-256, converter build, subset version, asset-normalizer version, output language version. Revalidate metadata with a short TTL; stale cache must not bypass access checks; short negative TTL on failures.

## Open approvals

1. The isolated-worker, Doctrine-backed async design with PHP as the working language.
2. The v1 subset, degraded downloads with note bricks, deferred custom procedures, keyboard behavior and warning prominence.
3. Language 1.14 tied to the acceptance build, and the mandatory extractor/serializer/open/run gates.
4. The proposed limits as hypotheses, plus licensing and fixture-retention review before importing tests.

## Phased plan

After approval, small issue-first PRs, each with green CI, an independent review and separate maintainer authorization to merge:

1. Bounded fetching (metadata, token, JSON, assets with limits).
2. Intermediate model and parser.
3. Current XML writer plus an ingestion golden test.
4. Category mappings.
5. Assets (raster/audio normalization).
6. Job status, API integration and operations.
