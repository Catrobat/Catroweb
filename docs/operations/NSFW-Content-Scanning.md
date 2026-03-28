# NSFW Content Scanning

## Overview

Catroweb scans uploaded images for NSFW (Not Safe For Work) content using a self-hosted AI model ([Falconsai/nsfw_image_detection](https://huggingface.co/Falconsai/nsfw_image_detection)). As a children's platform, all user-uploaded images are checked before being stored. NSFW images are rejected with HTTP 422.

## How It Works

1. A user uploads an image (avatar, project screenshot, or `.catrobat` project file containing images).
2. The Symfony `ContentSafetyScanner` service sends the raw image bytes to the NSFW scanner microservice via HTTP POST.
3. The scanner classifies the image using a Vision Transformer (ViT) model and returns an NSFW score (0.0--1.0).
4. If the score exceeds the configured threshold (default: 0.7), the upload is rejected.

### Integration Points

| Upload Type     | Integration Point                                  | Behavior                                                            |
| --------------- | -------------------------------------------------- | ------------------------------------------------------------------- |
| Profile picture | `UserRequestValidator::validateAndResizePicture()` | Scans the resized image before storing                              |
| Project images  | `ProjectManager::addProject()`                     | Scans all images extracted from `.catrobat` file after sanitization |

### Fail-Open Design

If the scanner microservice is unavailable (down, timeout, network error), uploads are **allowed through** and a warning is logged. This prevents the scanner from becoming a single point of failure for the entire upload pipeline.

## Configuration Reference

| Variable                   | Type    | Default                    | Description                                                              |
| -------------------------- | ------- | -------------------------- | ------------------------------------------------------------------------ |
| `CONTENT_SAFETY_URL`       | string  | `http://nsfw-scanner:5000` | URL of the NSFW scanner microservice                                     |
| `CONTENT_SAFETY_ENABLED`   | boolean | `true`                     | Master switch. Set to `false` to disable scanning.                       |
| `CONTENT_SAFETY_THRESHOLD` | float   | `0.7`                      | NSFW score threshold (0.0--1.0). Images scoring above this are rejected. |

### Where to Set Values

| Environment | File                            | Typical values                                                               |
| ----------- | ------------------------------- | ---------------------------------------------------------------------------- |
| Development | `.env` (committed)              | `CONTENT_SAFETY_ENABLED=true`, `CONTENT_SAFETY_URL=http://nsfw-scanner:5000` |
| Test        | `.env.test` (committed)         | `CONTENT_SAFETY_ENABLED=false`                                               |
| Production  | `.env.prod` (committed)         | `CONTENT_SAFETY_ENABLED=true`                                                |
| Production  | `.env.prod.local` (server only) | `CONTENT_SAFETY_URL=http://127.0.0.1:5000`                                   |

## Docker Development Setup

The NSFW scanner container is defined in `docker/docker-compose.dev.yaml`:

```yaml
nsfw-scanner:
  container_name: nsfw-scanner.catroweb
  build:
    context: ./nsfw-scanner
    dockerfile: Dockerfile
  ports:
    - '5050:5000'
  healthcheck:
    test:
      [
        'CMD',
        'python',
        '-c',
        "import urllib.request; urllib.request.urlopen('http://localhost:5000/health')",
      ]
    interval: 30s
    timeout: 10s
    retries: 3
```

**First build takes ~5 minutes** (downloads the ~500MB model). Subsequent builds use Docker cache.

```bash
# Start with the rest of the dev stack
docker compose -f docker/docker-compose.dev.yaml up -d

# Verify it's running
curl http://localhost:5050/health
# {"status": "ok"}
```

## Production Setup

The NSFW scanner runs as a standalone Docker container on the production server.

### Installation

```bash
# Create the scanner directory
mkdir -p /opt/nsfw-scanner

# Copy the three files from the repo
cp docker/nsfw-scanner/app.py /opt/nsfw-scanner/
cp docker/nsfw-scanner/requirements.txt /opt/nsfw-scanner/
cp docker/nsfw-scanner/Dockerfile /opt/nsfw-scanner/

# Build the image (takes ~5 minutes first time)
cd /opt/nsfw-scanner
docker build -t nsfw-scanner:latest .

# Run with auto-restart (survives reboots)
docker run -d \
  --name nsfw-scanner \
  --restart always \
  -p 127.0.0.1:5000:5000 \
  --memory=4g \
  nsfw-scanner:latest
```

### Verify

```bash
# Check container is running
docker ps --filter name=nsfw-scanner

# Check health endpoint
curl http://127.0.0.1:5000/health
# {"status": "ok"}

# Test with an image
curl -s -X POST http://127.0.0.1:5000/scan \
  --data-binary @/path/to/test-image.jpg \
  -H 'Content-Type: application/octet-stream'
# {"safe": true, "nsfw_score": 0.05, "safe_score": 0.95, "label": "safe"}
```

### Environment Configuration

Add to `.env.prod.local` on the server:

```env
CONTENT_SAFETY_URL=http://127.0.0.1:5000
CONTENT_SAFETY_ENABLED=true
CONTENT_SAFETY_THRESHOLD=0.7
```

### Updating

```bash
cd /opt/nsfw-scanner
docker stop nsfw-scanner
docker rm nsfw-scanner
docker build -t nsfw-scanner:latest .
docker run -d \
  --name nsfw-scanner \
  --restart always \
  -p 127.0.0.1:5000:5000 \
  --memory=4g \
  nsfw-scanner:latest
```

## Monitoring

### Health Check

```bash
curl -f http://127.0.0.1:5000/health || echo "NSFW scanner is down"
```

### Logs

```bash
docker logs nsfw-scanner
docker logs nsfw-scanner --tail 50 -f  # follow
```

### Resource Usage

The scanner uses approximately:

- **Memory**: ~2--3 GB (model loaded in memory)
- **CPU**: ~50ms per image on modern CPU
- **Disk**: ~2 GB (Docker image with model)

## Scanner API Reference

### `GET /health`

Returns `{"status": "ok"}` if the service is running and the model is loaded.

### `POST /scan`

Accepts raw image bytes in the request body.

**Request:**

- `Content-Type: application/octet-stream`
- Body: raw image binary data (JPEG, PNG, GIF, WebP, etc.)

**Response:**

```json
{
  "safe": true,
  "nsfw_score": 0.0468,
  "safe_score": 0.9528,
  "label": "safe"
}
```

**Error responses:**

- `400`: No image data provided, or invalid image format
- `500`: Internal error (model failure)

## Troubleshooting

### Scanner returns 500 for very small images

The ViT model requires images with at least 3 color channels (RGB). Single-pixel or grayscale images may fail. The application converts images to RGB before scanning, but edge cases may exist.

### Container won't start (out of memory)

The model requires ~2 GB RAM. Ensure the server has sufficient free memory. The `--memory=4g` flag limits the container to prevent it from consuming all system memory.

### Uploads succeed but scanner is down

This is expected behavior (fail-open). Check application logs for warnings like `Content safety scanner unavailable`. Restart the scanner container:

```bash
docker restart nsfw-scanner
```

### Threshold tuning

If you get false positives (safe images being rejected), lower the threshold:

```env
CONTENT_SAFETY_THRESHOLD=0.8
```

If you get false negatives (NSFW images getting through), raise it:

```env
CONTENT_SAFETY_THRESHOLD=0.6
```

The default (0.7) is conservative and should work well for most cases.

## Source Code

| File                                                  | Purpose                       |
| ----------------------------------------------------- | ----------------------------- |
| `docker/nsfw-scanner/app.py`                          | Python Flask microservice     |
| `docker/nsfw-scanner/Dockerfile`                      | Container build definition    |
| `docker/nsfw-scanner/requirements.txt`                | Python dependencies           |
| `src/Security/ContentSafety/ContentSafetyScanner.php` | Symfony service (HTTP client) |
| `src/Security/ContentSafety/ContentSafetyResult.php`  | Value object for scan results |
| `tests/PhpUnit/Security/ContentSafetyScannerTest.php` | Unit tests                    |
