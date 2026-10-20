# Authorized project-file delivery

This is a prepared application/server change. Deployment and CDN actions require separate approval of the concrete application and infrastructure PRs. The infrastructure repository deploys its main branch automatically; do not merge its preparation PR as a review shortcut.

## Request paths and policy

- `/project-files/{id}/assets/{path}` replaces generated extracted-preview URLs. It uses the existing project-page lookup, including its minor-owner, moderation owner/admin, featured and debug behavior.
- `/project-files/{id}/archive` and the existing `/api/projects/{id}/catrobat` use the existing archive API lookup and download limiter. They retain its stricter exclusion of invisible and auto-hidden projects, even for owners/admins and featured projects.
- The current `private` flag remains an unlisted/discovery flag: an otherwise accessible project is still accessible by ID. This change does not make it owner-only.
- Asset requests never dispatch download events. HEAD archive requests do not count as downloads. Archive GET events retain their existing behavior.

Only recognized raster/audio assets, project screenshots and `code.xml` can be delivered from the requested project's extracted directory. File IDs, path segments, real paths and MIME types are checked. Project-directory and nested symlinks are rejected; configured resource-root symlinks remain supported. The current deduplicator copies extracted assets, so it needs no escaping symlinks. Raw content-addressed storage URLs are denied; sharing a hash never grants access to another project's files.

XML and SVG use an attachment disposition, an inert content type and `nosniff`. HTML, scripts, unsupported extensions and mismatched image contents are rejected. Every delivered file is `private, no-store`.

## PHP fallback and nginx offload

The default is a Symfony `BinaryFileResponse`, supporting HEAD and byte ranges under development/Apache. Apache rejects legacy project/CAS prefixes before its image conversion rule, routes the new namespace to Symfony, and prevents its static-image cache headers from overriding the private policy. The public image converter is restricted independently by `PublicImageSourceResolver`.

The coordinated nginx configuration adds a server-owned FastCGI parameter, `PROJECT_FILE_ACCEL=1`. Only this parameter enables `X-Accel-Redirect`; HTTP request headers cannot enable it or select a mapping. The application validates the file before producing a URI under `/__protected-project-resources/`. That nginx location is `internal`, outside the public immutable-image location, and preserves private/no-store delivery. nginx handles the original HEAD/range request; PHP sends no duplicate file body.

The internal alias assumes the normal `public/resources` shared-storage layout. If storage parameters change to unrelated locations, update and test the alias mapping together; never accept a client-provided filesystem mapping. The test resource roots use PHP fallback.

## Coordinated rollout for separate approval

1. Review the image-conversion prerequisite, this application change and the private infrastructure change together. Record the exact approved SHAs and a rollback baseline containing the new routes.
2. Deploy the image-conversion prerequisite and application handling first. Newly generated previews use the new namespace immediately and work through PHP while nginx offload is still disabled. This compatibility stage does not by itself close existing static paths.
3. Activate the coordinated Apache/nginx rules and the trusted FastCGI parameter. Confirm direct legacy program/project/extract/CAS paths and direct internal locations return 404. Confirm the new namespace reaches the application even for image suffixes and cannot be shadowed by a static file.
4. Verify public/unlisted reads, authenticated owner/admin behavior, another minor's denial of not-for-kids files, and the minor-owner exception. Verify the intentionally stricter archive behavior for hidden/auto-hidden/featured projects. Check HEAD, ranges, safe dispositions, and `private, no-store` at the origin and through the CDN.
5. With explicit CDN approval, purge legacy project/archive/extract/CAS objects and configure the CDN to bypass caching for protected file routes and authenticated archive responses. Verify that cache rules do not override the private/no-store response policy. No purge or cache-rule change is performed by these PRs.
6. Verify generated markup/API preview URLs contain no legacy extracted-resource paths. Retain the server denies permanently.

Old immutable browser-cache entries and files already downloaded cannot be recalled. An edge purge removes CDN copies only. The new URL namespace avoids reusing those old browser-cache keys for future application-generated requests.

For fallback/rollback, keep the new application routes and legacy denies in place and disable the trusted acceleration parameter. Do not roll back to an application version that generates legacy static preview URLs while the server denies are active, and do not reopen raw project paths to restore a broken deployment.

## Verification

Native filesystem/controller/response tests cover authorization-before-file-access, project/path boundaries, symlinks, MIME/dispositions, header spoofing, PHP HEAD/range behavior and front-controller URL bases. API Behat covers the existing preview/archive policy matrix and download counts; web Behat exercises Apache rejection of real legacy archive/image files. The infrastructure repository separately runs isolated nginx/FastCGI transport tests with synthetic files, no host ports and no production access.
