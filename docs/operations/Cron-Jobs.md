# Cron Jobs

One dispatcher, `catrobat:cronjob`, runs every 10 minutes from system cron. It checks each registered job's interval against the `cronjob` table and runs due jobs as child processes. The job list, intervals and timeouts live in `src/System/Commands/DBUpdater/CronJobCommand.php`.

```
# /etc/cron.d/catroweb
*/10 * * * * www-data cd /var/www/share/current && php bin/console catrobat:cronjob --env=prod >> /var/log/catroweb/cronjob.log 2>&1
```

The log must go to a directory `www-data` can write, and not into the app's `var/log` (Symfony maintenance may empty it). `/var/log/catroweb/` is created `www-data`-owned for this. `/var/log` itself is `root:syslog`: a redirect there fails and bash never starts the command, so from 2026-04-21 to 2026-11-12 no job ran at all, silently.

## Behavior to know

- Jobs run **sequentially**; a hanging job delays everything after it in that cycle.
- On timeout a job is marked `timeout` and skipped next cycle.
- A crash or reboot mid-run leaves a job stuck in `state = 'run'`, which blocks it from re-running. Reset it at `/admin/system/cron-job/list`, or:

  ```sql
  UPDATE cronjob SET state='idle', result_code=0, end_at=NOW() WHERE state='run';
  ```

- Jobs appear in the admin UI only after their first run.

## Adding a job

Add a `runCronJob('Unique job name', ['bin/console', 'your:command'], ['timeout' => ...], '1 day', $output)` call in `CronJobCommand::execute()`. **Names must be unique**: duplicates share one database row and only one of the commands runs.
