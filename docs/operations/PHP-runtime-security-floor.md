# PHP runtime security floor

Repository runtime images and the native PHP/Composer CI setup require PHP 8.5.12
or a later stable patch on the 8.5 branch. This floor follows the
[PHP security release](https://www.php.net/archive/2026.php#2026-10-21-1)
for CVE-2026-109618 and CVE-2026-109619. A successful dependency cache restore is
not evidence of a patched interpreter.

`docker/Dockerfile` sets `PHP_MIN_VERSION` before its package-install layer. Bumping
that value invalidates the cached layer and the build asserts the installed CLI
version. The runtime uses the Debian Bookworm Sury repository; availability in a
different distribution repository does not prove these packages are available.
Check each target architecture independently: patched package publication can lag
between architectures; an unpatched architecture must continue to fail the floor.
If the selected repository has not published the required version, the build must
fail rather than silently retain an older cached runtime. Base-image pulls alone
do not invalidate an unchanged cached package-install command.

The shared native CI action requests the patched release and checks the actual
interpreter before Composer. The test, development-container and image-SBOM builds
also run `docker/app/check-php-runtime.sh` in a fresh disposable container without
published ports. It checks CLI and the version/SAPI returned by a real local Apache
HTTP request; it removes the temporary probe and stops Apache on exit. Run this
script only in a disposable image, never against a live application container.

For the next required security release, update the Docker floor, native setup and
assertion, and smoke-check floor together. Validate failure on an older runtime,
then build and check the patched image; retain actual CLI and Apache version output
as evidence. No Composer or application dependency update is needed for this change.
Repository image checks do not patch or prove the version of a separately managed
host runtime. Host package installation, worker restart and production acceptance
remain separate operational steps.
