# CAPTCHA Setup (Cap — Self-Hosted)

Catroweb uses [Cap](https://capjs.js.org/) for CAPTCHA protection on registration and password reset forms. Cap is a self-hosted, privacy-first CAPTCHA based on SHA-256 proof-of-work — no data is sent to third parties.

## Environment Variables

| Variable               | Purpose                                       | Example                    |
| ---------------------- | --------------------------------------------- | -------------------------- |
| `CAPTCHA_ENABLED`      | Enable/disable CAPTCHA verification           | `true` / `false`           |
| `CAPTCHA_API_ENDPOINT` | Backend URL for siteverify (server-to-server) | `http://127.0.0.1:3000`    |
| `CAPTCHA_PUBLIC_URL`   | Frontend URL for widget (browser-to-Cap)      | `https://cap.catrobat.org` |
| `CAPTCHA_SITE_KEY`     | Site key from Cap dashboard                   | `8c8e6f1e89`               |
| `CAPTCHA_SECRET`       | Secret key from Cap dashboard                 | `pS6bY...`                 |

**Why two URLs?** The backend verifies tokens via `CAPTCHA_API_ENDPOINT` (localhost, no DNS roundtrip). The browser widget loads from `CAPTCHA_PUBLIC_URL` (public HTTPS URL via nginx). In development both point to `http://localhost:3000`.

## Development (Docker)

The `docker-compose.dev.yaml` includes a pre-configured Cap instance:

```bash
docker compose -f docker/docker-compose.dev.yaml up -d
```

- Cap dashboard: `http://localhost:3000` (admin key: `catroweb-dev-admin`)
- **CAPTCHA is disabled by default** (`CAPTCHA_ENABLED=false` in `.env`) — registration works without any Cap setup

To enable CAPTCHA locally for testing, start the Cap container, create a site key in the dashboard, then update `.env`:

```env
CAPTCHA_ENABLED=true
CAPTCHA_SITE_KEY='<site-key-from-dashboard>'
CAPTCHA_SECRET='<secret-from-dashboard>'
```

## Production Setup

Production config for self-hosted services is maintained in the private [`Catrobat/infrastructure`](https://github.com/Catrobat/infrastructure) repo; this section describes the running setup.

- Cap runs as a container bound to localhost: `docker run -d --name cap --restart unless-stopped -p 127.0.0.1:3000:3000 -e ADMIN_KEY=<admin-key> -v cap-data:/usr/src/app/.data tiago2/cap:latest` (SQLite data in volume `cap-data`).
- nginx serves `cap.catrobat.org` (Cloudflare-proxied A record, origin certs, `/etc/nginx/sites-enabled/cap`). Admin routes (`/`, `/auth/`, `/server/`) are `deny all`; only the widget JS, challenge API and siteverify are public.
- `.env.prod.local`:

  ```env
  CAPTCHA_ENABLED=true
  CAPTCHA_API_ENDPOINT='http://127.0.0.1:3000'
  CAPTCHA_PUBLIC_URL='https://cap.catrobat.org'
  CAPTCHA_SITE_KEY='<site-key>'
  CAPTCHA_SECRET='<secret-key>'
  ```

### Managing Cap

Status/logs/restart: `docker ps | grep cap`, `docker logs cap --tail 50`, `docker restart cap`. Dashboard via SSH tunnel: `ssh -L 3000:127.0.0.1:3000 <user>@<host>`, then <http://localhost:3000>.

Creating a site key via API:

```bash
curl -s -X POST 'http://127.0.0.1:3000/auth/login' -H 'Content-Type: application/json' -d '{"admin_key": "<ADMIN_KEY>"}'
BEARER=$(echo -n '{"token":"<session_token>","hash":"<hashed_token>"}' | base64)
curl -s -X POST 'http://127.0.0.1:3000/server/keys' -H "Authorization: Bearer $BEARER" \
  -H 'Content-Type: application/json' -d '{"name": "share.catrobat.org"}'
```

## Testing

In the test environment `CaptchaVerifier` accepts every token except `'fail'`. Registration/reset responses always carry an `X-Captcha-Result` header (`verified`, `verification-failed`, `missing-token`, `disabled`, `test-auto-pass`, `test-forced-failure`); check it first when debugging.

## Troubleshooting

| Problem                         | Solution                                                                            |
| ------------------------------- | ----------------------------------------------------------------------------------- |
| Widget not rendering            | Check browser console for CORS errors; verify `CAPTCHA_PUBLIC_URL` is accessible    |
| 403 on registration             | Check `X-Captcha-Result` header; verify Cap is running and `CAPTCHA_SECRET` matches |
| Cap dashboard unreachable       | Check Docker container: `docker ps \| grep cap`                                     |
| "Token not found" on siteverify | Token already used or expired — Cap tokens are single-use                           |
| CORS errors in browser          | Configure CORS in Cap dashboard to allow `share.catrobat.org` origin                |
