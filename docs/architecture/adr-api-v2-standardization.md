# ADR: API v2 Standardization

**Date**: 2026-04-11
**Status**: Accepted

## Context

The API grew organically: mixed singular/plural routes, offset and cursor pagination, varying envelopes, endpoint-specific error DTOs, integer IDs exposed externally. Before the first official release everything was standardized in one pass, **without backward compatibility** (no deprecated aliases), since there was no release to stay compatible with.

## Decisions

1. **Plural resource nouns** (`/projects/{id}`, `/studios`, `/users/me`). Actions (`/join`, `/follow`, ...) are POST sub-resources; non-resource endpoints (`/search`, `/health`, `/authentication`) stay as-is.
2. **Cursor pagination** on all collections: `{ data, next_cursor, has_more }`; no `offset`. Exceptions: count endpoints, the achievements overview (small fixed shape) and static lists (`{data: []}`).
3. **UUIDs for every externally exposed ID.** Integer PKs stay internal; a UUID column is added and backfilled.
4. **One error shape** everywhere: `{ "error": { code, type, message, details: [{ field, message }] } }`.
5. **PATCH for partial updates** (`PATCH /projects/{id}`, `/users/me`, `/studios/{id}`).
6. **Status codes**: 200 read/mutation with body, 201 created, 204 no body, 400 malformed request, 401 unauthenticated, 403 forbidden, 404, 409 conflict, 422 validation/domain rule, 429 rate limited.

## Known gap: pre-UUID comment references

`Version20260411120000_uuid_migration` gave comments random UUIDs and remapped only tables with a foreign key to `user_comment`. Four columns store comments as a plain `content_id` string and were not remapped: `content_report`, `content_appeal`, `content_moderation_action`, `CatroNotification.moderation_content_id`. No old-id → UUID mapping was kept, so pre-migration rows (integer `content_id`) cannot be remapped except from a pre-migration backup.

- They are not auto-closed, and moderators should not accept/reject them: both resolutions change the reporter's trust score.
- `catrobat:check:data-consistency` lists them. The 2026-10-20 production dry run found no dangling reports.
- New comment reports always store the UUID. See #7415.
