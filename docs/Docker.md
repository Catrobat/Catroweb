# Docker Setup

Docker is the recommended way to run Catroweb locally and it is closest to CI.

## Quick Start

```bash
cd docker
docker compose -f docker-compose.dev.yaml up -d --build
```

Open:

- App: <http://localhost:8080/>
- phpMyAdmin: <http://localhost:8081/>

## Dev Services

The dev compose file starts:

- `app.catroweb` (Apache + PHP application)
- `db.catroweb.dev` (MariaDB for dev)
- `db.catroweb.test` (MariaDB for tests)
- `phpmyadmin.catroweb.dev`
- `elasticsearch`
- `chrome.catroweb` (headless browser for Behat)

## Common Commands

### Start / stop

```bash
cd docker
docker compose -f docker-compose.dev.yaml up -d
docker compose -f docker-compose.dev.yaml down
```

### Rebuild app image after dependency or Dockerfile changes

```bash
cd docker
docker compose -f docker-compose.dev.yaml build --no-cache app.catroweb
docker compose -f docker-compose.dev.yaml up -d
```

### Application commands in container

```bash
docker exec -it app.catroweb php bin/console catrobat:reset --hard
docker exec -it app.catroweb bin/phpunit tests
docker exec -it app.catroweb bin/behat -s web-general
```

### Logs

```bash
cd docker
docker compose -f docker-compose.dev.yaml logs -f app.catroweb
```

## Test-Only Stack (CI-like)

```bash
cd docker
docker compose -f docker-compose.test.yaml up -d --build
```

## Notes

- Source directories like `src/`, `assets/`, `templates/`, and `tests/` are mounted for live editing.
- `vendor/` and `node_modules/` are built in the image and are not host-mounted by default.
- If you need those folders locally for IDE indexing, run:

```bash
sh docker/app/import-container-libraries.sh
```

## PhpStorm (Optional)

- Add a Docker Compose run configuration pointing to `docker/docker-compose.dev.yaml`.
- Use it to start/stop containers and inspect logs from the IDE.
