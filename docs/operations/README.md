# Operations

- [Cron-Jobs.md](./Cron-Jobs.md) — How the `catrobat:cronjob` dispatcher schedules and tracks all periodic tasks.
- [Protected-Project-Files.md](./Protected-Project-Files.md) — Authorized delivery of project files and the nginx rollout order.
- [Data-consistency.md](./Data-consistency.md) — Read-only data consistency checks and explicitly approved repairs using the application owner.
- [Domain-Change.md](./Domain-Change.md) — Procedure for changing the production domain behind Cloudflare + nginx origin.
- [How-To-Deploy.md](./How-To-Deploy.md) — Release and deploy the app via Deployer.
- [Malware-Scanning.md](./Malware-Scanning.md) — ClamAV-based scanning of uploaded `.catrobat` files before extraction.
- [NSFW-Content-Scanning.md](./NSFW-Content-Scanning.md) — Self-hosted NSFW scanning of profile pictures.
- [Redis-Cache.md](./Redis-Cache.md) — Redis as the production application cache backend (Symfony, Doctrine, trust scores).
- [Secret-Management.md](./Secret-Management.md) — Symfony `.env` hierarchy and how production overrides committed dev defaults.
- [Storage-Architecture.md](./Storage-Architecture.md) — Symlink chain under `public/resources/` for user-generated content storage.

## Production infrastructure

Production deployment configuration — docker-compose stacks for self-hosted services, nginx vhost configs, host-level configs, the CI deploy workflow, and Dependabot for Docker image bumps — lives in the private companion repo [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure). The repo is private; only Catrobat org maintainers have access.
