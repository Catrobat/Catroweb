# Authorized project-file delivery

This is a prepared application/server change. Deployment and CDN actions require separate approval of the concrete application and infrastructure PRs. The infrastructure repository deploys its main branch automatically; do not merge its preparation PR as a review shortcut.

## Request paths and policy

- `/project-files/{id}/assets/{path}` replaces generated extracted-preview URLs. It uses the existing project-page lookup, including its minor-owner, moderation owner/admin, featured and debug behavior.
- `/project-files/{id}/archive` and the existing `/api/projects/{id}/catrobat` use the existing archive API lookup and download limiter. They retain its stricter exclusion of invisible and auto-hidden projects, even for owners/admins and featured projects.
- The current `private` flag remains an unlisted/discovery flag: an otherwise accessible project is still accessible by ID. This change does not make it owner-only.
- Asset requests never dispatch download events. HEAD archive requests do not count as downloads. Archive GET events retain their existing behavior.

Only recognized raster/audio assets, project screenshots and `code.xml` can be delivered from the requested project's extracted directory. File IDs, path segments, real paths and MIME types are checked. Project-directory and nested symlinks are rejected; configured resource-root symlinks remain supported. The current deduplicator copies extracted assets, so it needs no escaping symlinks. Raw content-addressed storage URLs are denied; sharing a hash never grants access to another project's files.

XML and SVG use an attachment disposition, an inert content type and `nosniff`. HTML, scripts and unrecognized content are rejected. Raster filenames must match their detected MIME, except extensionless raster looks and PNG bytes stored with a `.webp` suffix (Catroid converts imported WebP without renaming). The response always uses the detected canonical raster type. Every delivered file is `private, no-store`.

Sound-directory assets use a bounded detected MIME list independent of their filename: WAV, MP3, Ogg, MP4/M4A, AAC and FLAC. Catroid records MPEG-4/AAC as extensionless `recording` and preserves imported URI names; its unresolved-name `.wav` fallback does not transcode. Safe container aliases (`video/mp4`, `application/ogg`) are delivered as `audio/mp4` and `audio/ogg` with inline disposition and `nosniff`; an actual video track does not become active HTML/script content. Format recognition does not guarantee codec support in every browser. Other types, including HTML/XML/SVG and raster images placed in `sounds`, are rejected.

## PHP fallback and nginx offload

The default is a Symfony `BinaryFileResponse`, supporting HEAD and byte ranges under development/Apache. Apache rejects legacy project/CAS prefixes before its image conversion rule, routes the new namespace to Symfony, and prevents its static-image cache headers from overriding the private policy. The public image converter is restricted independently by `PublicImageSourceResolver`.

The coordinated nginx configuration adds a server-owned FastCGI parameter, `PROJECT_FILE_ACCEL=1`. Only this parameter enables `X-Accel-Redirect`; HTTP request headers cannot enable it or select a mapping. The application validates the file before producing a URI under `/__protected-project-resources/`. That nginx location is `internal`, outside the public immutable-image location, and preserves private/no-store delivery. nginx handles the original HEAD/range request; PHP sends no duplicate file body.

The internal alias assumes the normal `public/resources` shared-storage layout. If storage parameters change to unrelated locations, update and test the alias mapping together; never accept a client-provided filesystem mapping. The test resource roots use PHP fallback.

## Coordinated rollout for separate approval

1. Review the routing-only infrastructure prerequisite (infrastructure #32), image-conversion prerequisite, this application change and final private infrastructure change (#30) together. Record exact approved SHAs and a rollback baseline containing the new routes. Neither infrastructure PR may be merged as a review shortcut because main auto-deploys.
2. Deploy the routing-only nginx prerequisite first. Its `^~ /project-files/` location forwards image-looking virtual URLs to PHP before the existing static-image regex; it preserves legacy delivery and enables no acceleration or denies. Without this deployed prefix the current nginx image regex returns static 404s for the new preview URLs, so application-first rollout is unsafe.
3. Deploy the image-conversion prerequisite and application handling. Newly generated previews use the new namespace through PHP while nginx offload is still disabled. Confirm preview images reach the application, including paths shadowed by static files. This stage does not by itself close existing nginx static paths. The application's Apache rules are deployed with the compatible routes.
4. Activate the final nginx rules and trusted FastCGI parameter. Retain the compatibility prefix, confirm legacy program/project/extract/CAS paths and direct internal locations return 404, and verify authorized offload. Check public/unlisted reads, authenticated owner/admin behavior, another minor's denial of not-for-kids files, the minor-owner exception and the stricter archive behavior for hidden/auto-hidden/featured projects. Check HEAD, ranges, safe dispositions and `private, no-store` at origin.
5. With explicit CDN approval, purge legacy project/archive/extract/CAS objects and configure the CDN to bypass caching for protected file routes and authenticated archive responses. Verify that cache rules do not override private/no-store through the edge. No purge or cache-rule change is performed by these PRs.
6. Verify generated markup/API preview URLs contain no legacy extracted-resource paths. Retain the server denies permanently.

Old immutable browser-cache entries and files already downloaded cannot be recalled. An edge purge removes CDN copies only. The new URL namespace avoids reusing those old browser-cache keys for future application-generated requests.

For fallback/rollback, keep the new application routes and legacy denies in place and disable the trusted acceleration parameter. Do not roll back to an application version that generates legacy static preview URLs while the server denies are active, and do not reopen raw project paths to restore a broken deployment.

## Verification

Native filesystem/controller/response tests cover authorization-before-file-access, project/path boundaries, symlinks, MIME/dispositions, header spoofing, PHP HEAD/range behavior and front-controller URL bases. API Behat covers the existing preview/archive policy matrix and download counts; web Behat exercises Apache rejection of real legacy archive/image files. The infrastructure repository separately runs isolated nginx/FastCGI transport tests with synthetic files, no host ports and no production access.
