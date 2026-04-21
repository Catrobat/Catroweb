# Storage Architecture

## Overview

Catroweb stores user-generated content (project files, screenshots, assets) on disk.
The application code references `public/resources/` which is a symlink chain that can
point to any underlying storage — local disk, NVMe, HDD, or network mount.

## Symlink Chain

```
/var/www/share/current/public/resources
  → ../../../shared/public/resources     (Deployer shared symlink, survives deploys)
    → /data/resources                    (actual storage location)
```

The application never needs to know where data physically lives. To move storage
to a different disk, move the data and update the final symlink.

## Resource Directories

| Directory      | Content                                               | Growth Rate          |
| -------------- | ----------------------------------------------------- | -------------------- |
| `programs/`    | `.catrobat` project files (ZIP)                       | High — every upload  |
| `extract/`     | Extracted project contents                            | High — cleaned daily |
| `assets/`      | Deduplicated images/sounds (SHA256 content-addressed) | Medium               |
| `screenshots/` | Project screenshots (PNG, WebP, AVIF variants)        | Medium               |
| `thumbnails/`  | 80x80 project thumbnails                              | Medium               |
| `images/`      | User avatars, studio covers                           | Low                  |
| `featured/`    | Featured project/program banners                      | Low                  |
| `media/`       | Media library files                                   | Static               |
| `tmp/`         | Temporary upload staging                              | Transient            |

## Content-Addressable Store (Deduplication)

`src/Storage/ContentAddressableStore.php` stores deduplicated assets using SHA256 hashes:

```
assets/ab/cd/abcdef1234...   (first 2 hex chars / next 2 / full hash)
```

`src/Project/ProjectDeduplicationService.php` manages reference counting:

- Deduplicates `images/` and `sounds/` from uploaded projects
- `ProjectAsset` entity tracks each unique file with a reference count
- `ProjectAssetMapping` links projects to their shared assets
- `catrobat:gc-assets` cron job garbage-collects orphaned assets (refCount <= 0)

## Disk Pressure and Project Retention

`src/Storage/StorageLifecycleService.php` adjusts project retention based on disk usage:

| Disk Usage | Effect                                           |
| ---------- | ------------------------------------------------ |
| < 70%      | Normal retention (90-365 days depending on tier) |
| 70-85%     | Retention halved                                 |
| 85-95%     | Retention quartered                              |
| > 95%      | Uploads paused                                   |

The `catrobat:storage:lifecycle` cron job runs daily and deletes expired projects.

## Admin Maintenance Page

`/admin/system/maintenance/list` shows:

- Disk usage for all mounted volumes (system, data, backup)
- Storage pressure level based on the disk where projects actually reside
- RAM usage
- Removable objects (compressed files, logs)

The pressure indicator follows symlinks to detect which physical disk holds the
project storage directory, so it reports correctly even when storage is on a
separate mount.

## Configuration

All storage paths are configured in `config/services.php`:

```php
$container->setParameter('catrobat.file.storage.dir', '%kernel.project_dir%/public/resources/programs/');
$container->setParameter('catrobat.file.extract.dir', '%kernel.project_dir%/public/resources/extract/');
$container->setParameter('catrobat.file.assets.dir', '%kernel.project_dir%/public/resources/assets/');
$container->setParameter('catrobat.screenshot.dir', '%catrobat.pubdir%resources/screenshots/');
$container->setParameter('catrobat.thumbnail.dir', '%catrobat.pubdir%resources/thumbnails/');
```

## Backup Strategy

Resources should be backed up via `rsync` to a separate disk or remote storage.
A daily cron job mirrors the data directory and dumps the database:

```bash
# Mirror resources
rsync -aH --delete /data/resources/ /backup/resources/

# Database dump (14-day rolling retention)
mysqldump -u root --single-transaction catroweb | gzip > /backup/db/catroweb_$(date +%Y%m%d).sql.gz
find /backup/db/ -name "catroweb_*.sql.gz" -mtime +14 -delete
```

### Restore Procedures

**Restore resources:**

```bash
rsync -aHAX /backup/resources/ /data/resources/
```

**Restore database:**

```bash
gunzip < /backup/db/catroweb_YYYYMMDD.sql.gz | mysql -u root catroweb
```

## Monitoring

- **SMART disk health**: `smartctl -H /dev/sdX` (weekly cron recommended)
- **RAID status**: `cat /proc/mdstat` — look for `[UU]` (healthy) vs `[U_]` (degraded)
- **Disk space**: `df -h /data /backup /`
- **Backup logs**: `/var/log/backup-resources.log`
