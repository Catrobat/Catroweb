# How to Deploy

The app is deployed with Deployer (`deploy.php`, one `share` stage). Production infrastructure (compose stacks, nginx vhosts, fail2ban, monitoring) is deployed separately from the private [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure) repo on push to its `main`.

## Production (normal path)

1. Run the **Create Release PR** workflow. It bumps the version in `.env` and drafts a GitHub release.
2. Merge the release PR. The **Deployment** workflow (`.github/workflows/deployment.yaml`) runs on `.env` changes to `main` and deploys. It can also be started manually with a branch or tag.

Deployer's `smoke_test` task checks health on the server after the release is live; a failure fails the deploy but does not roll back (`bin/dep rollback share` does).

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
