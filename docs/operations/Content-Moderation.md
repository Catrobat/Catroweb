# Content moderation pipeline

Catroweb is a platform for children. Content is hidden from everyone but its owner in three ways:

| Path                    | Trigger                                                                                           | Code                                                                |
| ----------------------- | ------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------- |
| Community reports       | Reports whose trust-weighted score passes the threshold                                           | `ReportProcessor` → `AutoModerationService::autoHideContent`        |
| Adults-only text signal | "18+", "+18", "nsfw", "Ab 18", "solo adultos", "только для взрослых", … in a saved text           | `AgeSignalDetector` → `AutoModerationService::flagAgeSignal`        |
| NSFW image              | An image at or above `CONTENT_SAFETY_THRESHOLD` (see [NSFW scanning](./NSFW-Content-Scanning.md)) | `ScanContentImagesHandler` → `AutoModerationService::flagForReview` |

Profanity is a separate concern: `TextSanitizer` censors words from `config/moderation/wordlists/`. Age signals are not censored; the text is saved as written so the reviewer sees it.

## Automatic flags

`AutoModerationService::flagForReview` hides a project or studio (`auto_hidden`), writes an `auto_hidden` audit entry with the reason in its note, and adds a report **without reporter** to the moderation queue (`/admin/moderation/report/list`). The owner gets a notification and may appeal; admins get a notification too. Accepting or rejecting the report in the queue works as for a community report. Content that is already hidden is left alone, so nothing is queued twice.

**Accounts are never hidden or suspended automatically** (`flagUserProfile`): an about or "currently working on" text with a signal is cleared, a username is kept as it is, and the profile is queued (reporter-less report, category `inappropriate_profile`) with a notification to admins only. The account stays fully usable. While such a report is pending, the profile is not queued again.

Where it runs:

- Project upload and re-upload (`ProjectUploadModerationListener`, on `ProjectAfterInsertEvent`): name, description and notes are checked in the request; the images are scanned on the `default` worker 30 s later, because the upload moves the extracted files into place after its listeners ran. Restores from a backup are skipped.
- `PATCH /api/projects/{id}`: name, description and notes when one of them changed; a new screenshot is scanned.
- `POST` / `PATCH /api/studios`: name and description; a new cover is scanned.
- Registration and `PATCH /api/users/me`: username, about and "currently working on" (queued only, see above).
- Avatars are scanned synchronously by `UserRequestValidator` and rejected with 422 instead.

### Age signals

`AgeSignalDetector` holds the patterns (en, de, es, pt, ru, fr, it). Before matching it removes numbers that only look like an age: versions and levels ("1.18+", "version 18+", "level 18+"), years ("2018"), sums ("18+2") and times ("ab 18 Uhr"). A match is ignored right after a negation ("no 18+", "kein NSFW", "без 18+"), "Score:" or "Seite", when a counted noun follows ("18+ levels", "18+ kills", "ab 18 Punkten") and when more digits follow (phone numbers). Add a false positive or a missed phrase to `tests/PhpUnit/Moderation/AgeSignalDetectorTest.php` first.

### Image scans

`ScanContentImages` (queue `default`) sends the site screenshot, the project's own screenshots and then its largest look images to the scanner: at most 20 images between 2 KB and 10 MB. Studios: the cover's `card` WebP variant. The first image at or above the threshold flags the content. An image the scanner refuses (4xx, e.g. a format it can't read) is skipped and logged. If the scanner is down (timeout, connection error, 5xx) or disabled the scan stops, logs a warning and hides nothing; the job does not retry. With `CONTENT_SAFETY_ENABLED=false` (tests) nothing is queued.

## Backfill

```bash
bin/console catro:moderation:flag-adult-content                    # dry run: lists text hits
bin/console catro:moderation:flag-adult-content --apply            # hides them for review
bin/console catro:moderation:flag-adult-content --images --limit 200 --delay 500   # also image scans (dry run)
```

Only visible content is checked, and content with a rejected report is skipped: an admin has already looked at it, so re-runs don't queue it again. Profiles follow the account rule above. `--images` scans the newest `--limit` projects and studios one by one, waiting `--delay` ms between them so the scanner keeps up with live uploads. Run the dry run first and look through the hits before `--apply`.

## Retention: hidden content is deleted after 14 days

Content hidden by a moderator is not kept as free storage. `catro:moderation:purge-hidden` runs daily on the slow scheduler lane (task "Delete content hidden by moderation for 14 days", with `--apply`) and deletes:

- a project, studio or comment that is hidden (`auto_hidden`) and has a report an admin accepted (`accept`, `hide_warn`, or `warn` on already hidden content), when both `auto_hidden_at` and the report's `resolved_at` are at least 14 days old;
- everything of an account banned at least 14 days ago (`fos_user.profile_hidden_at`): its projects, its comments and the studios it is the only active admin of. The account row stays (banned, `profile_hidden = 1`) so the ban holds and the name and e-mail stay taken.

It never deletes content that has a pending report (state new: auto-flagged or community-hidden content nobody reviewed), a pending or approved appeal, or an evidence hold, and nothing of an account with a pending appeal or an evidence hold. Restored content is no longer `auto_hidden`, so it is never selected.

Projects go through `StorageLifecycleService::hardDeleteProject()` (zip, extracted files, screenshots, search index, cascades), studios through `StudioManager::deleteCoverImage()` and a Doctrine remove, comments through a Doctrine remove (their notifications cascade; replies by other users stay). Every deletion is logged (`Moderation purge: deleted {type} {id} of {owner_id}`) and printed.

```bash
bin/console catro:moderation:purge-hidden               # dry run: lists what is due
bin/console catro:moderation:purge-hidden --apply       # deletes it
bin/console catro:moderation:purge-hidden --days=30     # another deadline
```

**Since when.** `program.auto_hidden_at`, `studio.auto_hidden_at`, `user_comment.auto_hidden_at` and `fos_user.profile_hidden_at` are set when the flag goes on (entity setters, the ban cascade) and cleared when it goes off. Ops scripts that hide or ban with raw SQL must set them too, e.g. `UPDATE fos_user SET profile_hidden = 1, profile_hidden_at = NOW() WHERE …`, and clear them when they unhide. A hidden row without a timestamp is stamped by the next `--apply` run, so the 14 days start then; rows hidden before the columns existed therefore get 14 days from the first run after the deploy.

**Evidence hold.** A case reported to the police or Stopline must be kept:

```bash
bin/console catro:moderation:hold <project|studio|comment|user> <id> --note="case 123"
bin/console catro:moderation:hold <type> <id> --release
```

A hold is an `evidence_hold` row in `content_moderation_action` (a later `evidence_released` row lifts it). A hold on a user keeps all their content.

The owner warning (`hide_warn`) on hidden content names the deletion date: the notifications API appends `catro-notifications.moderation-warning.deletion` while the content is still hidden.
