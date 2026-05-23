# Operations

Public operations docs for Catroweb maintainers.

- [Cron-Jobs.md](./Cron-Jobs.md) — How the `catrobat:cronjob` dispatcher schedules and tracks all periodic tasks.
- [Domain-Change.md](./Domain-Change.md) — Procedure for changing the production domain behind Cloudflare + nginx origin.
- [How-To-Deploy.md](./How-To-Deploy.md) — Deploying the Catroweb app to dev/prod servers via Deployer-PHP.
- [Malware-Scanning.md](./Malware-Scanning.md) — ClamAV-based scanning of uploaded `.catrobat` files before extraction.
- [NSFW-Content-Scanning.md](./NSFW-Content-Scanning.md) — Self-hosted AI image moderation for user-uploaded media.
- [Redis-Cache.md](./Redis-Cache.md) — Redis as the production application cache backend (Symfony, Doctrine, trust scores).
- [Secret-Management.md](./Secret-Management.md) — Symfony `.env` hierarchy and how production overrides committed dev defaults.
- [Server-Setup.md](./Server-Setup.md) — Historical manual server setup notes (see banner; superseded by the private infra repo).
- [Storage-Architecture.md](./Storage-Architecture.md) — Symlink chain under `public/resources/` for user-generated content storage.

## Production infrastructure

Production deployment configuration — docker-compose stacks for self-hosted services, nginx vhost configs, host-level configs, the CI deploy workflow, and Dependabot for Docker image bumps — lives in the private companion repo [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure). The repo is private; only Catrobat org maintainers have access.
