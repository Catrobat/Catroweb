# NSFW Content Scanning

Images are scanned by a self-hosted model ([Falconsai/nsfw_image_detection](https://huggingface.co/Falconsai/nsfw_image_detection)) in the `docker/nsfw-scanner/` Flask service. `ContentSafetyScanner` posts the image bytes to `/scan`; a score at or above `CONTENT_SAFETY_THRESHOLD` (default 0.7) counts as unsafe.

- Profile pictures: `UserRequestValidator` rejects the upload with 422.
- Project screenshots and look images, studio covers: scanned on the `default` worker after the upload against the stricter `CONTENT_SAFETY_CONTENT_THRESHOLD`; flagged images queue the content for admin review or hide it, see [Image scans](./Content-Moderation.md#image-scans).

The scanner composites transparent images (alpha, palette transparency) onto white before classifying: `convert("RGB")` alone turns transparent pixels black, and a sprite cut out on black scored as nudity (0.99; 0.53 on white). `python3 docker/nsfw-scanner/test_flatten.py` checks it (Pillow only).

**Fail-open by design:** if the scanner is down or times out (5 s), the upload goes through, nothing is hidden and `Content safety scanner unavailable` is logged, so the scanner can't take down uploads.

## Configuration

| Variable                           | Default                    | Notes                                             |
| ---------------------------------- | -------------------------- | ------------------------------------------------- |
| `CONTENT_SAFETY_URL`               | `http://nsfw-scanner:5000` | prod: `http://127.0.0.1:5000`, set in `.env.prod` |
| `CONTENT_SAFETY_ENABLED`           | `true`                     | `false` in `.env.test`                            |
| `CONTENT_SAFETY_THRESHOLD`         | `0.7`                      | Avatars (prod 0.9): rejected at this score        |
| `CONTENT_SAFETY_CONTENT_THRESHOLD` | `0.97`                     | Project/studio images: flagged at this score      |
| `CONTENT_SAFETY_HIDE_THRESHOLD`    | `0.995`                    | One flagged image this sure hides the content     |
| `CONTENT_SAFETY_HIDE_MIN_IMAGES`   | `2`                        | This many flagged images hide the content         |

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
