#!/bin/sh
# Local gate, same as CI. Runs from anywhere: ./check.sh in user-docs/, or user-docs/check.sh from the repo root.
# Translations changed since the base (BASE, else upstream or origin main) must be current.
set -e
cd "$(dirname "$0")"
base=${BASE:-$(git rev-parse -q --verify upstream/main || git rev-parse -q --verify origin/main || true)}
python3 scripts/check.py ${base:+--base "$base"}
python3 scripts/test_check.py
