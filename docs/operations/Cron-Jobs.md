# Cron Jobs

One dispatcher, `catrobat:cronjob`, runs every 10 minutes from system cron. It checks each registered job's interval against the `cronjob` table and runs due jobs as child processes. The job list, intervals and timeouts live in `src/System/Commands/DBUpdater/CronJobCommand.php`.

```
# /etc/cron.d/catroweb
*/10 * * * * www-data cd /var/www/share/current && php bin/console catrobat:cronjob --env=prod >> /var/log/catroweb-cronjob.log 2>&1
```

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
