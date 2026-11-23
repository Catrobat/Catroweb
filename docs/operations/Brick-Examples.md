# Brick examples

The docs brick pages list community projects that use a brick:
`GET /api/docs/reference/bricks/{type}/projects` (`{type}` is the code.xml brick type, e.g. `SetXBrick`). It returns
public, visible projects whose owner is not hidden and that the browser player can run (language version from
`Project::PLAYER_MIN_LANGUAGE_VERSION` on, the same constant that decides whether the project page offers the player),
most downloaded first, in the usual project list format with a keyset cursor. Logged-out and adult responses are cached
like the other project lists.

The query joins and sorts every project that uses the brick. For common bricks (`SetXBrick`, `WaitBrick`, …) a cold
cache can take around a second; this is accepted because the list cache absorbs repeat requests.

## Data

`project_brick_usage (project_id, brick_type, count)` is a queryable copy of the brick counts of a project's newest
`project_code_statistics` row (`brick_counts` is JSON and cannot be searched per brick). Persisting a statistics row
replaces the project's usage rows (`ProjectBrickUsageWriter`), so uploads and the lazy parse on the code statistics
endpoint keep it current. The rows are written after the statistics commit, as an upsert; a failure is logged and never
breaks the statistics write. Only class-name types (`^[A-Za-z][A-Za-z0-9_]{0,99}$`) are stored, in a binary collation.

## Backfill

Projects uploaded before the table existed need a one-off backfill after the deploy:

```bash
bin/console catro:backfill:brick-usage -v
```

- A project with code statistics gets the counts of its newest row; one without is parsed from the `code.xml` in its
  stored `.catrobat` file (read from the zip, nothing is extracted to disk). Projects without a readable file are counted
  and skipped.
- It walks all projects in id order in batches (`--batch-size`, default 500) and keeps memory flat. Projects that already
  have usage rows are skipped, so it is safe to rerun and to run alongside production traffic.
- With `-v` it prints the last project id of every batch; resume an interrupted run with `--after=<id>`.
- Duration: reading and parsing a `code.xml` takes about 1.5 ms; the database writes and cold disk reads dominate. Expect
  roughly one to two hours for a few hundred thousand projects.

## Inline player

`templates/Docs/BrickExamples.html.twig` (Stimulus controller `docs--brick-examples`) renders the list with the usual
project cards. A click on a card plays the project in an iframe of the browser player (`PLAYER_ORIGIN/#<project id>`)
instead of opening its page; without WebGL or a configured player the card stays a link. The CSP allows the player origin
in `frame-src`, and the player allows share as frame ancestor.
