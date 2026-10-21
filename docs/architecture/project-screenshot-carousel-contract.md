# Additional project screenshots: proposed API and carousel contract

**Status:** Design for review; no implementation or deployment approval.
**Design issue:** [#7445](https://github.com/Catrobat/Catroweb/issues/7445).
**Feature:** [#7377](https://github.com/Catrobat/Catroweb/issues/7377) remains open.
**Source baseline:** Catroweb `d1a5888103cd5b5406d62778a39fbcfb9017fa9a`, 21 October 2026.

The accepted scope is up to eight extra screenshots, compatible main-image fields and
endpoints, required scanning before publication, moderation coverage, and consistent
not-for-kids (NFK) blurring. This document proposes the remaining API, storage and web
rules. Routes, JSON and limits below are **proposed contracts**, not live endpoints or
changes to the generated OpenAPI specification. Catroid integration is a later task.

## Existing behavior and integration gaps

| Source                                                                                                                                                                                                                                                            | Verified behavior                                                                                                                                                                                                      | Consequence for this design                                                                                                                                                                                    |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| [ProjectsRequestValidator](../../src/Api/Services/Projects/ProjectsRequestValidator.php), `MAX_UPLOAD_SIZE_BYTES` and `validateScreenshot`                                                                                                                        | Project archives have a 100 MiB application limit. The main screenshot update accepts an image data URI.                                                                                                               | The archive limit is an outer ceiling, not a safe per-image allowance. Keep old requests compatible; use a separate bounded multipart upload for extras.                                                       |
| [ProjectsApiProcessor](../../src/Api/Services/Projects/ProjectsApiProcessor.php), `updateProject`                                                                                                                                                                 | Main-image updates call `ScreenshotRepository::updateProjectAssets`; this path does not call the content-safety scanner.                                                                                               | Reusing storage alone does not implement the newly required scan gate. Do not claim that the existing main-image path already satisfies it.                                                                    |
| [ScreenshotRepository](../../src/Storage/ScreenshotRepository.php)                                                                                                                                                                                                | Writes one `screen_{project-id}.png`, square 480 px main image and 80 px thumbnail; updates the extracted manual screenshot. Variant generation is best effort. Its legacy deletion helper removes only the named PNG. | Extras need separate IDs/storage, aspect-preserving normalization, explicit publication and complete variant deletion. Never overwrite the existing main image or modify archive contents for an extra upload. |
| [ImageVariantLayout](../../src/Storage/Images/ImageVariantLayout.php), [ImageVariantGenerator](../../src/Storage/Images/ImageVariantGenerator.php)                                                                                                                | Thumb/card/detail widths 96/320/960 at 1x and 2x, WebP and optional AVIF; no upscale; orientation then metadata stripping; per-file temporary rename.                                                                  | Reuse this layout and encoder logic, but publish an entire validated generation atomically. Optional AVIF support and mandatory WebP completion need explicit handling.                                        |
| [ImageVariantUrlBuilder](../../src/Storage/Images/ImageVariantUrlBuilder.php)                                                                                                                                                                                     | Builds public paths with timestamps and nulls for missing files.                                                                                                                                                       | Reuse the response shape, not an unauthenticated public storage prefix for extras.                                                                                                                             |
| [ProjectManager](../../src/Project/ProjectManager.php), `findProjectIfVisibleToCurrentUser`                                                                                                                                                                       | Project-page visibility has super-admin, featured, owner/admin auto-hidden, minor-owner and debug-request exceptions; private projects are viewable by ID.                                                             | Every ordinary extra-image read must call the same policy. This design does not redefine private projects.                                                                                                     |
| [ProjectsApiLoader](../../src/Api/Services/Projects/ProjectsApiLoader.php), [ProjectRepository](../../src/DB/EntityRepository/Project/ProjectRepository.php)                                                                                                      | Existing editing lookup excludes invisible, auto-hidden and disallowed debug projects; includes private projects when requested; checks minor visibility.                                                              | Preserve this stricter editing eligibility for owner mutations, separately from page reads.                                                                                                                    |
| [ContentSafetyScanner](../../src/Security/ContentSafety/ContentSafetyScanner.php), [ContentSafetyResult](../../src/Security/ContentSafety/ContentSafetyResult.php)                                                                                                | Supports an image scan, but skipped/unavailable results have `safe=true`; response parsing defaults a missing score to zero. Current observed application caller is user-picture validation.                           | A new required-scan adapter must distinguish a performed valid verdict from skipped, unavailable or malformed results. Testing `safe` alone is insufficient.                                                   |
| [MalwareScanner](../../src/Security/Malware/MalwareScanner.php), [ProjectManager](../../src/Project/ProjectManager.php)                                                                                                                                           | Project archive ingestion calls malware scanning. Disabled/fail-open scans may produce clean-but-not-performed results.                                                                                                | Scan every extra input explicitly and require `scan_performed`, not merely `is_clean`. Do not infer image coverage from archive scanning.                                                                      |
| [ContentReport](../../src/DB/Entity/Moderation/ContentReport.php), [ReportProcessor](../../src/Moderation/ReportProcessor.php)                                                                                                                                    | Reports identify project/comment/user/studio, with one report per reporter/type/content, trust limits and whole-content hide/appeal behavior. There is no screenshot target.                                           | The recommended extension is project-level reports with immutable screenshot context and no additional trust vote. Repeated-image evidence needs the explicit follow-up contract below.                        |
| [ModerationResponseManager](../../src/Api/Services/Moderation/ModerationResponseManager.php), [ModerationQueueAdmin](../../src/Admin/Moderation/ModerationQueueAdmin.php), [content link template](../../templates/Admin/CRUD/list__field_content_link.html.twig) | API queue returns report metadata; the admin list links to the current content page. Neither supplies an all-images/evidence review model.                                                                             | Extend both moderator surfaces before enabling uploads. A link to the changing public page is not retained evidence.                                                                                           |
| [ProjectPage](../../templates/Project/ProjectPage.html.twig), [Thumbnail](../../templates/Project/Thumbnail.html.twig), [Project.js](../../assets/Project/Project.js)                                                                                             | Render the main picture; NFK handling includes thumbnail classes and single-image toggling.                                                                                                                            | Carousel, thumbnails, enlarged view and flag changes must share one NFK presentation state. Existing single-image behavior is not proof that all extras blur.                                                  |

No application or server behavior above was changed or exercised in production for this
proposal. The existing PHP upload settings in `docker/Dockerfile` are 100M per file and
105M per request; these are deployment ceilings, not the new image admission policy.

## Compatibility and data model

Keep the existing main screenshot request/response fields, default image, variants,
archive screenshot selection, download behavior and project-list cards unchanged.
The carousel's first slide is always the main image. Extra images are additional web
metadata, never automatically inserted into or exported from a Catrobat archive.

Propose `ProjectScreenshot` with server-generated UUIDv4 `id`, project FK, uploader FK,
UTC creation/update timestamps, immutable input hash/byte count, oriented width/height,
state, generation UUID, scan policy/model versions and internal storage keys. IDs are
opaque identifiers, not authorization credentials. Client filenames are discarded and
never become paths. No uploader IP, email or scanner score belongs in public responses.

Keep an ordered list of non-terminal extra IDs and an aggregate revision on the project.
The main image is not a row in that list and cannot be reordered or deleted through
these endpoints. Ready, pending and processing entries all occupy one of eight slots.
Rejected/failed/deleted entries release their slot and input budget exactly once;
terminal status metadata remains owner-readable for the proposed retention window.

An image's bytes are immutable. Replacing it means delete plus a new upload/ID. Reordering
does not change its ID or generation. Store a unique `(project, screenshot-id)` relation,
a unique per-project ordinal, and transactional quota counters; verify counters with a
reconciliation job. Do not rely on a preflight `COUNT` without a lock.

## Proposed limits and format policy

These numbers are engineering proposals requiring acceptance before implementation;
they are not measured capacity or existing product limits. MiB means 1,048,576 bytes.

| Bound                  | Proposal                                                                                                 | Rationale                                                                                                                                                                |
| ---------------------- | -------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Main image             | Existing behavior and limits retained                                                                    | Avoid breaking older clients or silently applying a migration.                                                                                                           |
| Extras                 | Eight non-terminal rows per project                                                                      | Accepted product maximum; pending scans cannot bypass it.                                                                                                                |
| One input              | 8 MiB compressed bytes; one file per request                                                             | Allows typical phone/tablet screenshots without exposing the 100 MiB archive budget to each image decoder.                                                               |
| Multipart body         | 9 MiB, streamed enforcement, at most one `file` part; no other parts                                     | Bounded overhead; reject before decoder allocation. Never raise the existing outer upload ceiling.                                                                       |
| Decode                 | Positive dimensions, each edge at most 4096, at most 12,000,000 pixels, exactly one frame                | Includes common portrait/landscape and 3840x2160 inputs; bounds roughly 48 MB of one RGBA surface, not total decoder memory.                                             |
| Logical source budget  | 32 MiB across pending/processing/ready extras, counted from accepted input byte lengths                  | Eight entries are not a promise that eight maximum-sized files fit. Release on terminal transition/deletion, including after original quarantine bytes have been erased. |
| Published bytes        | At most 8 MiB per complete master-plus-variant generation, hence at most 64 MiB for eight ready images   | Bound derivative amplification; fail generation if it exceeds this cap.                                                                                                  |
| Active project storage | At most 32 MiB input quarantine plus 64 MiB published sets: 96 MiB for extras                            | Main assets, bounded worker scratch and retained moderation evidence are separate inventories, not hidden inside this estimate.                                          |
| Worker                 | One image per job; initial proposed 256 MiB memory, 128 MiB scratch, 30 s wall deadline                  | Resource settings must be measured on worst-case fixtures/codecs before launch; pixel estimates alone cannot prove the process fits.                                     |
| Submission rate        | New proposed per-user 8/5 min and 32/day, plus per-IP 32/5 min; no admin exemption on owner upload route | Separate from archive `upload_daily`, so managing screenshots does not consume archive-upload allowance. Exact retries do not spend another submission token.            |

Accept detected JPEG, PNG and static WebP only. Reject SVG/XML/HTML, GIF, HEIC, AVIF
inputs, animations/multi-frame containers, malformed/truncated files and mismatched
multipart MIME versus detected format. Filename/extension is irrelevant. Require a
full bounded decode; header dimensions alone are not validation. No URLs or data URIs
are accepted by the extra endpoint. Enforce byte limits during streaming even without
Content-Length. PHP may spool multipart data before the controller runs; enforce the
9 MiB route limit and bounded concurrent ingress/temp storage at the edge too. Check
authentication before expensive decode/scanning, not only after allocating a decoder. Bound decoder threads, time and disk, disable remote/delegate reads,
and reject rather than silently selecting an animation frame.

After malware scanning, normalize orientation, convert to sRGB before stripping
metadata, preserve alpha, and fit within 1920x1920 without cropping or upscaling.
Encode a canonical WebP master, maximum 4 MiB. Generate the existing 96/320/960 × 1x/2x
layout, mandatory six WebP files and optional six AVIF files when supported. The complete
set, including master, must fit 8 MiB; otherwise fail with `render_budget_exceeded`.
Preserve aspect ratio with contain-style presentation. No client alt text/captions in
v1; localized ordinal labels avoid adding another public-text input surface.

The [OWASP upload guidance](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)
supports independent type/content checks, generated names, restricted storage, scanning
and size controls. These particular numeric budgets are proposed here, not taken from
that guidance. Decoder and codec conformance testing remains required.

## Proposed HTTP surface

All API paths below are under `/api`. UUIDs are lowercase canonical UUIDv4 strings;
unknown and foreign-project screenshot IDs are indistinguishable. Ordinary reads and
all error responses are `Cache-Control: private, no-store`, with `Vary: Cookie,
Authorization`. JSON uses UTF-8 and UTC RFC3339 timestamps. No pagination is needed for
at most eight active entries. Management `history` returns at most the latest 32
terminal metadata rows, newest first by timestamp/ID; older metadata is internal until
retention expiry. It does not expose retained report evidence.

| Method/path                                                                     | Request and audience                                                                                            | Success                                                                                                                                      |
| ------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /projects/{id}/screenshots`                                                | Project-page viewer; no body                                                                                    | `200` public-view collection: ready extras only, current ordering, no internal states or uploader metadata.                                  |
| `GET /projects/{id}/screenshots?view=manage`                                    | Authenticated current owner who passes the same page read policy                                                | `200` ready and pending entries plus retained terminal statuses and quota counters. Admin status alone does not grant this view.             |
| `POST /projects/{id}/screenshots`                                               | Eligible owner; `multipart/form-data` with exactly one binary `file`; required `If-Match` and `Idempotency-Key` | `202`, pending item, owner-view collection ETag, `Content-Location` pointing to the item status URL, `Retry-After: 5`. No image URL yet.     |
| `GET /projects/{id}/screenshots/{sid}`                                          | Eligible owner status view; project-page read policy also applies                                               | `200` item including state; ready items have variants. Other viewers use collection/byte routes, not this management status endpoint.        |
| `PATCH /projects/{id}/screenshots`                                              | Eligible owner; JSON `{"order":["uuid-a","uuid-b"]}`; required owner `If-Match`                                 | `200` owner collection and new ETag. Supply exactly the current non-terminal IDs, once each, in desired order; no gaps or partial patches.   |
| `DELETE /projects/{id}/screenshots/{sid}`                                       | Eligible owner; required owner `If-Match`; no body                                                              | `204`, logically removed immediately, new owner collection ETag. Original, generations, caches and queued jobs enter deletion lifecycle.     |
| `GET` or `HEAD /projects/{id}/screenshots/{sid}/files/{generation}/{rendition}` | Same project-page authorization on every request; ready generation only                                         | `200`, canonical image MIME, nosniff, length; HEAD has no body. Rendition is an enum, not a filesystem path. No raw uploaded-original route. |

The byte rendition enum is `master.webp` or `{thumb,card,detail}@{1,2}x.{webp,avif}`.
Unknown, ungenerated or non-ready renditions return `404`. Do not send these new paths
through legacy public image conversion. Private storage and server routing must prevent
raw-path bypass; verify Apache and nginx before enabling the feature. No archive
extraction or download-count side effects belong in screenshot delivery.

### Response examples and schemas

Public collection (illustrative IDs/URLs; not live resources):

```json
{
  "data": [
    {
      "id": "bd4888e8-e54d-4a6d-aee2-0674febdf399",
      "position": 0,
      "generation": "f7868966-52a4-4908-8b5f-34b2c92c57d7",
      "width": 1080,
      "height": 1920,
      "created_at": "2026-10-21T12:00:00Z",
      "display": "blurred",
      "master_url": "/api/projects/PROJECT_ID/screenshots/SCREENSHOT_ID/files/GENERATION/master.webp",
      "variants": {
        "thumb": {
          "webp_1x": "THUMB_URL",
          "webp_2x": "THUMB_2X_URL",
          "avif_1x": null,
          "avif_2x": null
        },
        "card": {
          "webp_1x": "CARD_URL",
          "webp_2x": "CARD_2X_URL",
          "avif_1x": null,
          "avif_2x": null
        },
        "detail": {
          "webp_1x": "DETAIL_URL",
          "webp_2x": "DETAIL_2X_URL",
          "avif_1x": null,
          "avif_2x": null
        }
      }
    }
  ],
  "max_extra_screenshots": 8,
  "main_fingerprint": "SHA256_OF_CURRENT_MAIN_IMAGE"
}
```

Positions describe the returned view, contiguous from zero; management positions cover
all non-terminal entries, so public positions may change when a pending entry becomes
ready. `width`/`height` are positive normalized-master dimensions. `display` is `normal`
or `blurred`, derived from the project's current NFK state, never from a client field.
`variants` retains the existing `ImageVariantSet` snake_case URL keys; absent AVIF
values are null, not broken links. Production responses contain actual valid URLs,
not the symbolic placeholders used above. Emit the existing main image separately
through its existing response field; it never appears in `data`.

Owner collection additionally returns `revision` (opaque string), `quota` with
`slots_used`, `slots_max`, `input_bytes_used`, `input_bytes_max`, and `history` for
bounded terminal metadata. Non-ready entries carry `state`, `created_at`, `updated_at`,
`input_bytes`, nullable `failure_code`, null master URL and null variants. Owner status
never exposes scanner scores, filesystem paths or original bytes. A `202` upload receipt
is `{"id":"UUID","state":"pending_scan","status_url":"ITEM_STATUS_URL"}`. Its body
records initial acceptance; polling the status URL provides the current state. Use
the owner ETag header, not an old receipt, for subsequent mutations. Use Content-Location
for this 202 response: PHP CGI/FastCGI can rewrite a non-201 response with Location
to a redirect, so do not copy a 201 Location convention here.

Public and owner representations use different ETags, e.g. `"shots-public-v42"` and
`"shots-owner-v42"`; the server, not a client-supplied version, allocates revisions.
Only an owner-view ETag is accepted for mutations. Authorize before comparing ETags;
an inaccessible object must not leak its version. Never return `304` before checking
current project access. NFK/visibility changes must invalidate affected representations.

### Concurrency, retries and errors

Lock the project aggregate (or equivalent serializable transaction) while comparing
the owner revision, reserving a slot and input bytes, allocating order and committing
an item plus durable outbox job. Concurrent requests with the same revision cannot
both commit. The loser gets `412`, refreshes the management collection and retries;
if eight slots or the byte budget are then exhausted it gets `409`. Storage failure
or inability to durably queue must roll back reservation and return `503`, not `202`.
No database transaction remains open while a scanner or encoder runs.

`Idempotency-Key` is a client UUIDv4 retained 24 hours, scoped to actor/project/POST.
Fingerprint the streamed file bytes and declared media type, not the multipart boundary.
A matching replay returns the original `202` receipt body/Content-Location plus current owner ETag;
do not create another row, reserve another slot, enqueue twice or charge the submission
limiter twice. Check a known replay after authorization but before stale If-Match.
Reusing a key with different content returns `409 idempotency_conflict`. Concurrent
same-key requests serialize on a unique key record. Authenticate/rate-bound ingress
and enforce the body bound even when hashing a replay. After key expiry a new POST
is a new upload, with new precondition and quota checks.

Every asynchronous state change advances the revision. Reorder/delete and worker
completion use that same locked aggregate. A worker holds an immutable item/generation
token, and may activate only if it is still current, not deleted and the project still
exists. A stale/deleted job discards its files; retry never resurrects deleted media.
Deletion compacts order transactionally. Repeating DELETE on a retained owned tombstone
returns `204` only with the current owner ETag; missing/foreign IDs still return `404`.

Use the repository's structured error envelope, for example:

```json
{
  "error": {
    "code": 422,
    "type": "image_invalid",
    "message": "Unsupported screenshot.",
    "details": [{ "field": "file", "message": "Use a static JPEG, PNG or WebP image." }]
  }
}
```

| HTTP  | Proposed meaning                                                                                                                                                   |
| ----- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `400` | Malformed multipart/JSON, missing file, duplicate/unknown parts, invalid query or UUID/header syntax.                                                              |
| `401` | Authentication required for management/mutation/reporting.                                                                                                         |
| `403` | Authenticated non-owner on an otherwise accessible project, blocked account/write eligibility, or disallowed reporting. No admin ownership bypass.                 |
| `404` | Missing/inaccessible project, foreign/missing screenshot, non-ready bytes, missing generation/rendition. Same empty delivery response for all denied byte lookups. |
| `409` | `screenshot_limit`, `image_budget_exceeded`, `idempotency_conflict` or existing duplicate-report rule. A stale order revision is 412; malformed membership is 422. |
| `412` | Valid but stale owner ETag; owner-only response supplies current ETag, no automatic replay of reorder.                                                             |
| `413` | Body or file byte limit exceeded, including streaming/middleware rejection. Edge servers may return their own non-JSON 413; the web client must handle it.         |
| `415` | Unsupported request Content-Type, before image validation.                                                                                                         |
| `422` | Decoded image validation/format/dimension/frame error; invalid reorder set (duplicate/foreign IDs) or report context.                                              |
| `428` | Missing required If-Match; missing Idempotency-Key is `400`.                                                                                                       |
| `429` | Proposed upload/reorder/report limit; include Retry-After.                                                                                                         |
| `503` | Admission storage/queue unavailable or global quarantine backpressure. No successful item/reservation is implied.                                                  |

## Required scanning and variant lifecycle

States are `pending_scan -> processing -> ready`, with `processing -> pending_scan`
for retryable scanner outages, and terminal `rejected`, `failed`, `deleted`. Terminal
failure codes include `image_invalid`, `malware_detected`, `content_unsafe`,
`scan_timeout`, `render_budget_exceeded`, `processing_failed`. They are owner-facing
categories, not raw scanner/error output. Only `ready` has delivery URLs.

1. Stream to a non-public, server-named quarantine object after bounded admission.
   Detect format/dimensions with bounded tooling. Scan original bytes for malware
   before full decode; require an actually performed clean result.
2. Decode/normalize under resource limits; require exactly one frame and validated
   output. Call content-safety scanning on the normalized full image, not just a
   small crop. Validate that the result is performed, available, finite and conforms
   to the configured score/label schema. Scanner disabled, skipped, malformed or
   unavailable is **pending**, even if an existing result object says `safe=true`.
   Validate the raw response in a strict required-scan adapter before permissive
   defaults discard evidence of missing fields; an existing boolean alone cannot
   reconstruct whether a valid scan occurred.
3. A valid unsafe verdict is `rejected`; NFK does not override that safety gate.
   Do not automatically clear or change the project's NFK rating. Hold the upload
   rather than publish on a scanner exception or fail-open setting.
4. Stage the WebP master and complete mandatory variants in a private generation.
   Missing optional AVIF support produces null AVIF URLs. A partial encoder run
   never becomes ready. Hash/manifest all files, enforce the set budget, then swap
   the active-generation pointer in a short transaction and advance revision.
5. Erase original quarantine bytes once a generation is activated; retain the input
   hash/count and scan audit. Proposed retries: exponential backoff starting 1 min,
   maximum 1 h interval and 24 h elapsed; after that mark `failed/scan_timeout`, erase
   quarantine and release slot/budget. The owner may submit a new upload. Pending
   placeholders appear only in management, not the public carousel.

Use a durable outbox and idempotent jobs, per-project serial image processing and
bounded global queue/scratch limits. Quarantine admission must stop at a configured
service storage cap; no unlimited disk growth during a long outage. Required capacities,
scanner schema/threshold policy versions and codec capabilities must pass deployment
preflight. Existing disabled/fail-open scanner configuration must not silently disable
this feature's publication requirement. No new URLs to unscanned pixels, including owner previews, admin list thumbnails,
social cards or on-demand conversion caches. Privileged evidence review may display a
performed content-unsafe verdict only under the distinct review policy below.

## Read access, moderation and deletion

| Viewer/project state                     | Ordinary collection and ready bytes                                                                  | Mutations                                                                   |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| Guest/adult; page policy permits project | Ready extras; blur all for NFK                                                                       | No                                                                          |
| Private/unlisted project known by ID     | Exactly the same page policy as today; no new owner-only rule inferred from the word private         | Current eligible owner only                                                 |
| Minor; another owner's NFK project       | `404` when page resolver hides it; no count, URLs or prefetched image bytes                          | No                                                                          |
| Minor; own NFK project                   | Follow existing owner exception; show blurred media                                                  | Only if existing project-edit and account checks permit                     |
| Auto-hidden project                      | Existing page-owner/admin exceptions only; guests/others denied                                      | Existing edit loader excludes auto-hidden projects; no new mutation bypass  |
| Invisible, featured or debug project     | Preserve exact page-resolver order, including featured/debug exceptions and super-admin early return | Existing project-edit lookup remains stricter; no automatic exception       |
| Pending/rejected/deleted image           | Never delivered through ordinary byte route, even to the owner                                       | Eligible owner may cancel a non-terminal item; terminal metadata is bounded |
| Moderation staff                         | General routes still use general policy; separate authorized review route below                      | No owner upload/delete impersonation                                        |

For management/mutations authenticate first, apply existing account-state checks,
then resolve the project with the stated read/edit policy, prove current ownership,
validate request/preconditions and finally reserve resources. An inaccessible project
stays 404; no version/quota details are returned before authorization.

Collection, HEAD and every variant URL reauthorize independently. Use dedicated guarded
storage/delivery, explicit rendition enums, canonical WebP/AVIF MIME and nosniff.
Generation IDs are not bearer tokens. Fail closed on missing database state, stale
ownership/access and replica lag; state-changing deletion/visibility must be consulted
before delivery. Start with private no-store responses and no shared CDN/image cache.
A later caching change needs its own authorization/invalidation review. These new
extra-image guarantees do not claim to retrofit authorization onto legacy public main
screenshot URLs or recall existing cached main images; that remains a separate scope. Browser copies
already received cannot be recalled, so do not claim deletion erases downloaded copies.

DELETE immediately tombstones the image, removes it from reads/order, revokes its
current-generation pointer and cancels jobs. Commit an outbox purge covering master,
every WebP/AVIF rendition, temporary generations and quarantine. File deletion retries
are idempotent; stale jobs cannot promote. Proposed purge SLO is 24 h with alerting for
failures and per-project deletion reconciliation. Raw keys never become web URLs, so
physical cleanup delay must not restore access. Project/account deletion runs the same
lifecycle for every extra; archive replacement/main-image updates do not silently
remove extras. Preserve only approved report evidence, as described next.

### Proposed project report context, not independent screenshot votes

Recommend extending `POST /api/projects/{id}/report` with optional `screenshot_context`:

```json
{
  "category": "sexual_content",
  "note": "Please review this slide.",
  "screenshot_context": {
    "kind": "extra",
    "id": "bd4888e8-e54d-4a6d-aee2-0674febdf399",
    "generation": "f7868966-52a4-4908-8b5f-34b2c92c57d7"
  }
}
```

`kind` is `main` or `extra`. Extra requires a ready owned-by-this-project ID and current
generation; main has neither ID nor generation and requires `sha256`, echoing the collection
`main_fingerprint` (a 64-character lowercase hex SHA256 in real responses). Reject a
changed fingerprint with `409 report_context_changed` rather than silently capturing
a different main image. Never accept a client URL/path as evidence. Atomically validate viewer access
and record the selected generation/hash plus an ordered manifest of **main and all
ready extras**, not only the selected slide. A selected image that changed or disappeared
returns `409 report_context_changed`; missing/foreign reference is `422`. An absent
context keeps legacy reports valid and captures the whole current set. The proposed
main fingerprint is new collection metadata, not a change to an existing main-image
field. Snapshot capture and legacy main replacement need a shared per-project image
lock and atomic completed-file publication; copying a file while the legacy writer
unlinks/replaces it is not a valid evidence snapshot. This is a required internal
integration hook, not an automatic rewrite of existing main-image data or APIs.

Concretely, every main writer (API replacement, archive ingestion/import and maintenance)
and main deletion must adopt the same project image lock before swapping/removing the
completed file. Prepare new bytes privately before that short critical section. Report
capture acquires that lock plus the extra-image aggregate in a fixed order, freezes the
ready-extra generation references with retention holds, and copies the current main
into a private immutable evidence object. Verify the copied bytes' hash against the
locked main fingerprint before committing the report's manifest. Only then release
the lock; later main replacement/deletion cannot delete held evidence. Never scan or
encode while holding this lock. For a context-bearing request, missing/changed main
bytes or a mismatching copy yields `409 report_context_changed` and commits neither
report nor a misleading manifest; remove unreferenced staging files. If a legacy report
without context encounters missing/unreadable media, preserve the report with explicit
`evidence_unavailable` metadata, not invented pixels or a fabricated scan verdict.
Tests must race main replacement/deletion and extra deletion against capture. Until
all main writers participate, atomic evidence is an unmet implementation gate.

Preserve existing categories, verified/trust/rate requirements, minor reporting denial,
own-content denial, whitelist rules and one project trust vote per reporter. Accepted
project reports continue to affect the entire project; deleting a selected slide does
not dismiss a report, clear NFK, undo a ban or change appeal eligibility.

The current lifetime duplicate constraint means a reporter cannot file a fresh report
for a later screenshot. Proposed companion contract, requiring approval of this report
model: `POST /api/projects/{id}/report-contexts` with JSON
`{"report_id":"UUID","screenshot_context":{...}}`. Only the original eligible reporter
may append context for a project they can still read. Return `201` with `context_id`
and `review_required:true`; an exact repeated generation reference returns its existing
receipt without another trust vote. Missing own report is `404`; foreign report is `403`
only after project access; stale screenshot uses the same `409`. Apply report limits.
Append immutable evidence and a pending review task linked to the existing report;
do not mutate past resolution history or recalculate its trust vote. Even a resolved
report's new image context must appear in staff's unresolved evidence queue. This
requires deliberate queue/read-model work, not merely an extra note field.

Proposed `GET /api/moderation/projects/{id}/screenshots` and guarded review-byte routes
require the existing `ROLE_ADMIN` moderation permission, with audit logging. Pixel
review additionally requires an adult staff account; this proposed route must not
become a way for minor accounts to bypass NFK visibility through a staff role. Return
current main/extras, all item states as metadata, each report's immutable image manifest,
selected ID/hash, removal marker and pending context-review tasks. Public and
reporter-facing report APIs do not expose private evidence URLs. Read permission
does not grant ordinary mutation rights.

Here, **sanitized retained evidence** means normalized pixels with a performed clean
malware scan and successful bounded decode. It does not mean a content-safety classifier
considered them suitable for publication. Record `malware_verdict`, `decode_verdict`,
`content_verdict` and scan policy versions separately. Performed `content_verdict:unsafe`
can be shown only by the guarded adult-staff review route after deliberate reveal,
with a warning; it never makes an extra publicly ready. No infected original is served;
retain its hash/verdict metadata only. Skipped/unavailable scans are not proof of safety.

A legacy main image has **no assumed performed scan proof**. Its frozen private copy
enters `legacy_unverified -> pending_scan -> reviewable` only after malware/decode and
required content scanning actually complete. Do not point a new review thumbnail at
the old public URL. Show metadata plus a pending/unavailable placeholder while scanning
is missing or down; retry under the bounded outage policy. Once scans are performed,
label the actual verdict, including content-unsafe, and serve only the normalized
review derivative through the review authorization route. Existing public main-image
behavior is not automatically rewritten by this review-only processing. A missing or
failed derivative stays explicit rather than being mislabeled already scanned.

Extend both the JSON moderation queue and Sonata review UI. Staff must be able to
inspect all current and captured images that meet the stated review-byte safety gate,
including clearly labeled content-unsafe evidence with deliberate staff reveal controls, then resolve the existing project report and acknowledge supplementary
context tasks with audited outcomes. Missing/failed evidence is explicit, never a broken
public link. Freeze scan/hash/generation metadata; do not claim this preserves pixels
unless the referenced normalized object is retained with a deletion hold.

**Proposed retention needing acceptance:** owner terminal status metadata 30 days;
report evidence for the pending case plus 30 days after final resolution; then purge
by audited job. Apply approved account-erasure/evidence policy and a separate bounded
service evidence quota, alerting/admission control and legal-hold process if required.
Do not ship indefinite retention or assume the ordinary 96 MiB active-image budget
covers evidence. If that retention policy is not accepted, implementation remains gated.

## Web carousel and management contract

Keep the main image as slide 1 and the existing fallback when the extra list fails or
is empty. Show only ready extras, preserving server order; a failed individual image
uses a localized placeholder and retry, never an unguarded URL fallback. Main-image
editing remains its current independent control. An owner management panel displays
pending/rejected states, remaining slots/budget, cancel/delete and explicit move-left/
move-right buttons; drag-and-drop is an enhancement, not the only ordering mechanism.

Use a named carousel group with localized previous/next native buttons, an ordinal
status and a single visible slide. No autoplay. Tab/Shift-Tab follow normal focus order;
Enter/Space activate controls without moving focus on every slide change. Support
Left/Right and Home/End only when focus is inside the carousel control region, with
RTL-aware direction; do not intercept typing or page scrolling. Hidden slides must not
retain focusable controls. A polite live region announces user-triggered slide changes.
Use meaningful localized alt labels and hide purely decorative duplicate thumbnails
from assistive technology. Respect reduced motion; avoid animated rotation by default.
These choices follow the [WAI carousel pattern](https://www.w3.org/WAI/ARIA/apg/patterns/carousel/)
and still require real keyboard and screen-reader acceptance, not only ARIA attributes.

Apply NFK blur to the main image, every extra, thumbnail, loading preview, enlarged
view and transition frame before assigning/revealing a source. A current NFK flag change
updates the whole component and owner management previews. Blur is presentation, not
access control; minor visibility is enforced by server routes and no-store headers.
Do not add an unblur/reveal policy for ordinary viewers in this work. Do not preload
or lazy-load any URL returned for an inaccessible project. Report controls bind to the
stable slide ID/generation, never a mutable slide index.

On `412`, refresh and show the current order; ask the owner to retry rather than
silently overwrite another action. Keep main/order focus predictable after deletion,
return focus to the next existing management control, and announce upload progress,
pending scans, quota errors and completion. Local file previews must use bounded,
revoked object URLs; they are not evidence that server scanning has passed.

## Review decisions, rollout and future acceptance

Already accepted: eight extras, main compatibility, required scanning/publication hold,
report/moderator coverage, NFK presentation, and design-only scope.

Decisions still needed before implementation:

1. Accept or adjust the proposed byte/pixel/storage/worker/rate budgets after corpus
   and codec/resource measurements. They are not established production capacities.
2. Accept the project-report-context model and supplementary evidence queue without
   extra trust votes, or request a separately designed screenshot report target.
3. Accept a bounded evidence/status retention policy and quota/erasure handling.

Implementation should begin with an OpenAPI contract update and request/response
examples approved against this proposal, then schema/private storage/outbox, scan and
quota workers, guarded reads, owner API, moderator read models and web controls behind
a disabled flag. Do not enable upload before moderator coverage and outage behavior
exist. Read rollout must not change legacy main-image behavior. Test routing precedence
and raw-storage denial in both server environments before exposing extra images.
Feature rollback disables writes and UI but keeps denial, cleanup and review evidence
available; it must never revert to static public delivery. No database migration,
server deployment, client change or feature enablement is authorized by this design PR.

Future implementation acceptance must cover:

- Owner/non-owner/guest/minor/admin and private/hidden/featured/debug policy matrix;
  direct originals/variants/HEAD, stale cookies and access changes; no metadata leak.
- Concurrent ninth upload, byte-budget races, worker/delete/reorder conflicts,
  stale/missing preconditions, same-key retries, different-body keys and quota release.
- Compressed byte bombs, truncated/forged MIME, huge dimensions, orientation, alpha,
  multi-frame and active formats, metadata stripping, codec/time/disk/memory failures.
- Performed clean versus skipped/unavailable/malformed scans; outage retries, expiry,
  duplicate jobs, worker crashes and partial variant generation without publication.
- Main-image/old-client compatibility, portrait/landscape layout, optional AVIF,
  no-upscale behavior, bounded total outputs and deletion of every generation/file.
- New, duplicate and supplementary report contexts; main/extra replacement and deletion
  during atomic evidence capture; legacy unverified main scan holds; content-unsafe
  versus malware/decode-safe review; evidence holds/expiry, moderator queue/API consistency and no extra trust weights.
- Keyboard-only upload/order/delete/navigation, screen readers, RTL, reduced motion,
  all NFK render surfaces, changed flags mid-session and a failed network/image load.

These are future gates. Formatting this document, static source inspection or existing
unit tests do not demonstrate that this proposed feature works.
