# Secret Management

## Overview

Catroweb uses Symfony's `.env` file hierarchy for configuration. The committed `.env` file contains
**intentionally insecure development-only defaults** so that developers can run the project locally
without any extra setup. These defaults **must be overridden** in production.

## Environment File Hierarchy

Symfony loads environment files in this order (later files override earlier ones):

1. `.env` -- committed, contains dev defaults
2. `.env.local` -- **gitignored**, local overrides for any environment
3. `.env.{APP_ENV}` -- committed, environment-specific defaults (e.g., `.env.prod`)
4. `.env.{APP_ENV}.local` -- **gitignored**, environment-specific local overrides

Real environment variables always take precedence over all `.env` files.

## Secrets That Must Be Overridden in Production

| Variable               | Dev Default                           | How to Override                   |
| ---------------------- | ------------------------------------- | --------------------------------- |
| `APP_SECRET`           | `93055246cfa39f62f5be97928084989a`    | `.env.prod.local` or real env var |
| `JWT_PASSPHRASE`       | `catroweb`                            | `.env.prod.local` or real env var |
| `DATABASE_URL`         | `pdo-mysql://root:root@localhost/...` | `.env.prod.local` or real env var |
| `DATABASE_PASSWORD`    | `root`                                | `.env.prod.local` or real env var |
| `GOOGLE_CLIENT_SECRET` | `'secret'`                            | `.env.prod.local` or real env var |
| `MAILER_DSN`           | `null://null`                         | `.env.prod.local` or real env var |

## Production Setup

### 1. Create `.env.prod.local` on the Server

This file is shared between deployments (configured in `deploy.php` as a shared file).

```bash
# On the production server, in the shared directory:
nano /var/www/share/shared/.env.prod.local
```

Example contents:

```env
APP_SECRET=<generate-with: php -r "echo bin2hex(random_bytes(16));">
JWT_PASSPHRASE=<generate-with: openssl rand -base64 32>
DATABASE_URL=pdo-mysql://catroweb:<db-password>@localhost/catroweb
DATABASE_PASSWORD=<db-password>
GOOGLE_CLIENT_SECRET=<from-google-console>
MAILER_DSN=<your-mailer-dsn>
```

### 2. Generate JWT Keys with the Production Passphrase

The JWT keys in `.jwt/` (also a shared directory) must be generated with the **production** passphrase:

```bash
mkdir -p .jwt
openssl genpkey -out .jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:<YOUR_JWT_PASSPHRASE>
openssl pkey -in .jwt/private.pem -out .jwt/public.pem -pubout -passin pass:<YOUR_JWT_PASSPHRASE>
chmod 600 .jwt/private.pem
chmod 644 .jwt/public.pem
```

**Important:** The `JWT_PASSPHRASE` in `.env.prod.local` must match the passphrase used to generate the keys.

### 3. Generate a Secure `APP_SECRET`

```bash
php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"
```

This secret is used by Symfony for CSRF tokens, remember-me cookies, and other security features.

## Safety Checks

### Runtime Warning

The `DefaultSecretsWarningListener` (in `src/Security/`) logs a `CRITICAL` message on the first
request if it detects default dev secrets in a non-dev environment. Monitor your logs for:

```
SECURITY: Default development secrets detected in "prod" environment: APP_SECRET, JWT_PASSPHRASE
```

### CI Check

The `bin/check-default-secrets` script runs in CI (Static Analysis workflow) and verifies:

- Committed `.env` files contain only known dev defaults (not real secrets)
- `.env.prod` does not define sensitive secrets (those belong in `.env.prod.local`)
- `.env.local` is properly gitignored

### Running Locally

```bash
bin/check-default-secrets
```

## What NOT to Do

- **Never** put production secrets in `.env`, `.env.prod`, or any other committed file
- **Never** commit `.env.local` or `.env.prod.local`
- **Never** use the default `JWT_PASSPHRASE=catroweb` in production
- **Never** use the default `APP_SECRET` in production
- **Never** hardcode the production database password in committed files

## Deployer Integration

The `deploy.php` file configures these as shared files (persisted across deployments):

```php
add('shared_files', [
    '.env.prod.local',
    'google_cloud_key.json',
    '.dkim/private.key',
]);

set('shared_dirs', [
    '.jwt',  // JWT keys persist across deployments
]);
```

This means `.env.prod.local` and `.jwt/` are symlinked from the shared directory into each release,
so production secrets only need to be set up once.
