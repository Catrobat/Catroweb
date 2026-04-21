# Cron Jobs

## Architecture

Catroweb uses a single dispatcher command (`catrobat:cronjob`) that manages all
periodic tasks. It runs every 10 minutes via system cron and internally tracks
when each sub-job last ran using the `cronjob` database table.

```
System cron (every 10 min)
  → catrobat:cronjob
    → Check each job: has interval elapsed since last run?
      → Yes → spawn child process (bin/console <command>)
      → No → skip (< 1ms)
```

### System Cron Entry

```
# /etc/cron.d/catroweb
*/10 * * * * www-data cd /var/www/share/current && php bin/console catrobat:cronjob --env=prod >> /var/log/catroweb-cronjob.log 2>&1
```

### Database Schema

The `cronjob` table tracks execution state:

| Column          | Type        | Purpose                            |
| --------------- | ----------- | ---------------------------------- |
| `name`          | STRING (PK) | Unique job identifier              |
| `state`         | STRING      | `idle`, `run`, or `timeout`        |
| `cron_interval` | STRING      | e.g., `1 day`, `6 hours`, `1 week` |
| `start_at`      | DATETIME    | Last execution start               |
| `end_at`        | DATETIME    | Last execution end                 |
| `result_code`   | INT         | Exit code (0 = success)            |

Jobs only appear in the admin UI after their first execution.

## Registered Jobs

### Achievements (weekly/yearly)

| Job                         | Command                                                   | Interval |
| --------------------------- | --------------------------------------------------------- | -------- |
| Diamond user achievements   | `catrobat:workflow:achievement:diamond_user`              | 1 week   |
| Gold user achievements      | `catrobat:workflow:achievement:gold_user`                 | 1 week   |
| Silver user achievements    | `catrobat:workflow:achievement:silver_user`               | 1 week   |
| Bronze user achievements    | `catrobat:workflow:achievement:bronze_user`               | 1 year   |
| Verified developer (silver) | `catrobat:workflow:achievement:verified_developer_silver` | 1 week   |
| Verified developer (gold)   | `catrobat:workflow:achievement:verified_developer_gold`   | 1 week   |
| Perfect profile             | `catrobat:workflow:achievement:perfect_profile`           | 1 year   |
| Translation achievements    | `catrobat:workflow:achievement:translation`               | 1 month  |

### Storage and Cleanup (daily/weekly)

| Job                             | Command                           | Interval |
| ------------------------------- | --------------------------------- | -------- |
| Delete expired projects         | `catrobat:storage:lifecycle`      | 1 day    |
| Clean extracted project files   | `catrobat:clean:extracts`         | 1 day    |
| Garbage collect orphaned assets | `catrobat:gc-assets`              | 1 week   |
| Clean unverified users          | `catrobat:clean:unverified-users` | 1 day    |

### Ranking and Content (6h/daily/weekly)

| Job                            | Command                                            | Interval |
| ------------------------------ | -------------------------------------------------- | -------- |
| Update popularity scores       | `catrobat:update:popularity`                       | 6 hours  |
| Update user rankings           | `catrobat:update:userranking`                      | 1 day    |
| Refresh project extensions     | `catrobat:workflow:project:refresh_extensions`     | 1 year   |
| Update random project category | `catrobat:workflow:update_random_project_category` | 1 week   |
| Detect broken projects         | `catrobat:workflow:detect_broken_projects`         | 1 day    |

### Maintenance (daily/weekly/monthly)

| Job                              | Command                              | Interval |
| -------------------------------- | ------------------------------------ | -------- |
| Archive log files                | `catrobat:logs:archive`              | 1 day    |
| Clean old log files              | `catrobat:clean:logs`                | 1 week   |
| Clean compressed project files   | `catrobat:clean:compressed`          | 1 week   |
| Re-sanitize user content         | `catro:moderation:sanitize-existing` | 1 month  |
| Trim machine translation storage | `catrobat:translation:trim-storage`  | 1 month  |

## Execution Model

Jobs run **sequentially** in the order defined in `CronJobCommand.php`. If a job
hangs or times out, all subsequent jobs in that cycle are delayed.

Each job has a timeout (configured per-job). On timeout, the job is marked with
`state = 'timeout'` and skipped on the next cycle.

### Stuck Jobs

A job can get stuck in `state = 'run'` if the process crashes without cleanup
(e.g., server reboot during execution). Stuck jobs block themselves from re-running.

**To fix via admin UI:** `/admin/system/cron-job/list` → reset the job.

**To fix via SQL:**

```sql
UPDATE cronjob SET state='idle', result_code=0, end_at=NOW() WHERE state='run';
```

## Admin Interface

The cron job admin is at `/admin/system/cron-job/list`:

- View all registered jobs, their state, and last execution times
- Manually trigger all jobs
- Reset stuck jobs

## Adding a New Cron Job

1. Create the Symfony command in `src/System/Commands/`
2. Add a `runCronJob()` call in `CronJobCommand::execute()`:
   ```php
   $this->runCronJob(
     'Unique job name',                          // Must be unique!
     ['bin/console', 'your:command:name'],
     ['timeout' => self::ONE_HOUR_IN_SECONDS],
     '1 day',                                    // Interval
     $output
   );
   ```
3. Deploy — the job appears in the admin after its first run

**Important:** Job names must be unique. Duplicate names cause jobs to share a
database row, and only one command will actually execute.

## Logs

- Application cron log: `/var/log/catroweb-cronjob.log`
- Individual command output is captured by the dispatcher
