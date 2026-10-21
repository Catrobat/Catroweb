#!/bin/sh
# CI-only smoke check: run in a fresh disposable image, without published ports.
set -eu
cd /var/www/catroweb
php -r 'printf("Installed CLI: PHP %s\n", PHP_VERSION); exit(PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 5 && version_compare(PHP_VERSION, "8.5.12", ">=") ? 0 : 1);'
probe=$(mktemp public/php-runtime-XXXXXXXX.php)
cleanup() {
    rm -f "$probe"
    apache2ctl -k stop >/dev/null 2>&1 || true
}
trap cleanup EXIT HUP INT TERM
cat > "$probe" <<'PHP'
<?php
header('Content-Type: application/json');
echo json_encode(['version' => PHP_VERSION, 'sapi' => PHP_SAPI], JSON_THROW_ON_ERROR);
PHP
chmod 644 "$probe"
apache2ctl -D FOREGROUND &
attempt=0
until response=$(curl --fail --silent --show-error --max-time 2 "http://127.0.0.1/$(basename "$probe")"); do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 30 ]; then
        echo 'Apache PHP runtime probe did not become healthy' >&2
        exit 1
    fi
    sleep 1
done
PHP_RUNTIME_RESPONSE="$response" php -r '
    $runtime = json_decode(getenv("PHP_RUNTIME_RESPONSE"), true, 512, JSON_THROW_ON_ERROR);
    printf("Installed HTTP runtime: PHP %s (%s)\n", $runtime["version"] ?? "unknown", $runtime["sapi"] ?? "unknown");
    exit(($runtime["sapi"] ?? null) === "apache2handler"
        && preg_match("/^8\\.5\\./", $runtime["version"] ?? "")
        && version_compare($runtime["version"], "8.5.12", ">=") ? 0 : 1);
'
