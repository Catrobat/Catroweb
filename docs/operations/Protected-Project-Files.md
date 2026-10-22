# Authorized project-file delivery

Extracted project files are served through Symfony, never as static files.

## Decisions

- `/project-files/{id}/assets/{path}` replaces the old static extracted-preview URLs and uses the project-page access rules. `/project-files/{id}/archive` and `/api/projects/{id}/catrobat` keep the stricter archive rules (invisible/auto-hidden excluded even for owners/admins).
- `private` stays an unlisted flag, not owner-only: an accessible project is reachable by ID.
- Only recognized raster/audio assets, screenshots and `code.xml` are delivered; the response type is the detected MIME, not the filename. Project-directory symlinks are rejected; raw content-addressed storage URLs are denied, so sharing a hash never grants access to another project's files.
- SVG previews get `Content-Security-Policy: sandbox; default-src 'none'; ...` and are always streamed by PHP, so the sandbox survives. Everything is `private, no-store` with `nosniff`.
- Assets never count as downloads. On-demand extraction spends the per-IP `downloadBurstLimiter` budget (429 + `Retry-After`).

## nginx offload

Without nginx, Symfony sends a `BinaryFileResponse` (HEAD and ranges supported). nginx enables `X-Accel-Redirect` only via the server-owned FastCGI parameter `PROJECT_FILE_ACCEL=1` (request headers can't enable it). The internal `/__protected-project-resources/` alias assumes the standard `public/resources` layout; if storage parameters change, update the alias with them.

## Rollout order (still required)

The nginx config lives in the private `Catrobat/infrastructure` repo, whose `main` auto-deploys, so don't merge its PRs as a review shortcut.

1. Review infrastructure #32 (routing prefix), the image-conversion prerequisite, the application change and infrastructure #30 (final rules) together; record approved SHAs and a rollback baseline.
2. **Deploy the routing-only nginx prerequisite first.** Its `^~ /project-files/` location forwards image-looking virtual URLs to PHP before the static-image regex. Without it, nginx returns static 404s for the new preview URLs, so application-first rollout is unsafe.
3. Deploy the image-conversion prerequisite and the application. Confirm preview images reach PHP.
4. Activate the final nginx rules and `PROJECT_FILE_ACCEL`. Confirm legacy project/extract/CAS paths and internal locations return 404; check owner/admin, minor/not-for-kids and hidden-archive behavior, HEAD, ranges and `private, no-store` at origin.
5. With CDN approval: purge legacy project/archive/extract/CAS objects and bypass caching for protected routes.
6. Verify no generated markup or API response contains legacy extracted-resource paths. Keep the server denies permanently.

Rollback: keep the new routes and legacy denies, disable `PROJECT_FILE_ACCEL`. Never roll back to an application version that generates legacy static preview URLs while the denies are active, and never reopen raw project paths.
