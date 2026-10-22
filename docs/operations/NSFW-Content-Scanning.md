# NSFW Content Scanning

Uploaded profile pictures are scanned by a self-hosted model ([Falconsai/nsfw_image_detection](https://huggingface.co/Falconsai/nsfw_image_detection)) in the `docker/nsfw-scanner/` Flask service. `ContentSafetyScanner` posts the image bytes to `/scan`; a score at or above `CONTENT_SAFETY_THRESHOLD` (default 0.7) rejects the upload with 422. Today the only caller is `UserRequestValidator` (avatars); project images are **not** scanned yet.

**Fail-open by design:** if the scanner is down or times out (5 s), the upload goes through and `Content safety scanner unavailable` is logged, so the scanner can't take down uploads.

## Configuration

| Variable                   | Default                    | Notes                                           |
| -------------------------- | -------------------------- | ----------------------------------------------- |
| `CONTENT_SAFETY_URL`       | `http://nsfw-scanner:5000` | prod `.env.prod.local`: `http://127.0.0.1:5000` |
| `CONTENT_SAFETY_ENABLED`   | `true`                     | `false` in `.env.test`                          |
| `CONTENT_SAFETY_THRESHOLD` | `0.7`                      | Raise on false positives, lower on misses       |

Dev: the scanner starts with `docker/docker-compose.dev.yaml` on port 5050 (`curl http://localhost:5050/health`). The first build downloads the ~500 MB model (~5 min).

## Production

One-time prerequisites (as root):

```bash
mkdir -p /opt/nsfw-scanner && chown -R deploy:deploy /opt/nsfw-scanner
usermod -aG docker deploy
```

After that, the `restart:nsfw-scanner` task in `deploy.php` rsyncs `docker/nsfw-scanner/` to `/opt/nsfw-scanner/` and rebuilds the container only when the sources changed. Manual restart: `cd /opt/nsfw-scanner && docker compose -f docker-compose.prod.yaml up -d --build`.

Check it:

```bash
curl http://127.0.0.1:5000/health          # {"status": "ok"}
curl -s -X POST http://127.0.0.1:5000/scan --data-binary @image.jpg -H 'Content-Type: application/octet-stream'
docker logs nsfw-scanner --tail 50 -f
```

Needs ~2–3 GB RAM (model in memory) and ~2 GB disk; ~50 ms per image on CPU. If it won't start, check free memory first.
