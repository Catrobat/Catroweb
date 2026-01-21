# Catroweb - Project Guide for Claude

## Overview

Catroweb is the share/communication platform for the Catrobat community. It's a Symfony-based PHP application with a modern frontend using Webpack Encore.

## Tech Stack

- **Backend**: PHP 8.5, Symfony 7.4
- **Frontend**: JavaScript (ES6+), SCSS, Webpack Encore
- **Database**: MariaDB 10.11
- **Search**: Elasticsearch 7.17
- **CSS Framework**: Bootstrap 5, Material Design Components
- **Package Managers**: Composer (PHP), npm (JS)

## Key Commands

### Build & Development

```bash
# Install dependencies
composer install
npm install

# Development build
npm run dev

# Production build
npm run build

# Watch mode (auto-rebuild on changes)
npm run watch

# Development server with hot reload
npm run dev-server
```

### Testing & Linting

```bash
# Run all tests
npm test

# Individual test commands
npm run test-js      # ESLint
npm run test-css     # Stylelint for SCSS
npm run test-asset   # Prettier
npm run test-php     # PHP CS Fixer
npm run test-twig    # Twig CS Fixer

# Fix commands
npm run fix          # Fix all
npm run fix-js       # Fix JS
npm run fix-css      # Fix SCSS
npm run fix-asset    # Fix assets
npm run fix-php      # Fix PHP
npm run fix-twig     # Fix Twig
```

### Symfony Console

```bash
# Reset database with sample data (limit 20 projects)
npm run reset
# or
bin/console catro:reset --hard --limit 20
```

## Docker Setup

### Start Development Environment

```bash
# Build and start all containers
docker compose -f docker/docker-compose.dev.yaml up -d

# Build with no cache (after dependency changes)
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
docker compose -f docker/docker-compose.dev.yaml up -d
```

### Container Services

| Service                 | Port | Description                 |
| ----------------------- | ---- | --------------------------- |
| app.catroweb            | 8080 | Main application            |
| db.catroweb.dev         | 3306 | MariaDB (dev)               |
| db.catroweb.test        | 3306 | MariaDB (test)              |
| phpmyadmin.catroweb.dev | 8081 | phpMyAdmin                  |
| elasticsearch           | 9200 | Elasticsearch               |
| chrome.catroweb         | 9222 | Headless Chrome (E2E tests) |

### Docker Volumes

The docker-compose.dev.yaml mounts these directories for live editing:

- `assets/` - Frontend assets (SCSS, JS)
- `src/` - PHP source code
- `templates/` - Twig templates
- `config/` - Symfony configuration
- `tests/` - Test files

**Note**: `node_modules/` and `vendor/` are NOT shared - they're built inside the container. After npm/composer changes, rebuild the container.

## Project Structure

```
Catroweb/
├── assets/                 # Frontend source files
│   ├── Layout/            # Global SCSS (Variables, Base)
│   ├── Components/        # Reusable component styles
│   ├── User/              # User-related pages
│   ├── Project/           # Project-related pages
│   └── Themes/            # Theme-specific overrides
├── config/                # Symfony configuration
├── docker/                # Docker configuration
│   ├── Dockerfile
│   └── docker-compose.dev.yaml
├── migrations/            # Database migrations
├── public/                # Web root
│   └── build/            # Compiled assets (generated)
├── src/                   # PHP source code
├── templates/             # Twig templates
├── tests/                 # Test files
├── translations/          # i18n files
├── webpack.config.js      # Webpack Encore config
└── package.json           # npm dependencies
```

## SCSS/Sass

- Uses **Dart Sass** (`sass` package, v1.97.2)
- 61 SCSS files in `assets/` directory
- sass-loader 16+ auto-detects the sass compiler
- Bootstrap 5 variables imported via `assets/Layout/Variables.scss`

### Deprecation Warnings

Bootstrap 5.x uses some deprecated Sass features (global functions like `mix()`, `shade-color()`). These show warnings but don't break the build. They'll be fixed in Bootstrap 6.

## Themes

The application supports 9 themes (configured in webpack.config.js):

1. pocketcode (default)
2. arduino
3. create@school
4. embroidery
5. luna
6. phirocode
7. pocketalice
8. pocketgalaxy
9. mindstorms

## Environment Files

- `.env` - Base environment variables
- `.env.dev` - Development overrides
- `.env.test` - Test overrides
- `.env.prod` - Production overrides

## Database

Default dev credentials (from docker-compose):

- Host: `db.catroweb.dev`
- Database: `catroweb_dev`
- User: `root`
- Password: `root`

## Common Issues

### After changing package.json

```bash
# Local development
rm -rf node_modules package-lock.json
npm install

# Docker - rebuild container
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
docker compose -f docker/docker-compose.dev.yaml up -d
```

### After changing composer.json

```bash
# Local
composer install

# Docker - rebuild container
docker compose -f docker/docker-compose.dev.yaml build --no-cache app.catroweb
```

### Check if app is running

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080
```

## PHP Static Analysis & Testing

When making PHP changes, run these tools before committing:

```bash
# PHP CS Fixer - code style (run first, auto-fixes issues)
docker exec app.catroweb bin/php-cs-fixer fix
docker exec app.catroweb bin/php-cs-fixer fix src/Path/To/File.php

# PHPStan - static analysis (level max)
docker exec app.catroweb bin/phpstan analyse
docker exec app.catroweb bin/phpstan analyse src/Path/To/File.php

# Psalm - type checking
docker exec app.catroweb bin/psalm
docker exec app.catroweb bin/psalm src/Path/To/File.php

# PHPUnit - unit tests
docker exec app.catroweb bin/phpunit --filter ClassName
```

**All four tools must pass before merging.**

## Behat Testing

### Running Behat Tests

```bash
# Run all tests for a specific suite
docker exec app.catroweb bin/behat -f pretty -s web-admin

# Run a specific feature file
docker exec app.catroweb bin/behat -f pretty tests/BehatFeatures/web/admin/login_basics.feature

# Run a specific scenario by line number
docker exec app.catroweb bin/behat -f pretty "tests/BehatFeatures/web/admin/featured_programs.feature:206"
```

## Git Workflow

- Main branch: `develop`
- Feature branches merged via PR
- CI runs on GitHub Actions (Static analysis, Dynamic analysis)

## API

- OpenAPI spec in `src/Api/OpenAPI/`
- Generate API client: `npm run generate-api`
