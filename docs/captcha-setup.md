# CAPTCHA Setup (Cap — Self-Hosted)

Catroweb uses [Cap](https://capjs.js.org/) for CAPTCHA protection on registration and password reset forms. Cap is a self-hosted, privacy-first CAPTCHA based on SHA-256 proof-of-work — no data is sent to third parties.

## Environment Variables

| Variable               | Purpose                                       | Example                 |
| ---------------------- | --------------------------------------------- | ----------------------- |
| `CAPTCHA_ENABLED`      | Enable/disable CAPTCHA verification           | `true` / `false`        |
| `CAPTCHA_API_ENDPOINT` | Backend URL for siteverify (server-to-server) | `http://127.0.0.1:3000` |
| `CAPTCHA_PUBLIC_URL`   | Frontend URL for widget (browser-to-Cap)      | `https://cap.catrob.at` |
| `CAPTCHA_SITE_KEY`     | Site key from Cap dashboard                   | `8c8e6f1e89`            |
| `CAPTCHA_SECRET`       | Secret key from Cap dashboard                 | `pS6bY...`              |

**Why two URLs?** The backend verifies tokens via `CAPTCHA_API_ENDPOINT` (localhost, no DNS roundtrip). The browser widget loads from `CAPTCHA_PUBLIC_URL` (public HTTPS URL via nginx). In development both point to `http://localhost:3000`.

## Development (Docker)

The `docker-compose.dev.yaml` includes a pre-configured Cap instance:

```bash
docker compose -f docker/docker-compose.dev.yaml up -d
```

- Cap dashboard: `http://localhost:3000` (admin key: `catroweb-dev-admin`)
- **CAPTCHA is disabled by default** (`CAPTCHA_ENABLED=false` in `.env`) — registration works without any Cap setup
- New contributors can start the containers and everything works immediately

To enable CAPTCHA locally for testing, start the Cap container, create a site key in the dashboard, then update `.env`:

```env
CAPTCHA_ENABLED=true
CAPTCHA_SITE_KEY='<site-key-from-dashboard>'
CAPTCHA_SECRET='<secret-from-dashboard>'
```

## Production Setup

Production server: `<ip4>` (`share.catrob.at`), running nginx with Cloudflare origin certs.

### Current Infrastructure

| Component      | Location                      | Details                                                 |
| -------------- | ----------------------------- | ------------------------------------------------------- |
| Cap container  | `127.0.0.1:3000`              | `docker run -d --name cap --restart unless-stopped ...` |
| nginx proxy    | `cap.catrob.at`               | `/etc/nginx/sites-enabled/cap`                          |
| Docker compose | `/opt/cap/docker-compose.yml` | Reference only (docker-compose-plugin not installed)    |
| Cap data       | Docker volume `cap-data`      | Persistent SQLite data                                  |

### Cap Container

Cap runs as a Docker container bound to localhost only:

```bash
docker run -d \
  --name cap \
  --restart unless-stopped \
  -p 127.0.0.1:3000:3000 \
  -e ADMIN_KEY=<admin-key> \
  -v cap-data:/usr/src/app/.data \
  tiago2/cap:latest
```

**Note**: The `docker-compose-plugin` package is not available on this Ubuntu 24.04 server. The container is managed directly with `docker run`. The `/opt/cap/docker-compose.yml` file exists as documentation but is not used.

### nginx Reverse Proxy

Config: `/etc/nginx/sites-enabled/cap`

- Serves `cap.catrob.at` on ports 80 and 443 (Cloudflare origin certs)
- Admin routes (`/`, `/auth/`, `/server/`) are blocked with `deny all`
- Only the widget JS, challenge API, and siteverify endpoints are publicly accessible
- TLS termination is handled by Cloudflare (origin cert on the server)

### DNS (Cloudflare)

A record for `cap.catrob.at` → `<ip4>` (proxied through Cloudflare).

### Environment Configuration

Production secrets are in `/var/www/share/shared/.env.prod.local` (symlinked into each release by Deployer):

```env
CAPTCHA_ENABLED=true
CAPTCHA_API_ENDPOINT='http://127.0.0.1:3000'
CAPTCHA_PUBLIC_URL='https://cap.catrob.at'
CAPTCHA_SITE_KEY='<site-key>'
CAPTCHA_SECRET='<secret-key>'
```

### Managing Cap

```bash
# SSH to server

# Check status
docker ps | grep cap

# View logs
docker logs cap --tail 50

# Restart
docker restart cap

# Access admin dashboard (via SSH tunnel)
ssh -L 3000:127.0.0.1:3000 <user>@<ip4>
# Then open http://localhost:3000 in your browser

# Create a site key via API
curl -s -X POST 'http://127.0.0.1:3000/auth/login' \
  -H 'Content-Type: application/json' \
  -d '{"admin_key": "<ADMIN_KEY>"}'
# Use the returned session_token + hashed_token to call /server/keys
```

### Creating a Site Key via API

1. Login to get a session token:

   ```bash
   curl -s -X POST 'http://127.0.0.1:3000/auth/login' \
     -H 'Content-Type: application/json' \
     -d '{"admin_key": "<ADMIN_KEY>"}'
   ```

2. Base64-encode the auth payload:

   ```bash
   BEARER=$(echo -n '{"token":"<session_token>","hash":"<hashed_token>"}' | base64)
   ```

3. Create the key:
   ```bash
   curl -s -X POST 'http://127.0.0.1:3000/server/keys' \
     -H "Authorization: Bearer $BEARER" \
     -H 'Content-Type: application/json' \
     -d '{"name": "share.catrob.at"}'
   ```

## How It Works

1. The registration/password-reset page renders a `<cap-widget>` web component
2. The widget issues a SHA-256 proof-of-work challenge from the Cap server (via `CAPTCHA_PUBLIC_URL`)
3. On solve, the widget emits a `solve` event with a token
4. The token is sent to the API as `captcha_token` in the request body
5. The backend verifies the token via `POST CAPTCHA_API_ENDPOINT/CAPTCHA_SITE_KEY/siteverify`
6. The `X-Captcha-Result` response header indicates the verification outcome

## Testing

- **Test environment**: `CaptchaVerifier` auto-passes all tokens except `'fail'`
- **`X-Captcha-Result` header** is always present on registration/reset responses:
  - `test-auto-pass` — test env, token accepted
  - `test-forced-failure` — test env, token was `'fail'`
  - `verified` — production, Cap accepted the token
  - `verification-failed` — production, Cap rejected the token
  - `missing-token` — no token provided
  - `disabled` — CAPTCHA_ENABLED=false

## Troubleshooting

| Problem                         | Solution                                                                            |
| ------------------------------- | ----------------------------------------------------------------------------------- |
| Widget not rendering            | Check browser console for CORS errors; verify `CAPTCHA_PUBLIC_URL` is accessible    |
| 403 on registration             | Check `X-Captcha-Result` header; verify Cap is running and `CAPTCHA_SECRET` matches |
| Cap dashboard unreachable       | Check Docker container: `docker ps \| grep cap`                                     |
| "Token not found" on siteverify | Token already used or expired — Cap tokens are single-use                           |
| CORS errors in browser          | Configure CORS in Cap dashboard to allow `share.catrob.at` origin                   |
