# Secret Management

The committed `.env` holds **intentionally insecure dev-only defaults** so the project runs locally without setup. Production overrides them in `.env.prod.local` (gitignored, a Deployer shared file) or real environment variables, which win over every `.env` file. Never put production secrets in a committed file.

## Secrets That Must Be Overridden in Production

| Variable               | Dev Default                           | How to Override                   |
| ---------------------- | ------------------------------------- | --------------------------------- |
| `APP_SECRET`           | `93055246cfa39f62f5be97928084989a`    | `.env.prod.local` or real env var |
| `JWT_PASSPHRASE`       | `catroweb`                            | `.env.prod.local` or real env var |
| `DATABASE_URL`         | `pdo-mysql://root:root@localhost/...` | `.env.prod.local` or real env var |
| `DATABASE_PASSWORD`    | `root`                                | `.env.prod.local` or real env var |
| `GOOGLE_CLIENT_SECRET` | `'secret'`                            | `.env.prod.local` or real env var |
| `MAILER_DSN`           | `null://null`                         | `.env.prod.local` or real env var |
| `CLAMAV_HOST`          | `clamav`                              | `.env.prod.local` or real env var |
| `CLAMAV_PORT`          | `3310`                                | `.env.prod.local` or real env var |

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

Run it locally with `bin/check-default-secrets`.

## Deployer Integration

`deploy.php` shares `.env.prod.local`, `google_cloud_key.json`, `.dkim/private.key` and the `.jwt/` directory across releases, so production secrets are set up once in `/var/www/share/shared/`.
