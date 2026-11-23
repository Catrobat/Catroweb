# How to Deploy

The app is deployed with Deployer (`deploy.php`, one `share` stage). Production infrastructure (compose stacks, nginx vhosts, fail2ban, monitoring) is deployed separately from the private [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure) repo on push to its `main`.

## Production (normal path)

1. Run the **Create Release PR** workflow. It bumps the version in `.env` and drafts a GitHub release.
2. Merge the release PR. The **Deployment** workflow (`.github/workflows/deployment.yaml`) runs on `.env` changes to `main` and deploys. It can also be started manually with a branch or tag.

Deployer's `smoke_test` task checks health on the server after the release is live; a failure fails the deploy but does not roll back (`bin/dep rollback share` does). After a passing smoke test, `deploy:cleanup` keeps the newest 5 releases (`keep_releases`) and deletes older ones.

## User docs

The user docs (`user-docs/`) ship with every release and are synced into the database by the deploy (`catrobat:docs:sync`). A merged change to `user-docs/` goes live without a full deploy: the **User docs publish** workflow (`.github/workflows/docs-publish.yaml`, also startable by hand) runs Deployer's `docs:publish` task, which

1. takes the deploy lock (it fails while a deploy runs),
2. copies `user-docs/` of `main` into `current/user-docs.new/` (unchanged files hard-linked from the live copy) and swaps it in by renaming, so the live release never serves a half-copied folder,
3. runs `bin/console catrobat:docs:sync` in the live release.

It uses the Deployment workflow's connection and secrets and its concurrency group (`deploy-production`), so a publish and a deploy never overlap. GitHub keeps only one pending run per group: a publish queued behind a running deploy replaces an older queued publish (harmless, it checks out the newest `main`), but it would also replace a queued deploy; restart that deploy by hand if it shows as cancelled. A later full deploy replaces the published copy with the same content from git.

Pull requests that change only `user-docs/` run the **User docs** checks (`.github/workflows/docs-check.yaml`, locally `user-docs/check.sh`) and skip the other workflows' jobs (`.github/actions/code-changed`); the required checks report success for the skipped jobs.

## Manual deploy

Set in `.env.local` (credentials from the coordinator; VPN may be required):

```
DEPLOY_HOST=_HOST_
DEPLOY_USER=deploy        # default
DEPLOY_BRANCH=main        # default
SLACK_WEBHOOK=https://hooks.slack.com/services/_KEY_
```

Then `bin/dep deploy share`.

## Production maintenance commands

Run write commands from the application directory as `sudo -n -u www-data /usr/bin/php bin/console ...`, without `nice`, so generated files have the right owner and the invocation matches the sudo rule. See [Data consistency checks](Data-consistency.md).
