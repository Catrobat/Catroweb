# Verified CI analysis-tool downloads

PHPLOC and PHPCPD use fixed versions from their existing official HTTPS origin.
The shared helper `.github/scripts/install-analysis-tool.sh` allows only those two
tools and pins their SHA256 digests. It downloads into the job's temporary directory
with bounded connection/request/retry limits, requires HTTP success, verifies the
digest, and checks the exact tool version before installing the final file. Failed
or interrupted downloads are removed; no tracked PHAR binary is added.

The initial pins were obtained from the official versioned HTTPS downloads with
normal TLS verification. Their detached signature/checksum sidecar endpoints were
unavailable during this change: these are reviewed TLS-origin pins, **not PGP-verified
artifacts**. Do not invent a publisher signature claim or substitute an unrelated
mirror. When updating a pin, review the official release and versioned artifact,
verify its digest and version, and verify a publisher signature when available.

Both jobs use the existing trusted PHP setup action, request PHP 8.5.12 and install
no application Composer dependencies. The helper checks the actual interpreter is
on the 8.5 branch and at least 8.5.12 before fetching anything. Update that helper
floor and both workflow setup requests together for the next required security
release. PHPLOC still scans `src tests`. PHPCPD still
scans `src` excluding exactly `src/Api/OpenAPI/Server` and `src/DB/Entity`; its nonzero
status still fails the check. No gate, threshold or exclusion is relaxed.

The versioned artifacts were checked in a disposable PHP 8.5.12 container with
networking disabled, read-only source/tools, no application configuration, an
unprivileged user, dropped capabilities and bounded resources. Both exact-version
checks, the full metrics command and the existing clone check succeeded. Those
checks validate tool acquisition and existing behavior, not broader application
compatibility or a replacement for normal CI.
