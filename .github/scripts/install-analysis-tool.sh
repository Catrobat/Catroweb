#!/usr/bin/env bash
set -euo pipefail
umask 077

case "${1:-}" in
  phploc)
    tool=phploc
    version=7.0.2
    sha256=3d59778ec86faf25fd00e3a329b2f9ad4a3c751ca91601ea7dab70f887b0bf46
    ;;
  phpcpd)
    tool=phpcpd
    version=6.0.3
    sha256=2cbaea7cfda1bb4299d863eb075e977c3f49055dd16d88529fae5150d48a84cb
    ;;
  *)
    echo 'Expected one supported tool: phploc or phpcpd' >&2
    exit 2
    ;;
esac
if [[ $# -ne 1 ]]; then
  echo 'Expected exactly one tool argument' >&2
  exit 2
fi

# Verify the actual interpreter; a setup request alone is not runtime evidence.
php -r 'printf("Installed CLI: PHP %s\n", PHP_VERSION); exit(PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 5 && version_compare(PHP_VERSION, "8.5.12", ">=") ? 0 : 1);'

install_dir="${RUNNER_TEMP:?RUNNER_TEMP must be set}/analysis-tools"
mkdir -p "$install_dir"
download=$(mktemp "$install_dir/.$tool.XXXXXXXX")
trap 'rm -f "$download"' EXIT
trap 'exit 1' HUP INT TERM

# Initial digest pins come from the same official HTTPS origin used previously;
# they are reviewed TLS-origin pins, not a claim of PGP signature verification.
curl --fail --silent --show-error --location --proto '=https' --proto-redir '=https' \
  --connect-timeout 10 --max-time 30 --retry 2 --retry-delay 2 \
  --retry-all-errors --retry-max-time 120 \
  --output "$download" "https://phar.phpunit.de/$tool-$version.phar"
if ! printf '%s  %s\n' "$sha256" "$download" | sha256sum --check --status; then
  echo "SHA256 mismatch for $tool" >&2
  exit 1
fi

# Never execute a download until both HTTP success and the pinned digest pass.
actual_version=$(php "$download" --version)
case "$actual_version" in
  "$tool $version "*) ;;
  *)
    echo "Unexpected $tool version" >&2
    exit 1
    ;;
esac
printf '%s\n' "$actual_version"
mv "$download" "$install_dir/$tool.phar"
