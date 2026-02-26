# Developer Quickstart

This page is the fastest path to a productive Catroweb setup.

## Recommended: Docker Dev Setup

1. Clone your fork and add the upstream remote:

```bash
git clone <your-fork-url>
cd Catroweb
git remote add catroweb git@github.com:Catrobat/Catroweb.git
git checkout develop
git pull catroweb develop
```

2. Start the development stack:

```bash
cd docker
docker compose -f docker-compose.dev.yaml up -d --build
```

3. Open the app:

- App: <http://localhost:8080/>
- phpMyAdmin: <http://localhost:8081/>

4. Run project reset (inside container):

```bash
docker exec -it app.catroweb php bin/console catrobat:reset --hard
```

## Daily Workflow Commands

Run in the project root unless stated otherwise.

### Pull latest changes from upstream `develop`

```bash
git checkout develop
git pull catroweb develop
```

### Run static checks

```bash
yarn test-js
yarn test-css
yarn test-asset
yarn test-php
yarn test-twig
```

or all in one:

```bash
yarn test
```

### Auto-fix supported checks

```bash
yarn fix
```

### Run tests

```bash
docker exec -it app.catroweb bin/phpunit tests
docker exec -it app.catroweb bin/behat
```

## Native Setup (Alternative)

If you explicitly want a non-Docker setup:

- [Setup guide macOS (native, no Docker)](<./Setup-guide-macOS-(native,-no-Docker).md>)
- [Setup guide Ubuntu](./Setup-guide-Ubuntu.md)

## Where to go next

- [Workflow](./Workflow.md)
- [Coding Standard](./Coding-Standard.md)
- [How to Test](./How-To-Test.md)
- [Architecture Overview](./Architecture-Overview.md)
