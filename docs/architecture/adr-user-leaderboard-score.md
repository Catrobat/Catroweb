# ADR: User Leaderboard Score

**Date**: 2026-10-19
**Status**: Proposed
**Authors**: Daniel, Claude
**Issue**: [#6696](https://github.com/Catrobat/Catroweb/issues/6696)

## Context

The homepage gets two leaderboards: all-time top creators and top creators of the last 7 days
(`GET /api/users?order_by=rank|rank_recent`). Both need one number per user, recomputed by
`catrobat:update:userranking` (daily, already in `catrobat:cronjob`). Classic Elo needs head-to-head
results, which we do not have, so this is a _ranking score_, not Elo.

Signals we store today: per-project `downloads` counter (counted once per logged-in user,
`ProjectStatisticsService`), `program_downloads` rows with `downloaded_at` (same rule), views
(counted per request, easy to inflate), reactions (`ProjectLike`), remixes.

## Options

| Approach                                                           | Formula                                                          | Pros                                               | Cons / gaming risk                                                                                            | Cold start                                                     |
| ------------------------------------------------------------------ | ---------------------------------------------------------------- | -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| 1. Average downloads per project (the previous `userranking` code) | `D / N`                                                          | Trivial                                            | One lucky project tops the board; deleting weak projects raises the score                                     | A single project with many downloads wins outright             |
| 2. Bayesian-smoothed average                                       | `(D + k·m) / (N + k)`, `m` = global average, `k` = 5             | Rewards quality, damps small samples, one SQL pass | Still favours a few strong projects over volume; uploading many 0-download projects lowers it (self-limiting) | New users sit near the global average until they have projects |
| 3. Weighted composite                                              | `Σ wᵢ · normalised(signalᵢ)` (like `catrobat:update:popularity`) | Captures views, reactions, remixes                 | Views are per request and cheap to farm; four weights to tune; min-max normalisation is dominated by outliers | Depends on weights                                             |

## Decision

Option 2, as suggested in the issue:

- `ranking_score = round(100 · (D + 5m) / (N + 5))` over the user's **public** projects (visible, not
  auto-hidden, not private, not debug builds), `D` = their download counters.
- `ranking_score_recent` = the same formula over `program_downloads` of the last 7 days, excluding the
  owner's own downloads; `N` = projects downloaded in that window, `m` = the window's global average.
- Scores are stored in hundredths so the small weekly numbers do not collapse into ties.
- Users without any download get no score and are not listed.
- Read-time filter: disabled users, suspended users (`profile_hidden`) and users whose parental consent is
  `pending`/`revoked` are never listed, even between recomputes. Ties are broken by user id (keeps the query on the score index).
- Scores are global (not per flavor), like the rest of `GET /api/users`.

## Consequences

- One indexed column per board on `fos_user`; listing is a plain indexed `ORDER BY`.
- The command writes scores in batches of 1,000 auto-committed `UPDATE … CASE` statements (no long lock on
  `fos_user`, no window where everyone reads 0); `--dry-run` prints the top 10 without writing.
- Changing `k`, the window or adding signals only touches `UpdateUserRankingCommand`.
