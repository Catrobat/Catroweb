# Operations

- [Brick-Examples.md](./Brick-Examples.md) — Example projects per brick for the docs, `project_brick_usage` and its backfill command.
- [Code-View.md](./Code-View.md) — The project page's code view, made by the player's code export (Catroid's own code) under Node.
- [Background-Jobs.md](./Background-Jobs.md) — Queues, workers and periodic tasks (Symfony Messenger + Scheduler), async API jobs, operations.
- [Protected-Project-Files.md](./Protected-Project-Files.md) — Authorized delivery of project files and the nginx rollout order.
- [Data-consistency.md](./Data-consistency.md) — Read-only data consistency checks and explicitly approved repairs using the application owner.
- [Domain-Change.md](./Domain-Change.md) — Procedure for changing the production domain behind Cloudflare + nginx origin.
- [How-To-Deploy.md](./How-To-Deploy.md) — Release and deploy the app via Deployer.
- [Email-Providers.md](./Email-Providers.md) — chaining free mail-provider tiers, daily limits per provider, AWS SES as capped paid overflow.
- [Moderation-Actions.md](./Moderation-Actions.md) — Report resolutions (ban, hide + warn, warn, reject) in the admin queue, API and console.
- [Machine-Translation.md](./Machine-Translation.md) — Self-hosted LibreTranslate behind the project and comment translate buttons; caching and supported languages.
- [Malware-Scanning.md](./Malware-Scanning.md) — ClamAV-based scanning of uploaded `.catrobat` files before extraction.
- [Content-Moderation.md](./Content-Moderation.md) — Reports, adults-only text signals and NSFW image scans that hide content for admin review; backfill command.
- [NSFW-Content-Scanning.md](./NSFW-Content-Scanning.md) — Self-hosted NSFW scanning of profile pictures, project images and studio covers.
- [Redis-Cache.md](./Redis-Cache.md) — Redis as the production application cache backend (Symfony, Doctrine, trust scores).
- [Secret-Management.md](./Secret-Management.md) — Symfony `.env` hierarchy and how production overrides committed dev defaults.
- [Storage-Architecture.md](./Storage-Architecture.md) — Symlink chain under `public/resources/` for user-generated content storage.
- [User-Docs.md](./User-Docs.md) — The user docs in user-docs/: sync on deploy, rendering, embeds, media, API and pages.

## Production infrastructure

Production deployment configuration — docker-compose stacks for self-hosted services, nginx vhost configs, host-level configs, the CI deploy workflow, and Dependabot for Docker image bumps — lives in the private companion repo [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure). The repo is private; only Catrobat org maintainers have access.
