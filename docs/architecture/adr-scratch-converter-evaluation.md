# ADR: Rebuild Scratch to Catrobat conversion (Phase 0 evaluation)

**Date**: 2026-10-20
**Status**: Proposed; implementation requires explicit maintainer approval
**Issue**: [#7419](https://github.com/Catrobat/Catroweb/issues/7419)
**Related**: [Scratch search card placeholder #7420](https://github.com/Catrobat/Catroweb/issues/7420)

## Context and evidence boundary

The archived converter is a specification and test corpus, not a deployable replacement for the
former Scratch converter service. Catroweb's `ScratchController` calls `ScratchManager`, which passes only
Scratch metadata to `ProjectManager::createProjectFromScratch`. That method persists metadata and a
thumbnail; it never creates a Catrobat archive or enters the upload validation/scanning pipeline.
A redirect to its project page is therefore not evidence of conversion. The separate search-card
placeholder work must not imply that this ADR already restores downloads.

This is a source-and-data spike. Ten live public Scratch 3 JSON documents were fetched and
inventoried. Legacy visitors and representative downstream handlers were inspected. Two real share
archives were downloaded and their ZIP/XML structure inspected. **No project was converted in this
spike, no Catroweb extractor acceptance test ran, and no Pocket Code open/run test ran.** These are
explicit acceptance gates for implementation, not successes inferred from opcode lookup. The proposal
can be approved as an approach; compatibility and performance remain unproven.

Pinned source snapshots:

- Catroweb `410d6eef2` (`origin/main` when the spike started).
- [ScratchToCatrobat develop `5433ec7fe86375e37cdcd98971c0a6f70cb9c300`](https://github.com/Catrobat/ScratchToCatrobat/tree/5433ec7fe86375e37cdcd98971c0a6f70cb9c300):
  `scratch/scratch3visitor/blockmapping.py`, category visitors, `scratch/scratch.py`,
  `converter/converter.py`, `converter/test_converter.py`, and `scratch3visitor/test_scratch3.py`.
- [Catroid develop `182f0feb0e8cb632542a5e9abe6a1666d1164d53`](https://github.com/Catrobat/Catroid/tree/182f0feb0e8cb632542a5e9abe6a1666d1164d53)
  defines `Constants.CURRENT_CATROBAT_LANGUAGE_VERSION = 1.14`.

## Proposed decisions

### Placement and language

Recommend **a PHP converter in a separate, restricted worker container**, with PHP orchestration in
Catroweb, as the working choice for implementation ease and measured front-end efficiency. PHP, Python, Go, Rust or another suitable language may be used; sharing Catroweb's language is
an implementation convenience, not a product constraint. The bounded comparison below informs this
choice but does not establish the fastest complete converter.

The PHP orchestration consumer owns fetching, authorization, job state, deduplication, persistence and
upload policy. The isolated conversion process receives a bounded immutable input bundle and returns
an archive plus structured diagnostics. It has no public ingress, database credentials or outbound
network. Infrastructure access belongs to the orchestration process, not the conversion sandbox.
This boundary is equally possible with Go, Rust or Python; isolation does not require any particular
language.

| Option                 | Correctness and implementation effort (assessment, not a port-cost measurement)                                                                                                                                                                                                                            | Measured evidence / remaining tradeoff                                                                                                                                                                                   |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| PHP worker             | Existing PHPUnit, ingestion integration and PHP XML tooling reduce the number of test/toolchain boundaries. Dynamic Scratch values still need an explicit typed model and complete semantic tests.                                                                                                         | Fastest repeated processing in this narrow comparison; more memory and startup cost than Go. Working recommendation, not an overall performance winner.                                                                  |
| Go worker              | Explicit structs/interfaces, standard JSON/XML/ZIP support and Go tests suit a standalone converter. Every mapping and its Java-backed output model still needs porting; cross-language ingestion fixtures and another build stage are needed.                                                             | Lower measured RSS and process-start latency; slower generic-tree repeated parsing here. Credible alternative if per-job startup, measured full-conversion CPU/RSS or deployment simplicity outweigh integration effort. |
| Python 3 worker        | Familiar visitor syntax makes transcription straightforward, but Java classes cannot be imported unchanged. A separate mapping test/runtime/dependency environment remains necessary.                                                                                                                      | Front-end times lie between PHP and Go v2 for the repeated workload; no measured reuse or fidelity advantage over a rewrite in another language.                                                                         |
| Rust worker            | Enums can express the intermediate model and ownership can manage memory without a GC. Mutable block graphs need deliberate ID/index/ownership design; this may add model-design work, but no porting experiment quantified it. Cargo tests and serialization crates provide a viable implementation path. | Compiler/cargo unavailable locally; no timing/RSS/build measurement and no inference that Rust is slow or unsuitable. A release build and the same workload are needed before any performance ranking involving Rust.    |
| Restore Jython service | Some old code could run with its Java model, but obsolete runtime/dependencies and output compatibility remain unresolved.                                                                                                                                                                                 | Reject as production architecture; not benchmarked.                                                                                                                                                                      |

The legacy converter imports Java Catroid classes through Jython; **Python 3 cannot import it
unchanged**. All four modern options need a semantic port and a replacement for the Java-backed output
model. Similar syntax alone does not establish lower total work. The hard acceptance work is Scratch
coercion/list indexing, variables and identity, event ordering, clones, unsupported formulas, graphic
and coordinate semantics, and current Catroid XML references. No front-end microbenchmark resolves
those requirements. Port into an explicit intermediate model and an allowlisted XML writer; never
execute Scratch JavaScript or deserialize untrusted server input through XStream.

PHP provides [JSON decoding](https://www.php.net/manual/en/function.json-decode.php) and
[XMLWriter](https://www.php.net/manual/en/book.xmlwriter.php). Go provides
[JSON](https://pkg.go.dev/encoding/json/v2), [XML](https://pkg.go.dev/encoding/xml) and
[ZIP](https://pkg.go.dev/archive/zip) packages; the standalone benchmark executable measured 3.6 MiB.
Rust's [ownership model](https://doc.rust-lang.org/book/ch04-01-what-is-ownership.html),
[enums](https://doc.rust-lang.org/book/ch06-00-enums.html),
[Serde](https://serde.rs/) and [Cargo tests](https://doc.rust-lang.org/cargo/commands/cargo-test.html)
provide concrete tools for a port, not measured proof of its implementation cost. These are official
project documentation sources; the effort judgments in the table are implementation assessments.

For this task, PHP's familiar ingestion/test path and acceptable measured front-end cost make it the
simplest initial correctness-focused implementation under this assessment. No complete mapping/writer
was ported in any language, so this is an explicit inference, not a measured development-time win.
Go remains attractive: its v2 run used about 17.6 MiB less warm peak RSS and roughly 42.6 ms less
fresh-process time than PHP in these samples. A per-job sandbox would pay startup cost, so that
advantage matters; a long-lived process could amortize it. Neither a roughly 40 ms front-end delta
nor this warm loop establishes total conversion latency or production throughput. Profile real
mapping, XML/ZIP writing, native assets and ingestion at the same acceptance level before deciding
whether a different worker language is worth a rewrite. The proposed 1 GiB limit is not a measured
requirement and cannot by itself establish that either implementation fits a real conversion.

`docker/nsfw-scanner` demonstrates an isolated service pattern; its existing Python runtime is not a
reason to add Python to the converter. All options reuse pinned native raster/audio tools under the
same subprocess limits, rather than reimplementing SVG rendering or codecs in PHP, Go, Rust or Python.
A Go/Rust converter does not eliminate those native dependencies or their sandbox requirements.
Implementation and maintenance remain subject to maintainer review. Language selection and the
separately approved scanning policy do not approve the revised ADR or start implementation.

### Bounded front-end comparison (2026-10-20)

This benchmark is scratch-only evaluation code, not a production parser or converter. No application
code, dependencies, benchmark scripts or generated inventories are added by this PR.

Environment: Apple M5, arm64 macOS, 32 GiB RAM; PHP CLI 8.5.11, Python 3.15.0, Go 1.27.3.
PHP ran with `-n`: Xdebug absent, CLI OPcache off and JIT disabled. The Python and Go versions are
locally available versions, not a requirement for eventual production. `rustc` and `cargo` were not
on PATH; no system compiler was installed. PHP/Python needed no setup. Go used only standard libraries,
`GOTOOLCHAIN=local`, `GOPROXY=off` and a scratch-local build cache.

Inputs: the same ten retained Scratch snapshots listed below, re-encoded as compact UTF-8 JSON after
removing environment-identifying `meta` and retaining `targets`, `monitors` and `extensions`. No opcode,
asset, field/input or target data was removed. All implementations read the identical 2,458,223 bytes.
Original snapshot hashes were rechecked. The SHA-256 of the ordered lines `ScratchID:normalizedSHA256`
(with a final newline per line) is
`acc59aa6401d3621fe68ab7a23fd9c4e49d9e0f53a6078e729ea81e9b3bcf266`.

Each implementation parses the full generic JSON tree, validates the target list, target names and
block dictionaries, checks opcode strings and nullable `next`/`parent` references against that
target's block IDs, skips compact array reporters, counts/sorts opcodes, and emits the same escaped
XML inventory. This is **an inventory, not Catrobat XML**, no assets are decoded and no mappings run.
Validation is deliberately partial: it is not the production graph/schema/security validator.
All outputs were byte-identical: 24,742 bytes, 10,517 block objects and 115 distinct opcodes, SHA-256
`ed4f58c24de1356fc310857738659d0ae1af24b5b1a4f9b577fb80c8e54c499d`.
Eight synthetic inputs across the four measured implementations produced all 32 expected accept/reject
results, including wrong container/opcode/reference types, dangling references and XML escaping
round-trips. This does not establish identical handling of every malformed JSON or Scratch input.

Method: nine samples per implementation and mode, all processes serial. Fresh-process measurements
include launch, input reads, one corpus pass and output; “cold” means a new process, **not** flushed
filesystem caches or a machine reboot. Warm measurements preload bytes, perform one unmeasured pass,
then time 25 parse/validate/inventory passes in-process; the table reports milliseconds per whole
10-project corpus, not per project. PHP/Python/Go-v1 order rotated between samples. OS peak RSS was
collected with the operating system resource-usage reporter and includes runtime baseline; it is not allocated model size.
Nine-sample medians and ranges are descriptive, not confidence intervals. Startup ranges show noise;
no CPU pinning or machine-wide quiet period was enforced.

| Implementation                   | Fresh process median ms (range) | Warm corpus median ms (range) | Median peak RSS MiB, fresh / warm | Maximum warm peak RSS MiB |
| -------------------------------- | ------------------------------: | ----------------------------: | --------------------------------: | ------------------------: |
| PHP `json_decode`                |            82.68 (69.97–380.97) |           19.12 (17.72–27.54) |                     40.98 / 41.17 |                     41.28 |
| Python `json.loads`              |            88.08 (64.25–103.49) |           23.18 (21.33–31.75) |                     32.44 / 33.86 |                     34.06 |
| Go `encoding/json`               |            58.39 (39.09–397.06) |           51.45 (45.34–55.02) |                     15.95 / 24.14 |                     27.06 |
| Go `encoding/json/v2`, follow-up |            40.12 (35.57–267.13) |           28.13 (26.39–30.42) |                     15.33 / 23.61 |                     25.45 |
| Rust                             |                     Unavailable |                   Unavailable |                        Unmeasured |                Unmeasured |

The Go-v2 follow-up changed only the JSON import; it ran later, separately rather than in the rotated
baseline, so its relative timings have a session/order confound. It compiled on local Go 1.27.3 with
**empty `GOEXPERIMENT` and no experimental flag**. This observation is version-specific; do not assume
v2 availability/stability in older toolchains. The current official
[v1 documentation](https://pkg.go.dev/encoding/json#hdr-Security_Considerations) recommends v2 for new
usage; the [v2 documentation](https://pkg.go.dev/encoding/json/v2) describes its different parsing
semantics. Same corpus output does not imply interchangeable malformed-input behavior.

Compilation is separate from runtime: first Go build with an empty scratch build cache took 25.90 s,
with 269.13 MiB maximum RSS; a source rebuild with the now-warm compiler cache took 1.08 s and
82.59 MiB. The v2 follow-up build took 0.82 s and 81.20 MiB with that cache. These single setup samples
are not benchmark medians or dependency-install comparisons. All builds and timed loops took the
shared resource gate, ran with memory supervision, and released the gate. No Docker resources were
created. Raw inputs, evaluation scripts, synthetic cases, manifests and timing reports are retained
separately for review; they are not committed by this ADR-only PR. Reproduction requires the same
inputs, workload, versions and measurement method described above. First-build timing requires an
empty compiler cache.

No language has been proved best for the complete converter. The generic-tree representation,
standard decoder implementations, runtime settings and macOS/arm64 host affect these results; typed
or streaming parsers and production Linux containers may change them. Mapping fidelity, full XML
serialization, ZIP compression, asset rendering/audio conversion, network, scanning and publication
are unmeasured. Native asset work may dominate, but this spike does not quantify that either. Keep
current serializer/golden/Pocket Code acceptance and per-project CPU/RSS measurements as gates before
claiming efficient usable conversion in any language.

### Asynchronous requests and publication

Propose a PHP Symfony Messenger consumer with a Doctrine transport and one consumer initially.
Messenger and its Doctrine transport are **not installed or configured today**; their introduction
needs a separate reviewed PR.
Use the existing database rather than adding Redis solely for conversion. A request enqueues or
reuses a durable job and returns a status reference; the web page and app poll queued, fetching,
converting, validating, ready, ready-with-warnings or failed. This is a conceptual contract, not a
new API in this PR. Specify the endpoint and response in OpenAPI first, then regenerate only semantic
changes in the later API PR. Adapt the existing GET redirect/POST-created behavior explicitly; a job ID
must never be mistaken for a project ID.

Only a validated, scanned artifact gets a downloadable Project record. Extract a shared ingestion
service from the upload path rather than calling an HTTP controller or duplicating policy. Preserve
Scratch-author ownership, attribution, malware scanning, extracted-file sanitization, visibility rules,
persistence/events and cleanup. Source inspection found `ContentSafetyScanner` called from avatar
validation, not from the project upload path, despite the broader scanning claim in the handoff.
Project NSFW checks therefore need an explicit integration step for converted images/screenshots;
calling the current upload method alone does not supply them. The existing content scanner marks
unavailable/skipped results safe. **Required image scanning and holding publication until
required scans pass are approved policy.** For converted projects, unsafe images are rejected;
unavailable or skipped required scans leave publication on hold with retryable status. Do not infer
approval of the worker architecture from this separately approved scanning policy.
Conversion warnings remain visible on the download/status page. Atomically publish the archive and
ready status; retries use an idempotency key and cannot create duplicate Projects or notifications.
A worker crash releases its lease; bounded transient retries end in a clear failed state. Recheck source
revision and visibility before publication so an older job cannot replace a newer conversion.

### Supported subset and degradation for v1

Target events (flag, sprite/stage click, broadcast/receive), control (wait, loops, conditionals, stop,
clones), basic motion, costumes/backdrops, show/hide/size/layering, speech, sound playback/volume,
arithmetic/boolean/string operators, variables/lists, mouse/touch, collision/distance/timer, and basic
pen. Add the two observed missing legacy visitors: stage click and delete-all-list. Menus and compact
variable/list reporters are inputs, not separate executable bricks. Preserve variable/list scope,
identity and initial contents even when no opcode block represents them.

The supported label is earned per opcode **and input variant** by ported tests and current Pocket Code
verification. The table below describes legacy source wiring; it is not a v1 compatibility promise.
Initially exclude cloud synchronization, external hardware/extensions, video sensing, music synthesis,
sound effects, custom procedure calls/definitions/arguments, non-brightness/ghost graphic effects and
unverified sensor/property variants. Custom procedures occur in the corpus: projects that depend on
them will be explicitly degraded until a later mapping PR implements and tests procedure semantics,
including warp. Do not advertise all ten projects as playable v1 examples merely because a ZIP opens.
Keyboard emulation and coordinates/rotation centers need product decisions and behavior tests.

Preserve the old converter's visible `NoteBrick` behavior for unsupported stack blocks. Include opcode,
sprite and source block identifier in a structured warning and a safe, escaped note. For unsupported
reporters, place a warning note in the owning script and substitute a documented typed neutral value
(0, empty string or false); never emit a NoteBrick into a formula position. An unsupported hat keeps
its script disabled with a diagnostic rather than activating it unconditionally. Preserve other scripts
and supported children where meaningful. Invalid block graphs, missing indispensable assets and limit
violations fail the job, rather than publishing corrupt archives. Offer degraded downloads with a clear
warning; “ready with warnings” must not mean behavior matches Scratch.

### Assets, sandbox and proposed budgets

All ten JSON documents contain SVG and WAV; the corpus also contains PNG, JPEG and MP3. Fetch assets
from Scratch's fixed asset host, validate identifiers, verify content hashes and MIME signatures, and
reject redirects outside the allowlist. Obtain `project_token` from project metadata, then fetch JSON
from `projects.scratch.mit.edu/{id}?token=…`; a metadata-only fetch does not obtain code. Never persist
or log tokens in URLs/diagnostics. External references and scripts in SVG are disabled. Use a pinned
rasterizer with its own subprocess limits; normalize output to PNG, honoring Scratch bitmapResolution,
rotation center and stage coordinates. Reuse existing Imagick thumbnail handling in PHP. Keep supported
WAV/MP3 when compatible; convert unsupported audio with a pinned codec in the worker. Test duration,
sampling, scaling, text/font fallback, transparency and rotation-center fidelity.

Initial **proposed, unmeasured** limits: one job per worker, 1 GiB container memory, 120-second wall
limit, 60-second CPU budget, 16 MiB project JSON, 100,000 block objects, 2,000 assets, 32 MiB per asset,
128 MiB total downloaded inputs, 256 MiB total decoded raster allocation and 512 MiB scratch disk.
Enforce dimensions/pixel allocation before raster decoding and cap audio decoded duration/size.
Validate reference graphs iteratively, cap nesting and expansion, and reject cycles where invalid.
Use read-only root filesystem, non-root UID, dropped capabilities, no network, bounded temporary
volume, fixed subprocess arguments and no shell interpolation. Validate returned paths, ZIP sizes,
entry counts and traversal before passing the archive to shared ingestion; do not trust worker output.

These limits are not performance results or promises that the 803-asset house project fits memory or
time. Measure each corpus project and native-codec peak RSS before choosing production budgets. Record
timeouts as unsupported/resource failures, queue backpressure and per-requester quotas before capacity
is exceeded. Admission and asset downloads also need timeouts/size caps, not only conversion itself.
The evaluation compilation and timed comparisons ran serially under a shared resource gate with
memory supervision. Later heavy runs must use equivalent resource controls.
No Docker build, package installation or application test suite ran in this documentation spike.

### Output format and acceptance

Write language **1.14**, pinned to the inspected Catroid commit. Before building, record the actual
Pocket Code release/build used for acceptance and check that its language support matches. A develop
constant alone does not establish compatibility with the installed stable app. If that build supports
an older language, explicitly revise the target rather than merely editing the version header.

Catroid's `io/XstreamSerializer.java` configures annotation-based Project/Scene/Sprite/XmlHeader
serialization, field sorting, and dedicated brick, script, formula and user-data converters.
`BackwardCompatibleCatrobatLanguageXStream` reads older formats. Therefore XML element names,
formula trees, references, collections, header values and asset paths must follow current serializer
fixtures; changing a header in old XML is insufficient. A modern archive inspected below contains
scene directories with image/sound assets and screenshots, plus root `code.xml`.

For each mapping PR, port meaningful legacy cases, add unsupported-input cases and compare normalized
XML to golden fixtures exported by the chosen current Pocket Code build. Keep PHP integration tests
for fetch/job/API and ingestion; test PHP mappings and the writer with PHPUnit in the worker module.
A golden end-to-end fixture must pass Catroweb's extractor/sanitizer and then import through the current Catroid serializer.
These verify different properties: extractor success alone is not an app execution test.

Before release, convert all ten snapshots, download each generated archive, open in the chosen current
Pocket Code build and run a written scenario: start/events, touch/key behavior, costumes/centers,
broadcast ordering, clone cleanup, variables/list values, sound and stage/pen state as applicable.
Record build/version, device/emulator, input/output hashes, warnings, observed behavior and differences
from Scratch; preserve failures. Require import success for every published artifact and explicit
maintainer acceptance of degraded behavior. No full-fidelity success threshold can be claimed from this
static corpus. No Android device or emulator was attached (`adb devices` empty) during this spike.

### Licensing, attribution and cache invalidation

Catroweb is AGPL; the legacy converter's source headers specify AGPL-3.0-or-later plus an additional
section-7 exception reference. Preserve upstream copyright/attribution and inspect that exception and
third-party dependency notices before porting code or tests. Do not assume every bundled font, codec
or asset shares the code license. Keep source availability and dependency notices in the worker
release. Preserve the Scratch project URL, creator, credits, instructions and source revision in the
converted project's notes/credits; verify Scratch content-license and redistribution obligations
before checking third-party project assets into permanent test fixtures. This ADR includes measured
metadata/hashes, not copied project JSON or artwork.

Cache by Scratch ID, metadata modified timestamp, downloaded JSON SHA-256, converter build, supported
subset version, asset-normalizer version and output-language version. Revalidate metadata with a short
TTL before serving cached conversions; deduplicate in-flight jobs by revision. On stale metadata TTL,
refresh before declaring a cache hit. An implementation may fetch JSON to detect content changes not
represented by timestamps; a timestamp-only match must not be documented as a content-hash check.
Changed inputs or converter versions trigger a new job, not reuse of metadata placeholders. Removed,
private or inaccessible projects cannot bypass current access checks through stale cache entries.
Invalidate public cached downloads on access loss; content/asset retention is bounded and policy-driven.
Failures have a short negative TTL, bounded retries, and a manual re-convert command later.

## Ten-project spike results

Selection: projects were candidates from the live Scratch explore API, `mode=popular`, on 2026-10-20.
We chose ten public projects with animation/art, games, procedures, lists and pen across increasing
block-object counts. They are discoverable in a popular listing, not a statistically representative
sample or the globally ten most popular projects. Love counts are recorded to make that distinction
checkable. Every snapshot had Scratch 3 `targets`. Objects in `targets[].blocks` with an `opcode` were
counted, including shadow/menu and detached blocks; compact array-encoded variable/list reporters are
excluded from opcode counts. These are static presence counts, not runtime execution counts.

| Project                                                                                 | Loves | Blocks / distinct opcodes | Categories                                                                               | Assets / formats         | Missing legacy visitors  | Converts / Pocket Code |
| --------------------------------------------------------------------------------------- | ----: | ------------------------: | ---------------------------------------------------------------------------------------- | ------------------------ | ------------------------ | ---------------------- |
| [Iguana Eye](https://scratch.mit.edu/projects/608045524/)                               |   103 |                   44 / 21 | control, data, event, looks, motion, operator, sensing                                   | 4 / svg, wav             | None observed            | Not run / not run      |
| [Clay Animation: A Dog](https://scratch.mit.edu/projects/618719280/)                    |  1105 |                   66 / 18 | control, event, looks, sound                                                             | 111 / svg, wav, jpg, png | None observed            | Not run / not run      |
| [crab](https://scratch.mit.edu/projects/842420335/)                                     |    22 |                  105 / 32 | control, data, event, looks, motion, operator, sensing, sound                            | 20 / png, svg, wav       | None observed            | Not run / not run      |
| [How to Draw Bubbles](https://scratch.mit.edu/projects/402432667/)                      |   356 |                  234 / 47 | control, data, event, looks, motion, operator, sensing, sound                            | 52 / svg, wav, mp3, png  | `event_whenstageclicked` | Not run / not run      |
| [Ocean Twilight - Animation](https://scratch.mit.edu/projects/805648420/)               |  1374 |                  315 / 58 | argument, control, data, event, looks, motion, operator, procedures, sensing, sound      | 16 / svg, wav, mp3       | None observed            | Not run / not run      |
| [Floating axolotl game](https://scratch.mit.edu/projects/574640739/)                    |  3481 |                  661 / 60 | control, data, event, looks, motion, operator, procedures, sensing, sound                | 125 / svg, mp3, wav, png | None observed            | Not run / not run      |
| [Crazy Pinball 瘋狂彈珠台](https://scratch.mit.edu/projects/720243438/)                 |  1843 |                  966 / 70 | argument, control, data, event, looks, motion, operator, procedures, sensing, sound      | 87 / svg, wav            | None observed            | Not run / not run      |
| [Asteroid Defender](https://scratch.mit.edu/projects/809533204/)                        |  2347 |                 1424 / 79 | argument, control, data, event, looks, motion, operator, procedures, sensing, sound      | 144 / svg, wav, png, mp3 | None observed            | Not run / not run      |
| [Laser — v1.4.2](https://scratch.mit.edu/projects/354682562/)                           |   748 |                 1936 / 93 | argument, control, data, event, looks, motion, operator, pen, procedures, sensing, sound | 188 / png, wav, svg      | `data_deletealloflist`   | Not run / not run      |
| [Interactive House: The Wizard of Scratch](https://scratch.mit.edu/projects/414820637/) |   360 |                 4766 / 60 | control, data, event, looks, motion, operator, procedures, sensing, sound                | 803 / svg, png, wav      | `event_whenstageclicked` | Not run / not run      |

Totals: **10,517 block objects, 115 distinct opcodes, 113 present in the legacy visitor table and two
missing**. Presence is not semantic coverage. In particular, sound effects have visitors that emit
notes, `sound_cleareffects` produces an unmapped intermediate opcode, `data_itemnumoflist` does too,
and procedure `warp` is explicitly discarded. Legacy broadcast handlers lowercase message names;
case/identity behavior needs comparison, not a blind port. The appendix conservatively marks special
handlers and unverified sensor behavior as partial even where a visitor exists. No success rate for
actual conversion can be derived from these totals.

### Snapshot identities

These hashes cover the downloaded project JSON bytes, without metadata/tokens. Upstream projects may
change or disappear; a rerun can yield a different hash. Before implementation, archive a licensed,
pinned fixture bundle for deterministic tests; live URLs alone are not golden fixtures.

| Scratch ID | Author          | Metadata modified (UTC)  | JSON SHA-256                                                       |
| ---------- | --------------- | ------------------------ | ------------------------------------------------------------------ |
| 608045524  | IguanaLover_PT  | 2021-11-29T11:30:04.000Z | `f8f8ac082265e4107392cd9a4a96e636d5b4122299c848982180fa2b226b5962` |
| 618719280  | Chumie          | 2026-09-22T05:55:17.000Z | `5807f209380c8ab7ea00ce89da9583903905a5cbccd5e58f3005bbd539ddb070` |
| 842420335  | ranie300        | 2023-08-21T17:24:13.000Z | `ee044638e350df04317de0668802ddd05061e75156a9565fe041246220925906` |
| 402432667  | -lemonblossom-  | 2020-06-25T15:21:46.000Z | `6b9d216f81d0f84ebfc9cfe366e8b5e9fd26ffd497b2679960765dcb13db16b8` |
| 805648420  | wilhelm43       | 2025-04-26T09:08:54.000Z | `23a13d14d605f473bd526c086987609fecd8d89fee26f93e171b487d337d28c8` |
| 574640739  | Programmer-CPR- | 2026-07-22T12:46:33.000Z | `c45058b24c59d8462f7104bea71074619be12a4a9918c713a295c62eef3992a1` |
| 720243438  | LCGcheng        | 2023-06-22T04:32:32.000Z | `4b614b8b1e01863228931618e2b0acb2d5b5fc7dad6e406e68fe254a2e0983b1` |
| 809533204  | -Scruffles-     | 2024-08-31T19:19:50.000Z | `cf8c8302362ef0cb182fe6372212187b191af4e4fe5f3dfac41cbde9a19bf8db` |
| 354682562  | betamaj         | 2026-07-17T15:30:03.000Z | `231570e2106bcba771fc079ab265b0c2e427107c80a7c68e497b6381adad3112` |
| 414820637  | Chumie          | 2025-01-01T21:53:02.000Z | `5a5cfc196e89a5ee7954ca2f4ad72553a932a48b154cc7cd9ef6d88f130850af` |

### Reproducing the source assessment

1. Fetch public metadata from `https://api.scratch.mit.edu/projects/{id}`; read `project_token` in memory.
2. Fetch `https://projects.scratch.mit.edu/{id}?token={project_token}`; record SHA-256 before JSON parsing.
   Never include the token in reports. All ten project fetches succeeded using this flow.
3. For every target, collect `opcode` from dictionary-valued entries in `blocks`, counting occurrences.
   Group by prefix for the category inventory. Inspect variables/lists and extensions separately;
   presence in these tables does not establish monitor or compact-reporter support.
4. Compare the distinct opcode set with `visitormap` at the pinned legacy commit, then follow each
   category visitor's Scratch 2 intermediate opcode into `converter/converter.py` mappings/handlers.
   The appendix records the resulting source assessment; partial rows require further behavior tests.
5. Do not execute the converter under system Python 3. Its runner requires Jython and bundled Java
   classes; this machine has Java 27 and no `jython` executable. No legacy runtime was installed and
   no old-converter conversion was attempted. The legacy token-aware fetch path was not established
   by this source lookup and must not be assumed to work today.

### Real share archives inspected

Two public downloads were fetched on 2026-10-20 and opened locally with ZIP/XML parsers. Initial API
requests with the default Python user agent returned HTTP 403; retry with an explicit browser-like
user agent succeeded. This observation is not a proposed production fetch strategy.

| Archive                                                                                                        | Language | ZIP evidence                                                      | Archive SHA-256                                                    |
| -------------------------------------------------------------------------------------------------------------- | -------- | ----------------------------------------------------------------- | ------------------------------------------------------------------ |
| [Nyancat 1.0](https://share.catrobat.org/api/projects/d194ca62-ef89-43e4-b97a-5a7ad433eba8/catrobat)           | 0.91     | 1,586,192 bytes; 19 entries; XML root `program`; 0 scene elements | `14f8252a98528a10328b6971e2ab996c6f6bca199a8a56c4188d7e10d615a512` |
| [Bloons 1 (New Update)](https://share.catrobat.org/api/projects/b7390980-55fb-4404-b974-e43a5f3f8346/catrobat) | 1.14     | 1,154,277 bytes; 23 entries; XML root `program`; 2 scene elements | `336207d5c87268fa9c635285cf884e2ccb874f099d2d85014dfa3b5918e8e824` |

The 0.91 archive has top-level `images/` assets; the 1.14 archive has `Scene/` and `track/` directories,
images/sounds, `.nomedia`, screenshots, and root `code.xml`. This demonstrates why old flat layout
must not be treated as a current serializer fixture. Both are existing Catrobat projects, **not outputs
of this spike**, and neither was opened in Pocket Code here. Asset presence and XML parseability do
not prove serializer or runtime acceptance. Downloading public fixtures does not change server
configuration; normal download counters may increase.

## Maintainer decisions and implementation gate

**Approved policy:** Required image scanning and holding publication until required scans pass.
This policy is settled for the converter proposal; it has not been implemented by this documentation
PR. Implementation and maintenance remain subject to maintainer review.

The following decisions require explicit maintainer approval before converter implementation:

1. The working language choice prioritizes implementation ease and efficient operation; PHP is
   recommended after the bounded four-language assessment above, without a PHP-only constraint.
   The isolated-worker/Doctrine-backed asynchronous design and the complete revised ADR remain
   **proposed** and require approval before implementation.
2. Initial supported subset, explicit degraded downloads with note bricks, and custom procedures
   deferred despite their use in the corpus; decide keyboard behavior and how prominently to warn.
3. Language 1.14 conditional on the selected Pocket Code acceptance build, and the mandatory
   extractor/serializer/open/run gates before claiming usable conversion.
4. The proposed limits as starting hypotheses, with measured per-project resource results required
   before release; source/dependency licensing and fixture retention review before importing tests.

This PR changes only this ADR. It adds no endpoint, queue, worker, scanner behavior or deployment.
Phase 0 delivers evidence and a proposal, **not a converter feasibility pass**. After approval, use
small issue-first PRs: bounded fetching, model/parser, current XML writer and ingestion golden test,
category mappings, assets, then status/integration/ops. Every PR needs green applicable CI and a fresh
independent review; no merge without separate maintainer authorization. The independent review
and CI state belong in the PR/report, not invented as results in this document.

## Appendix: observed opcode coverage

`✓` means a legacy visitor and a direct mapping/formula/input representation were identified in
source. `partial` means semantic limitations, notes, special downstream handlers or unverified helper
behavior. `✗` means absent from the legacy Scratch 3 visitor table. **None means runtime-tested**.
The target column records legacy classes/formula representations, not a guarantee that those exact
serialized classes remain valid in current Catroid. Counts include shadow/menu blocks. The table has
one row for each of the 115 distinct observed opcodes.

| Scratch opcode                    | Count | Legacy visitor                       | Catrobat target / caveat                                                                                           | Source coverage |
| --------------------------------- | ----: | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------ | --------------- |
| `argument_reporter_string_number` |    33 | `control.visitArgumentIntOrString`   | Special handler/input; verify downstream (getParam); parameter scope needs verification                            | partial         |
| `control_create_clone_of`         |    90 | `control.visitCreate_clone_of`       | CloneBrick                                                                                                         | ✓               |
| `control_create_clone_of_menu`    |    90 | `control.visitCreate_clone_of_menu`  | Input/menu value, no standalone brick                                                                              | ✓               |
| `control_delete_this_clone`       |   118 | `control.visitDelete_this_clone`     | DeleteThisCloneBrick                                                                                               | ✓               |
| `control_forever`                 |   300 | `control.visitForever`               | ForeverBrick                                                                                                       | ✓               |
| `control_if`                      |   447 | `control.visitIf`                    | IfThenLogicBeginBrick/end                                                                                          | ✓               |
| `control_if_else`                 |   215 | `control.visitIf_else`               | IfLogicBeginBrick/else/end                                                                                         | ✓               |
| `control_repeat`                  |   290 | `control.visitRepeat`                | RepeatBrick                                                                                                        | ✓               |
| `control_repeat_until`            |    57 | `control.visitRepeat_until`          | RepeatUntilBrick                                                                                                   | ✓               |
| `control_start_as_clone`          |    80 | `control.visitStart_as_clone`        | WhenClonedScript                                                                                                   | ✓               |
| `control_stop`                    |    48 | `control.visitStop`                  | Special handler/input; verify downstream (stopScripts)                                                             | partial         |
| `control_wait`                    |   782 | `control.visitWait`                  | WaitBrick                                                                                                          | ✓               |
| `control_wait_until`              |    12 | `control.visitWait_until`            | Special handler/input; verify downstream (doWaitUntil)                                                             | partial         |
| `data_addtolist`                  |     6 | `data.visitAddtolist`                | AddItemToUserListBrick                                                                                             | ✓               |
| `data_changevariableby`           |   123 | `data.visitChangevariableby`         | ChangeVariableBrick                                                                                                | ✓               |
| `data_deletealloflist`            |     6 | `—`                                  | No Scratch 3 visitor                                                                                               | ✗               |
| `data_hidevariable`               |     1 | `data.visitHidevariable`             | Special handler/input; verify downstream (hideVariable:)                                                           | partial         |
| `data_itemnumoflist`              |     3 | `data.visitItemnumoflist`            | Special handler/input; verify downstream (itemNum:ofList:); itemNum:ofList: absent in downstream map               | partial         |
| `data_itemoflist`                 |    13 | `data.visitItemoflist`               | Special handler/input; verify downstream (getLine:ofList:)                                                         | partial         |
| `data_setvariableto`              |   374 | `data.visitSetvariableto`            | SetVariableBrick                                                                                                   | ✓               |
| `data_showvariable`               |     2 | `data.visitShowvariable`             | Special handler/input; verify downstream (showVariable:)                                                           | partial         |
| `event_broadcast`                 |   174 | `event.visitBroadcast`               | BroadcastBrick                                                                                                     | ✓               |
| `event_broadcastandwait`          |     2 | `event.visitBroadcastandwait`        | BroadcastWaitBrick                                                                                                 | ✓               |
| `event_whenbackdropswitchesto`    |    50 | `event.visitWhenbackdropswitchesto`  | Special handler/input; verify downstream (whenSceneStarts); background-message workaround                          | partial         |
| `event_whenbroadcastreceived`     |   431 | `event.visitWhenbroadcastreceived`   | BroadcastScript                                                                                                    | ✓               |
| `event_whenflagclicked`           |   287 | `event.visitWhenflagclicked`         | StartScript                                                                                                        | ✓               |
| `event_whengreaterthan`           |     2 | `event.visitWhengreaterthan`         | Special handler/input; verify downstream (whenSensorGreaterThan); sensor threshold semantics                       | partial         |
| `event_whenkeypressed`            |     8 | `event.visitWhenkeypressed`          | BroadcastScript + key workaround; keyboard/touch emulation                                                         | partial         |
| `event_whenstageclicked`          |     2 | `—`                                  | No Scratch 3 visitor                                                                                               | ✗               |
| `event_whenthisspriteclicked`     |   141 | `event.visitWhenthisspriteclicked`   | WhenScript                                                                                                         | ✓               |
| `looks_backdropnumbername`        |    95 | `looks.visitBackdropnumbername`      | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `looks_backdrops`                 |    42 | `looks.visitBackdrops`               | Input/menu value, no standalone brick                                                                              | ✓               |
| `looks_changeeffectby`            |   121 | `looks.visitChangeeffectby`          | Special handler/input; verify downstream (changeGraphicEffect:by:); only supported effect subset                   | partial         |
| `looks_changesizeby`              |    29 | `looks.visitChangesizeby`            | ChangeSizeByNBrick                                                                                                 | ✓               |
| `looks_cleargraphiceffects`       |    58 | `looks.visitCleargraphiceffects`     | ClearGraphicEffectBrick                                                                                            | ✓               |
| `looks_costume`                   |   844 | `looks.visitCostume`                 | Input/menu value, no standalone brick                                                                              | ✓               |
| `looks_costumenumbername`         |    40 | `looks.visitCostumenumbername`       | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `looks_goforwardbackwardlayers`   |    27 | `looks.visitGoforwardbackwardlayers` | GoNStepsBackBrick; front/back layer translation                                                                    | partial         |
| `looks_gotofrontback`             |   127 | `looks.visitGotofrontback`           | ComeToFrontBrick; front/back layer translation                                                                     | partial         |
| `looks_hide`                      |   435 | `looks.visitHide`                    | HideBrick                                                                                                          | ✓               |
| `looks_nextbackdrop`              |     1 | `looks.visitNextbackdrop`            | NextLookBrick                                                                                                      | ✓               |
| `looks_nextcostume`               |    61 | `looks.visitNextcostume`             | NextLookBrick                                                                                                      | ✓               |
| `looks_say`                       |    56 | `looks.visitSay`                     | SayBubbleBrick                                                                                                     | ✓               |
| `looks_sayforsecs`                |    32 | `looks.visitSayforsecs`              | SayForBubbleBrick                                                                                                  | ✓               |
| `looks_seteffectto`               |   188 | `looks.visitSeteffectto`             | Special handler/input; verify downstream (setGraphicEffect:to:); brightness/ghost only; other effects become notes | partial         |
| `looks_setsizeto`                 |   152 | `looks.visitSetsizeto`               | SetSizeToBrick                                                                                                     | ✓               |
| `looks_show`                      |   299 | `looks.visitShow`                    | ShowBrick                                                                                                          | ✓               |
| `looks_size`                      |    16 | `looks.visitSize`                    | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `looks_switchbackdropto`          |    42 | `looks.visitSwitchbackdropto`        | SetBackgroundBrick                                                                                                 | ✓               |
| `looks_switchcostumeto`           |   844 | `looks.visitSwitchcostumeto`         | SetLookBrick                                                                                                       | ✓               |
| `motion_changexby`                |   120 | `motion.visitChangexby`              | ChangeXByNBrick                                                                                                    | ✓               |
| `motion_changeyby`                |    75 | `motion.visitChangeyby`              | ChangeYByNBrick                                                                                                    | ✓               |
| `motion_direction`                |     7 | `motion.visitDirection`              | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `motion_glidesecstoxy`            |    36 | `motion.visitGlidesecstoxy`          | GlideToBrick                                                                                                       | ✓               |
| `motion_glideto`                  |     2 | `motion.visitGlideto`                | Special handler/input; verify downstream (glideTo:)                                                                | partial         |
| `motion_glideto_menu`             |     2 | `motion.visitGlideto_menu`           | Input/menu value, no standalone brick                                                                              | ✓               |
| `motion_goto`                     |     5 | `motion.visitGoto`                   | GoToBrick                                                                                                          | ✓               |
| `motion_goto_menu`                |     5 | `motion.visitGoto_menu`              | Input/menu value, no standalone brick                                                                              | ✓               |
| `motion_gotoxy`                   |   244 | `motion.visitGotoxy`                 | PlaceAtBrick                                                                                                       | ✓               |
| `motion_ifonedgebounce`           |     6 | `motion.visitIfonedgebounce`         | IfOnEdgeBounceBrick                                                                                                | ✓               |
| `motion_movesteps`                |    31 | `motion.visitMovesteps`              | MoveNStepsBrick                                                                                                    | ✓               |
| `motion_pointindirection`         |    21 | `motion.visitPointindirection`       | PointInDirectionBrick                                                                                              | ✓               |
| `motion_pointtowards`             |     6 | `motion.visitPointtowards`           | PointToBrick                                                                                                       | ✓               |
| `motion_pointtowards_menu`        |     6 | `motion.visitPointtowards_menu`      | Input/menu value, no standalone brick                                                                              | ✓               |
| `motion_setrotationstyle`         |     3 | `motion.visitSetrotationstyle`       | SetRotationStyleBrick                                                                                              | ✓               |
| `motion_setx`                     |     7 | `motion.visitSetx`                   | SetXBrick                                                                                                          | ✓               |
| `motion_sety`                     |     3 | `motion.visitSety`                   | SetYBrick                                                                                                          | ✓               |
| `motion_turnleft`                 |     7 | `motion.visitTurnleft`               | TurnLeftBrick                                                                                                      | ✓               |
| `motion_turnright`                |    14 | `motion.visitTurnright`              | TurnRightBrick                                                                                                     | ✓               |
| `motion_xposition`                |    25 | `motion.visitXposition`              | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `motion_yposition`                |    21 | `motion.visitYposition`              | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `operator_add`                    |    32 | `operator.visitAdd`                  | FormulaElement operator/function (via +)                                                                           | ✓               |
| `operator_and`                    |    28 | `operator.visitAnd`                  | FormulaElement operator/function (via &)                                                                           | ✓               |
| `operator_contains`               |     7 | `operator.visitContains`             | FormulaElement operator/function (via contains:)                                                                   | ✓               |
| `operator_divide`                 |    32 | `operator.visitDivide`               | FormulaElement operator/function (via /)                                                                           | ✓               |
| `operator_equals`                 |   611 | `operator.visitEquals`               | FormulaElement operator/function (via =)                                                                           | ✓               |
| `operator_gt`                     |    30 | `operator.visitGt`                   | FormulaElement operator/function (via >)                                                                           | ✓               |
| `operator_join`                   |    30 | `operator.visitJoin`                 | FormulaElement operator/function (via concatenate:with:)                                                           | ✓               |
| `operator_length`                 |    26 | `operator.visitLength`               | FormulaElement operator/function (via stringLength:)                                                               | ✓               |
| `operator_letter_of`              |    37 | `operator.visitLetter_of`            | FormulaElement operator/function (via letter:of:)                                                                  | ✓               |
| `operator_lt`                     |    29 | `operator.visitLt`                   | FormulaElement operator/function (via <)                                                                           | ✓               |
| `operator_mathop`                 |    16 | `operator.visitMathop`               | FormulaElement operator/function (via computeFunction:of:)                                                         | ✓               |
| `operator_multiply`               |    45 | `operator.visitMultiply`             | FormulaElement operator/function (via *)                                                                           | ✓               |
| `operator_not`                    |    43 | `operator.visitNot`                  | FormulaElement operator/function (via not)                                                                         | ✓               |
| `operator_or`                     |    63 | `operator.visitOr`                   | FormulaElement operator/function (via OR)                                                                          | ✓               |
| `operator_random`                 |   141 | `operator.visitRandom`               | FormulaElement operator/function (via randomFrom:to:)                                                              | ✓               |
| `operator_subtract`               |    44 | `operator.visitSubtract`             | FormulaElement operator/function (via -)                                                                           | ✓               |
| `pen_clear`                       |     7 | `pen.visitClear`                     | ClearBackgroundBrick                                                                                               | ✓               |
| `pen_stamp`                       |     6 | `pen.visitStamp`                     | StampBrick                                                                                                         | ✓               |
| `procedures_call`                 |    56 | `control.visitProcedures_call`       | Special handler/input; verify downstream (call); call/argument handling needs port verification                    | partial         |
| `procedures_definition`           |    52 | `control.visitProcedures_definition` | Special handler/input; verify downstream (visitor); user-brick model/semantics need port verification              | partial         |
| `procedures_prototype`            |    52 | `control.visitProcedures_prototype`  | Special handler/input; verify downstream (visitor); warp explicitly discarded                                      | partial         |
| `sensing_distanceto`              |     7 | `sensing.visitDistanceto`            | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_distancetomenu`          |     7 | `sensing.visitDistanceto_menu`       | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_keyoptions`              |    24 | `sensing.visitKey_options`           | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_keypressed`              |    24 | `sensing.visitKeypressed`            | FormulaElement sensor/helper (verify behavior); keyboard/touch emulation                                           | partial         |
| `sensing_mousedown`               |    13 | `sensing.visitMousedown`             | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_mousex`                  |     6 | `sensing.visitMousex`                | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_mousey`                  |    10 | `sensing.visitMousey`                | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_of`                      |   112 | `sensing.visitOf`                    | FormulaElement sensor/helper (verify behavior); property and target-dependent behavior                             | partial         |
| `sensing_of_object_menu`          |   112 | `sensing.visitOf_object_menu`        | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_resettimer`              |     1 | `sensing.visitResettimer`            | FormulaElement sensor/helper (verify behavior); timer workaround                                                   | partial         |
| `sensing_timer`                   |    13 | `sensing.visitTimer`                 | FormulaElement sensor/helper (verify behavior); timer workaround                                                   | partial         |
| `sensing_touchingobject`          |   106 | `sensing.visitTouchingObject`        | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_touchingobjectmenu`      |   106 | `sensing.visitTouchingObjectMenu`    | FormulaElement sensor/helper (verify behavior)                                                                     | partial         |
| `sensing_username`                |     2 | `sensing.visitUsername`              | FormulaElement sensor/helper (verify behavior); identity/privacy semantics                                         | partial         |
| `sound_changeeffectby`            |     2 | `sound.visitChangeeffectby`          | NoteBrick; visitor emits NoteBrick                                                                                 | partial         |
| `sound_changevolumeby`            |     7 | `sound.visitChangevolumeby`          | ChangeVolumeByNBrick                                                                                               | ✓               |
| `sound_cleareffects`              |     1 | `sound.visitCleareffects`            | Special handler/input; verify downstream (clearSoundEffects); clearSoundEffects absent in downstream map           | partial         |
| `sound_play`                      |    35 | `sound.visitPlay`                    | PlaySoundBrick                                                                                                     | ✓               |
| `sound_playuntildone`             |    73 | `sound.visitPlayuntildone`           | PlaySoundAndWaitBrick                                                                                              | ✓               |
| `sound_seteffectto`               |     5 | `sound.visitSeteffectto`             | NoteBrick; visitor emits NoteBrick                                                                                 | partial         |
| `sound_setvolumeto`               |     8 | `sound.visitSetvolumeto`             | SetVolumeToBrick                                                                                                   | ✓               |
| `sound_sounds_menu`               |   108 | `sound.visitSounds_menu`             | Input/menu value, no standalone brick                                                                              | ✓               |
| `sound_stopallsounds`             |     6 | `sound.visitStopallsounds`           | StopAllSoundsBrick                                                                                                 | ✓               |
