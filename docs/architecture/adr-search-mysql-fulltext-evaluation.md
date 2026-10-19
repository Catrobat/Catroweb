# ADR: Replace Elasticsearch with MariaDB FULLTEXT? (Evaluation)

**Date**: 2026-10-19
**Status**: Proposed
**Authors**: Daniel, Claude
**Issue**: [#6341](https://github.com/Catrobat/Catroweb/issues/6341)

## Context

Elasticsearch needs a JVM with at least a 512 MB heap. The dev and test stacks run
`ES_JAVA_OPTS=-Xms512m -Xmx512m` (`docker/docker-compose.dev.yaml`, `docker/docker-compose.test.yaml`), and
the production guide sets `-Xms1g -Xmx1g` (`docs/setup/Installing-Elasticsearch.md`). For a low-resource
server that is the largest single memory consumer next to MariaDB. The issue asks whether MariaDB 10.11
FULLTEXT can replace it. The research comment on the issue concluded "yes, 2–3 days". The follow-up comment
asked for a spike and for honesty about behaviour we would lose. This document measures what is actually used.

Versions today: `elasticsearch:9.4.2` (compose), `friendsofsymfony/elastica-bundle` v7.2.0, `ruflin/elastica`
9.0.0 (`composer.lock`). The issue text still says 7.17 and the issue comment says 8.17, but both are outdated.

## What Elasticsearch is used for (measured)

### Indexes (`config/packages/fos_elastica.php`)

| Index         | Entity    | Searched fields                                                                            | Filter fields                                                     | Indexed but only used by the disabled listing path                                  |
| ------------- | --------- | ------------------------------------------------------------------------------------------ | ----------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| `app_program` | `Project` | `id`, `name^3`, `description`, `getUsernameString`, `getTagsString`, `getExtensionsString` | `private`, `visible`, `debug_build`, `language_version`, `flavor` | `downloads`, `rand`, `popularity`, `uploaded_at`, `last_modified_at`, `auto_hidden` |
| `app_user`    | `User`    | `id`, `username^3`                                                                         | `verified`, `profile_hidden`                                      | —                                                                                   |
| `app_studio`  | `Studio`  | `name^3`, `description`                                                                    | `is_enabled`, `auto_hidden`                                       | —                                                                                   |

The issue research lists two indexes; there are three (`app_studio`, `src/Studio/StudioSearchService.php`).

### Analysis

One identical custom analyzer for all three indexes: the `standard` tokenizer plus `lowercase`, `elision` and a
`stop` filter with `_english_` stop words. Every language uses the same analyzer. There are no per-language
analyzers, no stemming, no synonyms, no ngram/edge-ngram, no ASCII folding and no custom normalizers.

### Queries

All three services build the same shape (`ProjectSearchService::projectSearchQuery`,
`UserManager::userSearchQuery`, `StudioSearchService::studioSearchQuery`):

- `Util::escapeTerm($query)`, then `*` appended to every space-separated word (prefix match on every word)
- one `query_string` over the boosted fields, `analyze_wildcard`, `default_operator: AND`
- `Terms`/`Term`/`Range` filters for visibility, flavor and `language_version`
- results hydrated back to Doctrine entities (`TransformedFinder`), offset pagination, plus a separate count
  query (`findPaginated()->getNbResults()`)

**Not used anywhere in `src/`:** fuzziness or typo tolerance, synonyms, aggregations or facets, highlighting,
suggesters or autocomplete, geo, function-score or decay ranking, multi-index search, percolation. The
Elastica listing path in `ProjectRepository::getProjects()`/`getTrendingProjects()` sits behind
`GET_projects_elastica`, which is `false` (`config/packages/feature_flags.php`).

### Call sites

- `GET /api/search` (`src/Api/SearchApi.php`). With `type=all` it runs **six** ES requests: search plus count
  for projects, users and studios.
- `GET /api/projects/search` (`ProjectsApiLoader`), `GET /api/users` with `query` and `GET /api/users/search`
  (`UserApiLoader`).
- Index lifecycle: FOSElastica Doctrine listeners on flush, `fos:elastica:reset`/`populate` in
  `ResetCommand` and in `DataFixturesContext` (reset `@BeforeFeature`, populate on demand).

### What the tests pin

43 search scenarios: `api/search` 26, `web/search` 17. Others touch search too: `api/projects/GET_projects_search`,
`api/user/GET_users_search`, `web/top-bar/top_app_bar_search`, `web/project-list/project_list_notforkids_blur`.
Behaviour they rely on:

- **ID segment prefix**: `query=phci` and `query=-etqx` find project `phci-etqx`, and `kbrw` finds `kbrw-khwf`
  (`api/search/search_projects.feature:68-91`). This works because the `standard` tokenizer splits UUIDs at `-`.
- **Tags, extensions, owner name**: `search_projects_by_tags.feature`, `search_projects_by_extension.feature`,
  `web/search/search_projects_via_projectowner.feature`.
- **One-character, non-Latin user search**: `query=п` finds `пользователь`
  (`api/user/GET_users_search/search_users.feature:50`).
- **Ranking is not asserted.** `ApiContext::theSearchResponseShouldContainTheFollowingProjects()` only checks
  that each expected name is present. It checks neither order nor count. So "name hit before description
  hit" (`query=Universe`) is expected by readers but not enforced. A replacement could silently change
  ordering, and the tests would stay green.

### Search is the measured source of Behat reruns

In the last 15 nightly `Dynamic analysis` runs on `main` (2026-10-05 → 2026-10-19), the `api-search` job needed a
rerun **15/15** times. `api/search/search.feature:100` returns HTTP 500 on its first attempt.
`web/search/search.feature:38` failed all four attempts on both red nightlies (2026-10-09, 2026-10-19). The
Behat step `I wait for the search index to be updated` is a `sleep(1)` (`ApiContext::waitForSearchIndex()`, 6
uses). ES indexing is near-real-time (refresh interval), so the tests race it. FULLTEXT on InnoDB is part of the
same transaction, so this whole class of flake would disappear.

## What MariaDB 10.11 FULLTEXT can and cannot match

| Current behaviour                                  | InnoDB FULLTEXT (`MATCH … AGAINST … IN BOOLEAN MODE`)                                                                                                                                                                                                                                             | Verdict                                    |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------ |
| Every word required, prefix match (`+cat* +game*`) | Native in boolean mode                                                                                                                                                                                                                                                                            | ✅                                         |
| Case-insensitive                                   | Follows the column collation (`utf8mb4_unicode_ci`)                                                                                                                                                                                                                                               | ✅                                         |
| English stop words                                 | InnoDB has its own default stop-word list (36 words; replaceable via `innodb_ft_server_stopword_table`)                                                                                                                                                                                           | ✅ (slightly different list)               |
| Filters, flavor, `language_version`                | Plain `WHERE`; `program_listing_idx` already covers the visibility columns                                                                                                                                                                                                                        | ✅                                         |
| Field boost `name^3`                               | `MATCH` only accepts the exact column list of one FULLTEXT index, so you need one index per boosted group and `3*MATCH(name)+MATCH(description,…)` in `ORDER BY`                                                                                                                                  | ⚠️ manual, extra indexes                   |
| Tags, extensions, owner name                       | Need a denormalised `search_text` column kept in sync by a Doctrine listener (as ES does today), or JOINs over FULLTEXT on `tag`/`extension`/`fos_user`                                                                                                                                           | ⚠️ new sync code                           |
| ID segment prefix (`phci`, `etqx`)                 | The default parser splits on `-`, so `+phci*` works; `-etqx` must be stripped of the leading `-` (boolean NOT operator)                                                                                                                                                                           | ⚠️ needs query sanitising                  |
| Words shorter than `innodb_ft_min_token_size` (3)  | Not indexed, so `п`, `ab` and 1–2-letter names find nothing                                                                                                                                                                                                                                       | ❌ needs a `LIKE 'x%'` fallback            |
| Chinese, Japanese, Thai (no spaces)                | The default parser only splits on spaces and punctuation, so a CJK title is one token and only a whole-title prefix matches. ES's `standard` tokenizer splits CJK per ideograph. MariaDB has **no ngram or MeCab parser** (those are MySQL-only plugins); the MariaDB route is the Mroonga engine | ❌ regression for CJK users                |
| BM25 relevance                                     | InnoDB boolean-mode relevance is TF-IDF-like and much coarser                                                                                                                                                                                                                                     | ⚠️ order will change (and no test pins it) |
| `elision` (l', d')                                 | Not available; `l'avion` stays one token                                                                                                                                                                                                                                                          | ⚠️ minor (French)                          |

## Options

| Option                                                        | RAM saved                                                       | Effort                                                                                                                               | Behaviour change                                                   | Ops                                         |
| ------------------------------------------------------------- | --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------ | ------------------------------------------- |
| **A. Keep ES, trim it**: heap 256–512 MB, keep 9.x            | ~0.5 GB vs the 1 GB prod setting                                | Config only, plus fixing the two test races                                                                                          | None                                                               | Same service, OOM risk at small heaps       |
| **B. MariaDB FULLTEXT + fallbacks**                           | All of ES (≈ 1–1.5 GB RSS in prod)                              | `search_text` column + listener + migration; three services rewritten; `LIKE` fallback for short words and IDs; stronger tests first | Ranking changes; CJK/Thai substring search regresses; elision lost | One fewer service; FOSElastica removed      |
| **C. Meilisearch or Typesense**                               | Most of it (tens to low hundreds of MB, measure with real data) | New client + sync (no Symfony bundle equivalent to FOSElastica), Docker service, backups                                             | Gains typo tolerance; CJK handled by their tokenizers              | Still a separate service to run and upgrade |
| **D. B, but keep ES optional behind a flag during the spike** | Same as B once switched                                         | B plus a `SearchBackend` seam with two implementations                                                                               | Comparable side by side                                            | Temporary dual path                         |

## Recommendation

**Run a spike of B (MariaDB FULLTEXT with fallbacks) behind a flag (D). Decide with data. Do not commit to removal yet.**

Arguments for B:

- The query surface is small (one query shape in three services).
- None of the advanced ES features are in use.
- It removes the largest memory consumer.
- It removes the search-index race behind the nightly reruns.

Arguments against deciding now:

- CJK/Thai search would regress. MariaDB has no ngram parser.
- Ranking would change, and no test notices.
- Each of the three services needs a short-query/ID fallback.

The spike answers both questions cheaply.

Spike scope and go/no-go:

1. First, make the search tests assert what we care about: order for the `Universe` case, and result counts.
   Without that, any migration is unverifiable.
2. Implement `search_text` (name, description, owner, tags, extensions) and FULLTEXT on
   `program(search_text)`, `program(name)`, `fos_user(username)` and `studio(name, description)`.
   Add the boolean-mode query and the `LIKE` fallback for terms shorter than 3 characters and for ID segments.
3. Run the existing 43 search scenarios against it. Compare the top 20 results for the 50 most frequent
   production queries (from logs) between ES and MariaDB.
4. **Go** if the Behat search suites pass, the top-20 overlap is acceptable to you, and CJK queries (sample
   from production) are rare or acceptable with prefix-only matching. **No-go** if CJK search matters: then
   choose C (Meilisearch), or A if RAM is not actually the constraint on the target server.

## Migration plan sketch (if the spike says go)

1. Migration: `program.search_text` (TEXT) plus the FULLTEXT indexes above. Doctrine listener on
   `Project` / `Tag` / `Extension` / `User` rename to keep `search_text` in sync. Note that the ES index does
   not re-index a project today when its owner renames, because only `Project` changes trigger it.
2. A `SearchBackend` interface with `Elastica` and `Fulltext` implementations behind a feature flag. Switch it
   per environment.
3. Backfill command (`catro:search:rebuild-text`) for existing rows.
4. Flip the flag in test and dev first. Make `I wait for the search index to be updated` a no-op, and remove
   `fos:elastica:*` from `DataFixturesContext` and `ResetCommand`.
5. Flip in production and watch search latency and zero-result rate for two weeks.
6. Remove `friendsofsymfony/elastica-bundle`, `config/packages/fos_elastica.php`, the ES services in
   `docker/*.yaml`, `ELASTICSEARCH_URL` in `.env`, the `GET_projects_elastica` flag and its dead code paths in
   `ProjectRepository`, and `docs/setup/Installing-Elasticsearch.md`.

## Side findings (not part of this decision)

- **Project search does not filter `auto_hidden`.** `ProjectSearchService::projectSearchQuery()` filters only
  `private`, `visible` and `debug_build`, even though `auto_hidden` is indexed. Community-auto-hidden projects
  can therefore appear in `/api/search` and `/api/projects/search`. The listing path
  (`ProjectRepository::excludeInvisibleProjects`) does filter it. Even with a filter, the indexed value can go
  stale: the ban cascade in `ContentVisibilityManager` uses bulk DQL `UPDATE`, which bypasses the FOSElastica
  Doctrine listeners. A SQL-based search would read the live column.
- `GET /api/search?type=all` makes six ES round-trips per request. The count queries could be dropped or merged
  whichever backend is chosen.
