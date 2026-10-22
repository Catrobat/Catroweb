# Developer Quickstart

Docker is the recommended setup and closest to CI. Native alternatives: [Ubuntu](Setup-guide-Ubuntu.md), [macOS](<./Setup-guide-macOS-(native,-no-Docker).md>).

## Docker setup

```bash
git clone <your-fork-url> && cd Catroweb
git remote add catroweb git@github.com:Catrobat/Catroweb.git
cd docker
docker compose -f docker-compose.dev.yaml up -d --build
docker exec -it app.catroweb php bin/console catrobat:reset --hard
```

- App: <http://localhost:8080/>, phpMyAdmin: <http://localhost:8081/>, Mailpit (all dev mail): <http://localhost:8025/>
- `src/`, `assets/`, `templates/`, `config/` and `tests/` are mounted live. `vendor/` and `node_modules/` are built in the image; for IDE indexing copy them out with `sh docker/app/import-container-libraries.sh`.
- After dependency or Dockerfile changes: `docker compose -f docker-compose.dev.yaml build --no-cache app.catroweb`, then `up -d`.
- Logs: `docker compose -f docker-compose.dev.yaml logs -f app.catroweb`.
- CI-like test-only stack: `docker compose -f docker-compose.test.yaml up -d --build`.
- Apple Silicon: if the build fails the PHP version check, Sury's arm64 packages are behind amd64. Add `- PHP_MIN_VERSION=8.5.11` under `build.args` of `app.catroweb` in `docker-compose.dev.yaml` locally (don't commit it) until they catch up.

## Daily commands

```bash
git pull catroweb main
yarn test            # all static checks (test-js, test-css, test-asset, test-php, test-twig)
yarn fix             # auto-fix
docker exec -it app.catroweb bin/phpunit tests
docker exec -it app.catroweb bin/behat -s web-general     # always pass a suite
```

## Next

- [Workflow](../contributor-guide/Workflow.md)
- [Coding Standard](../contributor-guide/Coding-Standard.md)
- [How to Test](../contributor-guide/How-To-Test.md)
- [Architecture Overview](../architecture/Architecture-Overview.md)
