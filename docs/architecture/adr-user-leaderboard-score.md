# ADR: User Leaderboard Score

**Date**: 2026-10-19
**Status**: Accepted
**Issue**: [#6696](https://github.com/Catrobat/Catroweb/issues/6696)

## Context

The homepage has two leaderboards, all-time and last 7 days (`GET /api/users?order_by=rank|rank_recent`), recomputed daily by `catrobat:update:userranking`. There are no head-to-head results, so this is a ranking score, not Elo. Downloads are counted once per logged-in user; views are counted per request and are easy to inflate.

## Decision

Bayesian-smoothed average downloads per public project: `ranking_score = round(100 · (D + 5m) / (N + 5))`, `m` = global average.

- `ranking_score_recent` uses the same formula over the last 7 days of `program_downloads`, excluding the owner's own downloads.
- Stored in hundredths so small weekly numbers don't collapse into ties; users without downloads are not listed.
- Disabled, suspended (`profile_hidden`) and consent-`pending`/`revoked` users are filtered at read time, so they never appear between recomputes.
- Global, not per flavor.

## Why

- Plain average (`D / N`, the old code) lets one lucky project top the board and rewards deleting weak projects.
- A weighted composite with views/reactions/remixes is farmable (views) and needs four weights tuned against outliers.
- Smoothing damps small samples, is one SQL pass, and uploading many 0-download projects only lowers your own score.
