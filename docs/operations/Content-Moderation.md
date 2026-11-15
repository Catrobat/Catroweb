# Content moderation pipeline

Catroweb is a platform for children. Content is hidden from everyone but its owner in three ways:

| Path                    | Trigger                                                                                    | Code                                                         |
| ----------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------------ |
| Community reports       | Reports whose trust-weighted score passes the threshold                                    | `ReportProcessor` → `AutoModerationService::autoHideContent` |
| Adults-only text signal | "18+", "+18", "nsfw", "Ab 18", "solo adultos", "только для взрослых", … in a saved text    | `AgeSignalDetector` → `AutoModerationService::flagAgeSignal` |
| NSFW image              | An image at or above `CONTENT_SAFETY_THRESHOLD` (see [NSFW scanning](./NSFW-Content-Scanning.md)) | `ScanContentImagesHandler` → `AutoModerationService::flagForReview` |

Profanity is a separate concern: `TextSanitizer` censors words from `config/moderation/wordlists/`. Age signals are not censored; the text is saved as written so the reviewer sees it.

## Automatic flags

`AutoModerationService::flagForReview` hides the content (`auto_hidden`, or `profile_hidden` for a user, which also hides the user's projects, studios and comments), writes an `auto_hidden` audit entry with the reason in its note, and adds a report **without reporter** to the moderation queue (`/admin/moderation/report/list`). The owner gets a notification and may appeal; admins get a notification too. Accepting or rejecting the report in the queue works as for a community report. Content that is already hidden is left alone, so nothing is queued twice.

Where it runs:

- Project upload and re-upload (`ProjectUploadModerationListener`, on `ProjectAfterInsertEvent`): name, description and notes are checked in the request; the images are scanned on the `default` worker.
- `PATCH /api/projects/{id}`: name, description and notes when one of them changed; a new screenshot is scanned.
- `POST` / `PATCH /api/studios`: name and description; a new cover is scanned.
- Registration and `PATCH /api/user`: username, about and "currently working on".
- Avatars are scanned synchronously by `UserRequestValidator` and rejected with 422 instead.

### Age signals

`AgeSignalDetector` holds the patterns (en, de, es, pt, ru, fr, it). Before matching it removes numbers that only look like an age: versions and levels ("1.18+", "version 18+", "level 18+"), years ("2018"), sums ("18+2"), score gains ("+18 points") and times ("ab 18 Uhr"). Add a false positive or a missed phrase to `tests/PhpUnit/Moderation/AgeSignalDetectorTest.php` first.

### Image scans

`ScanContentImages` (queue `default`) sends the site screenshot, the project's own screenshots and then its largest look images to the scanner: at most 20 images between 2 KB and 10 MB. Studios: the cover's `card` WebP variant. The first image at or above the threshold flags the content. If the scanner is down or disabled the scan stops, logs a warning and hides nothing; the job does not retry. With `CONTENT_SAFETY_ENABLED=false` (tests) nothing is queued.

## Backfill

```bash
bin/console catro:moderation:flag-adult-content                    # dry run: lists text hits
bin/console catro:moderation:flag-adult-content --apply            # hides them for review
bin/console catro:moderation:flag-adult-content --images --limit 200 --delay 500   # also image scans (dry run)
```

Only visible content is checked. `--images` scans the newest `--limit` projects and studios one by one, waiting `--delay` ms between them so the scanner keeps up with live uploads. Run the dry run first and look through the hits before `--apply`.
