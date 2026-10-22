# ADR: Replace Elasticsearch with MariaDB FULLTEXT? (Evaluation)

**Date**: 2026-10-19
**Status**: Proposed: spike before deciding
**Issue**: [#6341](https://github.com/Catrobat/Catroweb/issues/6341)

## Context

Elasticsearch needs a JVM heap of at least 512 MB (1 GB in production) and is the largest memory consumer next to MariaDB. The query surface is small: three indexes (`app_program`, `app_user`, `app_studio`, see `config/packages/fos_elastica.php`), one multi-match query shape, no advanced ES features.

What MariaDB FULLTEXT would lose:

- Words shorter than `innodb_ft_min_token_size` (3) are not indexed; needs a `LIKE 'x%'` fallback (also for ID segments).
- CJK: no ngram/MeCab parser in MariaDB (MySQL-only), so CJK titles become one token. Regression for CJK users.
- Ranking changes (TF-IDF-like instead of BM25) and no test pins the order; French elision is lost.

## Decision

Spike MariaDB FULLTEXT with fallbacks behind a `SearchBackend` flag; do not commit to removing ES yet.

## Why

It removes a whole service and the index-timing races in tests, but the CJK and ranking regressions are real and currently unmeasured.

## Spike go/no-go

1. First make the search tests assert order and result counts; otherwise no migration is verifiable.
2. Add `search_text` (name, description, owner, tags, extensions) with FULLTEXT on projects, usernames and studios, plus the `LIKE` fallback.
3. Run the existing search scenarios and compare top-20 results for the 50 most frequent production queries.
4. **Go** if suites pass, overlap is acceptable and CJK queries are rare. **No-go** if CJK matters: pick Meilisearch/Typesense, or just trim the ES heap if RAM isn't the real constraint.

Note for a SQL backend: the ban cascade in `ContentVisibilityManager` uses bulk DQL `UPDATE`, which bypasses the FOSElastica listeners, so indexed `auto_hidden` can go stale today; a SQL search would read the live column.
