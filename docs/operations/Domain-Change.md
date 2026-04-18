# Domain Change Guide

How to change the production domain (e.g., `old.example.com` → `new.example.com`) when using Cloudflare + nginx origin setup.

## Overview

Traffic flow: `User → Cloudflare (public SSL) → nginx (origin SSL) → PHP-FPM`

Each domain needs its own Cloudflare origin certificate for the Cloudflare→nginx connection.

## Steps

### 1. Generate Cloudflare Origin Certificate for New Domain

1. Cloudflare Dashboard → select the new domain → **SSL/TLS → Origin Server → Create Certificate**
2. Let Cloudflare generate a new private key (don't reuse keys across domains)
3. Set hostnames: `*.newdomain.example, newdomain.example`
4. Copy the **certificate** (PEM) and **private key**

### 2. Install Certificate on Server

```bash
ssh user@host

# Save cert and key (use your actual filenames)
sudo nano /etc/ssl/cloudflare/newdomain-origin.pem   # paste certificate
sudo nano /etc/ssl/cloudflare/newdomain-origin.key   # paste private key

# Restrict key permissions
sudo chmod 600 /etc/ssl/cloudflare/newdomain-origin.key
```

### 3. Update nginx Configuration

Edit `/etc/nginx/sites-available/catroweb` with two sections:

**A) New domain — primary server block:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name new.example.com;
    return 301 https://new.example.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    client_max_body_size 100M;

    ssl_certificate     /etc/ssl/cloudflare/newdomain-origin.pem;
    ssl_certificate_key /etc/ssl/cloudflare/newdomain-origin.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    root /var/www/catroweb/current/public/;
    server_name new.example.com;

    # ... (standard location blocks, see Server-Setup.md)
}
```

**B) Old domain — redirect only:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name old.example.com;
    return 301 https://new.example.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name old.example.com;

    ssl_certificate     /etc/ssl/cloudflare/olddomain-origin.pem;
    ssl_certificate_key /etc/ssl/cloudflare/olddomain-origin.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    return 301 https://new.example.com$request_uri;
}
```

The old domain's SSL block still needs its original cert — Cloudflare connects via HTTPS before nginx can issue the redirect.

### 4. Ensure sites-enabled Uses a Symlink

If `sites-enabled/catroweb` is a copy (not a symlink), changes to `sites-available` won't take effect:

```bash
# Check
ls -la /etc/nginx/sites-enabled/catroweb

# If it's a regular file (not a symlink), fix it:
sudo rm /etc/nginx/sites-enabled/catroweb
sudo ln -s /etc/nginx/sites-available/catroweb /etc/nginx/sites-enabled/catroweb
```

### 5. Test and Reload nginx

```bash
sudo nginx -t              # validate config syntax
sudo systemctl reload nginx
```

### 6. Configure Cloudflare DNS

In Cloudflare Dashboard for the new domain:

1. Add an **A record**: `share` → server IP, **Proxied** (orange cloud)
2. SSL/TLS mode: **Full** or **Full (strict)**

### 7. Verify

```bash
# From any machine, check that nginx serves the correct origin cert:
echo | openssl s_client -connect YOUR_SERVER:443 -servername new.example.com 2>&1 | grep -E "NotBefore|subject="
```

Then visit `https://new.example.com` — should load the app.
Visit `https://old.example.com/any/path` — should 301 redirect to `https://new.example.com/any/path`.

### 8. Update Application Config

After nginx is working, update any app-level domain references:

- `.env.prod.local`: `APP_URL`, `TRUSTED_HOSTS`, `CORS_ALLOW_ORIGIN`
- OAuth callback URLs (if any)
- Email templates referencing the old domain

## Troubleshooting

| Error           | Cause                                 | Fix                                                                                      |
| --------------- | ------------------------------------- | ---------------------------------------------------------------------------------------- |
| **526**         | Cloudflare can't validate origin cert | Check cert is for correct domain, key matches, nginx serves it (`openssl s_client` test) |
| **521**         | Cloudflare can't reach origin at all  | Check nginx is running, firewall allows 443, DNS points to correct IP                    |
| **525**         | SSL handshake failed                  | Check `ssl_protocols` includes TLSv1.2+, cert/key files are readable by nginx            |
| Old cert served | `sites-enabled` is a stale copy       | Replace with symlink (step 4), reload nginx                                              |

## Why Origin Certs?

Cloudflare origin certificates authenticate the connection between Cloudflare's edge and your server. Without them, you'd need to set Cloudflare SSL mode to "Flexible" (HTTP between Cloudflare and origin), which is insecure — traffic between Cloudflare and your server would be unencrypted.
