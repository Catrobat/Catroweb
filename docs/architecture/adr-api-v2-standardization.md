# ADR: API v2 Standardization

**Date**: 2026-04-11
**Status**: Accepted
**Authors**: Daniel, Claude

## Context

The Catroweb API has grown organically. Routes mix singular/plural nouns, pagination is inconsistent (offset vs cursor), response envelopes vary (raw arrays vs wrapped), error responses use endpoint-specific DTOs, and some entities still expose integer IDs externally. Before an official release, we standardize everything in one pass.

## Decisions

### 1. Plural Resource Nouns (Everywhere)

| Old                       | New                        |
| ------------------------- | -------------------------- |
| `/project/{id}`           | `/projects/{id}`           |
| `/studio`                 | `/studios`                 |
| `/studio/{id}`            | `/studios/{id}`            |
| `/user` (self)            | `/users/me`                |
| `/user/{id}`              | `/users/{id}`              |
| `/notification/{id}/read` | `/notifications/{id}/read` |

Action endpoints (`/join`, `/leave`, `/promote`, `/follow`) stay as POST sub-resources under the plural parent.
Non-resource endpoints (`/search`, `/health`, `/authentication`) stay as-is.

### 2. Cursor Pagination Everywhere

All collection endpoints return:

```json
{
  "data": [...],
  "next_cursor": "opaque-string-or-null",
  "has_more": true
}
```

Remove deprecated `offset` parameter from all endpoints.
Remove `PaginationInfo` schema.

**Exceptions**:

- Aggregate/count endpoints (`/notifications/count`, `/achievements/count`) keep their custom shapes
- Achievements list keeps its custom `{unlocked, locked, most_recent, ...}` shape (no pagination needed, ~11 items)
- Static list endpoints (`/projects/extensions`, `/projects/tags`) use `{data: []}` without pagination

### 3. UUID Identifiers Everywhere

All externally-exposed entity IDs become UUID strings:

| Entity                                           | Current | Target           |
| ------------------------------------------------ | ------- | ---------------- |
| Program, User, Studio, MediaAsset, MediaCategory | UUID    | UUID (no change) |
| UserComment                                      | integer | UUID             |
| CatroNotification (all subtypes)                 | integer | UUID             |
| Achievement                                      | integer | UUID             |
| UserAchievement                                  | integer | UUID             |
| ContentReport                                    | integer | UUID             |
| ContentAppeal                                    | integer | UUID             |
| StudioUser (membership)                          | integer | UUID             |
| StudioJoinRequest                                | integer | UUID             |
| StudioActivity                                   | integer | UUID             |
| StudioComment (id, parent_id)                    | integer | UUID             |
| FeaturedBanner                                   | integer | UUID             |

**Strategy**: Add UUID column, backfill, expose UUID externally, keep integer PK internally.

### 4. Unified Error Response

Kill all endpoint-specific error DTOs. One shape everywhere:

```json
{
  "error": {
    "code": 422,
    "type": "validation_error",
    "message": "Validation failed",
    "details": [{ "field": "email", "message": "Email already in use" }]
  }
}
```

**Removed schemas**: `RegisterErrorResponse`, `UpdateUserErrorResponse`, `ResetPasswordErrorResponse`, `CreateStudioErrorResponse`, `UpdateStudioErrorResponse`, `UploadErrorResponse`, `UpdateProjectErrorResponse`, `UpdateProjectFailureResponse`.

### 5. HTTP Method Cleanup

| Old                          | New                    | Why                                  |
| ---------------------------- | ---------------------- | ------------------------------------ |
| `PUT /project/{id}`          | `PATCH /projects/{id}` | Partial update (all fields optional) |
| `PUT /user`                  | `PATCH /users/me`      | Partial update                       |
| `POST /studio/{id}` (update) | `PATCH /studios/{id}`  | Was using POST for update            |

### 6. Field Changes

- **Add** `uploaded_at` (ISO 8601 datetime) to `ProjectResponse`
- **Remove** `uploaded` (unix timestamp) from `ProjectResponse`
- **Keep** `uploaded_string` (translated relative time)
- **Remove** deprecated `download` field (use `downloads`)

### 7. Query Parameter Changes

- `/projects` category parameter becomes **optional** (was required). No category = recent projects.
- `/users` query parameter becomes **optional** (was required). No query = list all users.

### 8. Status Code Rules

| Code | Usage                                 |
| ---- | ------------------------------------- |
| 200  | Successful read or mutation with body |
| 201  | Resource created with body            |
| 204  | Successful mutation, no body          |
| 400  | Malformed request                     |
| 401  | Missing/invalid auth                  |
| 403  | Authenticated but forbidden           |
| 404  | Resource not found                    |
| 409  | Conflict (duplicate)                  |
| 422  | Validation/domain rule failure        |
| 429  | Rate limited                          |

## No Backward Compatibility

Since there is no official release yet, we break everything cleanly. No deprecated aliases.
