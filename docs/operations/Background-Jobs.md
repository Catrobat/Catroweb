# Background jobs

Slow work runs outside web requests, on workers that each consume one queue, so one kind of job never waits for another. The queues are rows in the `messenger_messages` table (Symfony Messenger, Doctrine transport); there is no broker to run.

| Queue / worker               | What runs there                                                                                               |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------- |
| `scratch`                    | Scratch imports (`ConvertScratchProject`), one at a time                                                      |
| `uploads`                    | Completing resumable uploads that asked for it (`CompleteProjectUpload`)                                      |
| `default`                    | Mail delivery (`SendMail`), follower notifications for a new project, code view exports                       |
| `scheduler_maintenance`      | Fast periodic tasks (popularity, rankings, achievements, small clean-ups)                                     |
| `scheduler_maintenance_slow` | Periodic sweeps that may take long (retention, asset GC, broken projects, re-sanitizing, unverified accounts) |

Configuration: `config/packages/messenger.php` (queues and routing), `src/System/Cron/CronTasks.php` (periodic tasks). In tests every queue is `sync://`: a job runs inside the request that queues it.

## Jobs a client waits for

The Scratch import (`POST /api/projects/scratch/{id}`) and the completion of a resumable upload (`POST /api/projects/uploads/{id}/complete`) answer `202` with a job when the request sends `Prefer: respond-async` (RFC 7240). The client polls `GET /api/jobs/{id}` until `state` is `done` (load `project_id`) or `failed` (show `error_message`). Without the header they work synchronously as before, so released apps keep working. Job rows (`background_job`) are deleted a week after they finish.

## Periodic tasks

Each scheduler lane ticks every 5 minutes and runs its tasks that are due: a task is due when its interval has passed since its last successful start, which is stored in the `cronjob` table. Deploys and worker restarts therefore don't reset the clock, and a failed task is retried at the next tick. Each task runs as its own `bin/console` process with a timeout; a task stuck in `run` (crash, reboot) is recovered after twice its timeout.

Add a task: one line in `CronTasks::all()`, with a unique, stable name (it is the `cronjob` table's key). Long sweeps go on the slow lane.

Run by hand: `bin/console catrobat:cronjob` runs every due task of both lanes, `bin/console catrobat:cronjob "Update user rankings"` runs one task now.

## Production

The workers are systemd services (Catrobat/infrastructure `host-config/systemd/catroweb-worker@.service`, one instance per queue above, grouped by `catroweb-workers.target`):

```bash
systemctl status 'catroweb-worker@*'
journalctl -u catroweb-worker@scratch -f
```

- **Deploys** restart them with `systemctl restart catroweb-workers.target` (`deploy.php`, task `restart:workers`). Not `messenger:stop-workers`: its signal is stored in the release's own `var/cache`, which workers started from the previous release never read. systemd sends SIGTERM and a worker finishes its current job before it exits.
- **Failed jobs** (retries used up) move to the `failed` queue. See and retry or delete them at `/admin/system/background-tasks/list`, or with `bin/console messenger:failed:show` / `messenger:failed:retry`.
- **Monitoring**: the Background tasks admin page; Grafana's "Background jobs" row; `catroweb-watchdog` alerts when a worker is not running, when jobs wait longer than 15 minutes, and when a job failed for good.
- A worker restarts itself every hour (`--time-limit`) and after a job that took it over 512 MB (`--memory-limit`).

## Locally

`bin/console messenger:consume default scratch uploads scheduler_maintenance scheduler_maintenance_slow -vv` runs all queues in one terminal. Without a worker, async requests stay `queued`.
