# Multiple project flavors: compatibility and rollout proposal

**Status:** Design only; no schema, API, client, migration or deployment changes.
**Design issue:** [#7451](https://github.com/Catrobat/Catroweb/issues/7451).
**Feature discussion:** [#7378](https://github.com/Catrobat/Catroweb/issues/7378) remains open.
**Source baseline:** `d1a5888103cd5b5406d62778a39fbcfb9017fa9a`, 21 October 2026.

A project should retain one primary flavor for legacy compatibility while belonging to
several curated discovery flavors. A discovery membership is neither a free-form tag
nor a claim that every client can execute the project. Active flavor ownership and
permitted combinations remain unknown; accepting this document does not enable editing.

## Source inventory and compatibility matrix

The matrix describes source expressions, not a reproduced production defect. SQL
collation, index mappings and caller inputs influence observed results. Characterization
fixtures and caller coverage are required before changing any existing filter.

| Surface                                                                                                                                                                              | Current source behavior                                                                                                                                                                               | Transition rule                                                                                                                                                                                       |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| [Project](../../src/DB/Entity/Project/Project.php), `flavor`                                                                                                                         | Indexed string with pocketcode default; PHP property/getter/setter allow null. The ORM column does not declare nullable. Extensions are a separate many-to-many relation.                             | Preserve the scalar and stored values. Verify actual schema/data; do not assume null is accepted by persistence or replace unknown values with the default.                                           |
| [Flavor](../../src/DB/Entity/Flavor.php), [themes configuration](../../config/packages/themes.php)                                                                                   | Nine named constants; persisted catalog has unique names. Themes use this list, with `app` umbrella and `admin` administration theme.                                                                 | Reuse catalog IDs/names, not a second taxonomy. Constants/configuration do not establish active ownership or runtime support.                                                                         |
| [MediaAsset](../../src/DB/Entity/MediaLibrary/MediaAsset.php)                                                                                                                        | Already has many-to-many Flavor memberships.                                                                                                                                                          | Align with the shared catalog without changing media-asset semantics.                                                                                                                                 |
| [ProjectManager](../../src/Project/ProjectManager.php), upload path                                                                                                                  | Sets project primary flavor from request flavor.                                                                                                                                                      | Preserve existing upload contract and primary assignment; new membership writes must be coordinated with every such writer.                                                                           |
| [ProjectsResponseManager](../../src/Api/Services/Projects/ProjectsResponseManager.php)                                                                                               | Serializes `flavor` from primary getter, with null represented as empty string.                                                                                                                       | Retain exact legacy serialization; never substitute the first sorted membership.                                                                                                                      |
| [ProjectRepository](../../src/DB/EntityRepository/Project/ProjectRepository.php), SQL positive filter                                                                                | Empty/whitespace-only means no flavor constraint. Otherwise `lower(primary) LIKE lower(input) OR lower(extension.internal_title) LIKE lower(input)`; no extra wildcard is added.                      | Keep old mode unchanged, including extension fallback, case handling and caller-supplied SQL pattern characters. New exact discovery mode is separate.                                                |
| Same repository, SQL negative filter                                                                                                                                                 | Leading `!` uses primary `<> remainder`, without lowercasing and without extension exclusion. Database collation/null semantics still apply.                                                          | Do not reinterpret as NOT membership. Preserve until a separately accepted compatibility change.                                                                                                      |
| Same repository, Elastica filter                                                                                                                                                     | Positive lowercased wildcard over primary OR `getExtensionsString` (a comma-separated string). Negative adds must-not primary Match, then also falls through to wildcards containing the leading `!`. | Characterize independently; do not describe it as equivalent to SQL. No current internal literal negative-filter caller was found in the inspected source; reachability and impact remain unverified. |
| [ProjectSearchService](../../src/Project/ProjectSearchService.php)                                                                                                                   | Nonblank flavor is exact `Terms('flavor', [input])`; no extension OR or explicit case normalization in this filter. Text search separately includes extension text.                                   | Preserve old text-search flavor semantics. Exact discovery must have an explicit opt-in contract shared by search/list implementations.                                                               |
| Repository counts and pagination                                                                                                                                                     | `createQueryCountBuilder` selects `count(e.id)`; positive filter joins extensions, and list limits/offsets operate on the query. ORM hydration may coalesce entities after SQL limits.                | Multiplicity is a risk requiring fixtures, not proof of deployed duplicate pages/counts. New membership mode must select unique IDs before count or pagination.                                       |
| [ExampleRepository](../../src/DB/EntityRepository/Project/Special/ExampleRepository.php), [FeaturedRepository](../../src/DB/EntityRepository/Project/Special/FeaturedRepository.php) | Special curated records have their own flavor associations and selection rules.                                                                                                                       | Project memberships do not automatically retag curated placements, examples or featured records. Audit each caller rather than broadly rewriting all flavor predicates.                               |
| [ThemeRequestEventListener](../../src/Application/Theme/ThemeRequestEventListener.php)                                                                                               | Request theme is preferred, legacy URL themes supported; request flavor defaults to pocketcode for unrecognized themes; routing theme may remain umbrella/legacy route.                               | Keep route generation, requested theme and primary flavor independent of the discovery set. Existing theme fallback is not permission to default unknown stored project values.                       |

The recognized catalog names are `pocketcode`, `pocketalice`, `pocketgalaxy`,
`phirocode`, `luna`, `create@school`, `embroidery`, `arduino`, `mindstorms`.
None is declared active, retired or mutually compatible here. Before implementation,
reconcile constants, configuration and actual catalog rows; identify aliases, duplicate
case variants, orphan references and unrecognized project values in an auditable report.
Do not publish private project records or operational inventories in that report.

## Proposed model and invariants

Retain `Project.flavor` as the primary scalar and legacy serialization source during the
transition. Add a proposed normalized project-to-existing-Flavor relation with a unique
`(project_id, flavor_id)` pair and appropriate lookup index in each direction. No schema
is changed by this PR. Primary must always be a member for a successfully reconciled
project; no duplicate or arbitrary string memberships. Use a project membership revision
for concurrency, indexing and audit. Membership order has no meaning.

Maintain a reconciliation state separate from public memberships. A project with an
unknown, null, empty or ambiguous legacy primary remains unresolved: preserve its exact
scalar, expose no fabricated catalog value, retain old read behavior and block new-mode
membership editing. A mapping report records the reason, counts and proposed resolution.
Require explicit approval of each mapping class; do not silently lowercase, merge catalog
rows, erase data or map to pocketcode. Resolved projects backfill exactly the primary
membership, not inferred extension memberships. This invariant has an explicit unresolved
exception until approved remediation; rollout cannot claim complete coverage while it exists.

Do not infer runtime compatibility from the join table, extension names or discovery
visibility. Before editing is enabled, a reviewed catalog must state for each flavor:
owner/contact role, supported client/runtime versions and hardware requirements,
assignment authority, permitted primary/discovery combinations, retirement behavior and
whether membership is a discoverability claim only. Record an explicit combination
allowlist, not an assumption that all nine flavors compose. Missing policy denies new
assignments while preserving historical primary reads. No external team messages are
part of this design task.

## Proposed API compatibility boundary

Keep all existing `flavor` request/response fields and legacy route/theme interpretation.
Do not change live OpenAPI or generated models in this PR. Recommend a separately
versioned or explicit opt-in discovery contract, reviewed before any endpoint change:

- A new `discovery_flavors` filter is a bounded set of exact canonical catalog names.
  One means membership in that flavor; multiple mean OR. Deduplicate input; at most
  the catalog size (currently nine). Empty supplied set is invalid, omitted means no
  discovery constraint. Unknown, inactive-for-new-discovery or noncanonical values are
  rejected explicitly, not treated as wildcards or silently dropped. Case-sensitive
  canonical names only; no trim/casefold aliases without an approved mapping.
- No patterns, negation or extension fallback in the new filter. Reject `%`, `_`, `*`,
  `?` and leading `!` as invalid names unless a future accepted catalog legitimately
  contains them; the current catalog does not. This deliberately differs from legacy
  repository filters and must be documented in the future contract.
- Reject combining legacy `flavor` and new `discovery_flavors` rather than choosing
  silent precedence. Keep legacy-only requests on their current backend semantics.
  A future harmonization of old SQL/search behavior needs separate approval and
  characterization; enabling memberships does not itself authorize that change.
- Additive opt-in response could expose `primary_flavor`, sorted canonical
  `discovery_flavors` and `membership_revision`, while retaining `flavor` unchanged.
  For unresolved rows, report a documented reconciliation state and no invented
  primary/catalog membership. Do not use such rows in new exact membership results.
- Reads disclose memberships only when the existing endpoint authorizes the project.
  No unguarded lookup by project ID or catalog-count leak for hidden/private projects.
  A proposed future write contract requires authentication, existing account/edit
  eligibility, current owner or explicitly approved catalog-assignment role, strong
  revision precondition, and atomic set replacement. No new admin bypass is implied.
  Missing precondition, stale revision, unknown flavor and forbidden combination must
  have distinct documented errors in the future API design.

Ordinary owners must not self-assert hardware/runtime capabilities without approved
assignment rules. Until that policy exists, all membership-editing flags remain off;
read support and characterization may be implemented only in a separately authorized task.
Legacy uploads/updates must never erase additional memberships by sending only `flavor`.
If a legacy writer changes primary, atomically add the new primary and preserve explicit
extras; validate the resulting combination. During mixed-version deployment, primary
changes that cannot meet the accepted policy must remain on an explicitly reviewed
compatibility path, not be silently rejected or delete memberships. This is a deployment
gate requiring all writers to be upgraded before multi-membership writes are enabled.

## Unique results, access and pagination

For new mode, compile a single eligible-project-ID relation after all endpoint-specific
visibility, private/debug/language-version, user and minor policies. Apply membership
with `EXISTS` or an equivalent unique-ID semijoin, not a multiplying outer join. Count
that same unique eligible relation. Order it by the endpoint's existing stable sort plus
project ID tie-breaker, then apply limit/offset or keyset predicates, and finally hydrate
rows in that ID order. Joining extensions/tags/memberships afterward must not shorten or
repeat pages. Do not fix multiplicity by deduplicating an already limited result array.

Preserve the actual policy of each surface. Public lists exclude private, invisible and
auto-hidden projects; owner lists may include private projects; page-by-ID uses
`ProjectManager::findProjectIfVisibleToCurrentUser` and its existing exceptions.
[ProjectsApiLoader](../../src/Api/Services/Projects/ProjectsApiLoader.php) currently applies
minor filtering to several results after retrieval. Do not claim counts already match
minor-visible pages. New mode must integrate the equivalent policy into the eligible-ID
relation for both count and paging, including owner exceptions, without widening access
or silently changing legacy mode. Verify parity with the policy helper rather than
simplifying it to a blanket NFK predicate. Preserve request/environment debug exceptions,
featured/page exceptions, max language version and curated endpoint rules as applicable.

An OR filter over two memberships must return a dual-member project once and score it
once. Membership count must not boost text-search ranking. Elasticsearch should have one
document per project with a canonical keyword-array field separate from legacy primary
and extension text; build new exact OR terms over that field. Preserve current ranking
and ID tie-breaker. No unapproved analyzer/case changes to existing fields.

New cursors must bind filter-mode/version, canonical sorted flavor set, sort, relevant
access/debug/version context and index generation. Reject reuse with another context;
re-authorize every page. Use opaque integrity-protected cursors, not client-supplied SQL.
Concurrent edits may legitimately change live results; no snapshot guarantee is implied.
If stable snapshot pagination is promised later, specify bounded snapshot lifetime and
revocation behavior first. Never continue serving hidden content from a stale snapshot.
Legacy cursors remain in their old mode; changing read mode requires a fresh first page.

## Proposed staged rollout and rollback

Each stage is a future implementation/deployment decision, not authorization from this PR.

1. **Inventory and contract:** capture a private reconciliation report and accepted
   catalog/combination/assignment policy. Characterize every legacy caller/backend,
   including featured/example/category/search/owner and download-related consumers.
   Approve the opt-in contract, errors, cursor strategy and parity acceptance criteria.
2. **Read-first compatibility:** deploy readers understanding optional memberships and
   unresolved state while legacy remains authoritative and all new flags are off.
   Missing relation/index data must not remove legacy projects or alter old responses.
   Add observability without public raw project data or full search strings.
3. **Shadow backfill:** after a separately reviewed additive migration, process bounded,
   restartable ID batches. Lock/check primary revision before inserting the exact mapped
   primary membership; unique constraints make retries idempotent. Record run version,
   input mapping version, unresolved reasons and checkpoints. Concurrent uploads/primary
   changes use coordinated writes or are reconciled before verification. No destructive
   column replacement, automatic unknown mapping or extension-derived membership.
4. **Dual-write:** upgrade every primary writer, including upload/reupload/import/admin
   and maintenance paths, before enabling edits. In one transaction lock project,
   validate policy/revision, retain scalar primary, ensure it is a member, persist extras,
   increment revision and write an index/cache outbox event. Crash/retry must converge;
   no write-to-index-before-commit. A legacy primary update cannot clear explicit extras.
5. **Index and cache:** build a versioned new index from a consistent database checkpoint,
   replay outbox changes through a known watermark, validate cardinality/revisions and
   only then switch an alias for opted-in reads. Keep old index until rollback window
   expires. Cache keys include mode, canonical filter, access context, sort and index/
   policy version. Invalidate all affected discovery lists/counts on membership/primary/
   visibility/deletion changes; no globally shared private responses. A stale index must
   be checked against current authorization before disclosure. Do not silently fall back
   from new semantics to a stale legacy index; fail explicitly or use equivalent DB read.
6. **Shadow comparisons:** compare old and new reads with primary-only backfill; report
   ordered unique IDs, totals, complete page walks and known extension/case/pattern
   differences by backend. Classify expected deliberate differences instead of forcing
   false parity. Require zero unexplained differences/access leaks, no duplicates/missing
   IDs under fixed fixtures and acceptable measured query latency/storage before canary.
7. **Flagged opt-in reads, then editor:** enable small reversible read cohorts only after
   reconciliation, policy/access and index checks. Editor remains independently disabled
   until approved authority/combinations and atomic writes are deployed everywhere.
   Membership edits have audit events and quota bounded by the approved catalog, not
   an arbitrary tagging UI. Monitor count/page disagreements and outbox/index lag.

Rollback first disables editing, then new discovery reads, preserving primary scalar,
legacy responses, all stored extra memberships, revisions, mapping decisions and audit.
Keep dual-write compatibility while any new writer remains deployed; do not run destructive
down-migrations or replace scalar primary from an arbitrary membership. Switch old index/
cache namespace only for legacy reads, rebuild from current primary data if stale, and
retain outbox/audit for replay. Export a versioned lossless membership/primary snapshot
before any later destructive cleanup, with a tested restore path. Rolling back discovery
intentionally hides extra placements; it must not erase them or change runtime primary.
Project deletion purges membership/index/cache records through the existing deletion
lifecycle and durable retry; snapshots/rollback must not resurrect deleted or hidden rows.

## Decisions and acceptance before implementation

| Decision                                | Recommendation                                                                                            | Required evidence/approval                                                                                              |
| --------------------------------------- | --------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| Active catalog and runtime combinations | Curated existing Flavor catalog; explicit combination allowlist; discovery is not execution certification | Active owner, supported runtime/hardware and assignment authority for each flavor; unknown remains unknown              |
| Old filter semantics                    | Preserve per-surface legacy behavior; add opt-in exact membership OR                                      | Characterization matrix, caller coverage and accepted deliberate extension/case/pattern/negative differences            |
| Extension relationship                  | Retain legacy extension fallback only in legacy mode; no automatic extension-to-membership backfill       | Product acceptance of new-mode difference; any later curated mapping separately approved                                |
| Primary updates and permissions         | Primary always included; legacy scalar and route/theme preserved; no extras erased                        | All-writer inventory, atomic policy for primary changes, accepted owner/staff rights                                    |
| Unknown/ambiguous stored values         | Explicit unresolved report and approved mappings; no silent default/erase                                 | Reconciled catalog/schema/data report and mapping sign-off before full rollout                                          |
| Search and count consistency            | Unique authorized IDs before count/page, one indexed document per project                                 | SQL/Elasticsearch parity, visibility/minor policy tests, complete fixed-corpus page walks and measured resource budgets |

Future tests must cover primary-only and dual-membership projects, overlapping OR filters,
multiple extensions, empty/no-match inputs, canonical case and every supported legacy
pattern/negative form, null/unknown/ambiguous values and special curated records. Run each
supported SQL/Elasticsearch/ProjectSearchService mode independently. Reproduce the
negative fall-through through an actual caller fixture before asserting user impact;
a correction would be a separately reviewed bug fix, not hidden inside this design.

Access fixtures include guest, owner, other user, minor owner/other minor, admin and
super-admin across visible/hidden/auto-hidden/featured/private/debug/version cases.
Compare new-mode count, offset and cursor results under the same policy. Cover duplicate
join rows, equal sort keys, index lag, authorized deletion, stale cursors and changed
filters/access. Do not infer permission from a matching flavor or requested theme.

Migration tests cover repeated batches, crash between commit/index publication, primary
changes during backfill, mixed readers/writers, conflicting edits, denied combinations,
unknown mapping, cache invalidation, outbox retries, rollback and restoring all extras
without resurrecting deleted projects. Old request/response snapshots, main route/theme,
primary upload and actual client compatibility remain required acceptance gates.

Static source inspection and formatting/build checks validate this proposal only. No
runtime compatibility, migration correctness or cross-flavor execution is established.
