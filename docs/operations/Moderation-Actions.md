# Moderation actions

A moderator resolves a content report with one of four actions. All of them go through `App\Moderation\ReportProcessor::resolveReport()`, which also resolves every other pending report on the same content, notifies the reporters, invalidates their trust scores and writes a `content_moderation_action` audit row.

| Action      | Admin queue button | Content                                                                                               | Owner                                       | Reports  | Audit action                                        |
| ----------- | ------------------ | ----------------------------------------------------------------------------------------------------- | ------------------------------------------- | -------- | --------------------------------------------------- |
| `accept`    | Hide + ban owner   | hidden                                                                                                | banned (profile hidden, all content hidden) | accepted | `admin_confirmed`, plus `admin_banned` on the owner |
| `hide_warn` | Hide + warn        | hidden; a reported **user** is not banned, their `about` and `currently_working_on` texts are cleared | warning notification, not banned            | accepted | `admin_hide_warned` (user: `admin_warned`)          |
| `warn`      | Warn               | unchanged (a hidden item or banned user stays hidden)                                                 | warning notification, not banned            | accepted | `admin_warned`                                      |
| `reject`    | Reject             | restored unless other active reports exist                                                            | untouched                                   | rejected | `admin_reversed`                                    |

Use `hide_warn` for a first offense (for example suggestive content by a child) and `accept` for clear abuse.

Hidden content with an accepted report, and everything of a banned account, is deleted 14 days later unless it is appealed or held as evidence: see [Retention](./Content-Moderation.md#retention-hidden-content-is-deleted-after-14-days).

The warning is a `ModerationNotification` with `moderation_action = 'admin_warning'` (`'admin_hide_warning'` for `hide_warn` on hidden content). Its text is the moderator's message; when the message is empty, the notifications API shows the translated default `catro-notifications.moderation-warning.message`. For `admin_hide_warning`, while the content is hidden, the API appends that it will be deleted 14 days after it was hidden (`catro-notifications.moderation-warning.deletion`).

## Where the actions are available

- Admin moderation queue (`/admin/moderation/report/list`): the warn buttons share an optional message field.
- The report's **Show** page (`/admin/moderation/report/{id}/show`) has the same buttons above the reported content: what an automatic flag found comes first, outlined in red (flagged images with their NSFW score, the texts containing a flagged signal), then a project's other images, sounds and texts, as on Approve Projects (other content types: only the flags and a link to the content). The flags come from `content_report.flagged_assets`; reports from before it are read from their note (`image "<file>" has NSFW score …`, `adults-only signal "…"`).
- API: `PUT /api/moderation/reports/{id}/resolve` with `{"action": "hide_warn", "message": "..."}` (admin only).
- Console, for scripted moderation (never close reports with raw SQL, it skips notifications, audit and trust caches):

```bash
bin/console catro:moderation:resolve <content_type> <content_id> <accept|reject|hide_warn|warn> --admin=<admin username> [--message="..."]
```

`content_type` is `project`, `user`, `studio` or `comment`. The command resolves all pending reports on that content and records the resolution for the given admin.
