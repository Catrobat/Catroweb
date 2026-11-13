# Storage Architecture

User-generated content lives under `public/resources/`, a symlink chain so the app never needs to know which disk holds the data:

```
/var/www/share/current/public/resources
  → ../../../shared/public/resources     (Deployer shared dir, survives deploys)
    → /data/resources                    (actual storage)
```

To move storage to another disk, move the data and update the last symlink. Paths are configured in `config/services.php`; images and sounds of uploaded projects are deduplicated into a SHA-256 content-addressed store (`assets/`), garbage-collected by the `catrobat:gc-assets` cron job. Raw project, extract and CAS paths must be denied by the web server; files are delivered through [authorized routes](Protected-Project-Files.md).

## Disk pressure

`StorageLifecycleService` shortens project retention as the disk holding the projects fills: halved at 70%, quartered at 85%, uploads paused above 95%. `catrobat:storage:lifecycle` deletes expired projects daily. `/admin/system/maintenance/list` shows disk usage and the pressure level (it follows the symlinks to the real disk).

## Projects with lost files

The `.catrobat` zip is only a cache, rebuilt from the extracted directory. `catrobat:workflow:detect_broken_projects` (cron) sets `has_missing_files` when a project has neither a zip nor an extracted `code.xml`, and clears it once files are back; a re-upload clears it right away. Flagged projects are hidden from everyone but their owner and admins (lists, search, featured, studios, detail page), without touching `visible`.

`catrobat:projects:notify-lost-files [--dry-run]` is a one-off: run the detector first, then it sends each affected owner one in-app notice listing their projects (re-runs only announce projects not in an earlier notice). `catrobat:storage:lifecycle` deletes a project still flagged 14 days after its notice. The detector's bulk update bypasses the search index: run `fos:elastica:populate` after it so search hides flagged projects too.

## Backup and restore

Daily cron:

```bash
rsync -aH --delete /data/resources/ /backup/resources/
mysqldump -u root --single-transaction catroweb | gzip > /backup/db/catroweb_$(date +%Y%m%d).sql.gz
find /backup/db/ -name "catroweb_*.sql.gz" -mtime +14 -delete
```

Restore:

```bash
rsync -aHAX /backup/resources/ /data/resources/
gunzip < /backup/db/catroweb_YYYYMMDD.sql.gz | mysql -u root catroweb
```

Monitoring: `smartctl -H /dev/sdX` (weekly), `cat /proc/mdstat` (`[UU]` healthy, `[U_]` degraded), `df -h /data /backup /`, `/var/log/backup-resources.log`.
