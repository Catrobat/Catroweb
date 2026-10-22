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
