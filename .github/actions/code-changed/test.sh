#!/bin/bash
# Self-test for changed.sh in a throwaway repository: docs-only, code-only, a file list over 64 KB, and renames.
set -euo pipefail
script="$(cd "$(dirname "$0")" && pwd)/changed.sh"
repo=$(mktemp -d)
trap 'rm -rf "$repo"' EXIT
cd "$repo"
git init -q
git config user.email test@example.com
git config user.name test
git config commit.gpgsign false
mkdir -p user-docs/en src
echo a > user-docs/en/a.md
echo a > src/a.php
git add -A && git commit -qm base

expect() { # <name> <expected output>
  local got
  got=$("$script" HEAD^ HEAD | tr '\n' ' ')
  if [ "$got" != "$2" ]; then
    echo "FAIL $1: got '$got', expected '$2'"
    exit 1
  fi
  echo "ok   $1"
}

echo b > user-docs/en/a.md && git commit -qam docs
expect 'docs only' 'code=false docs=true '

echo b > src/a.php && git commit -qam code
expect 'code only' 'code=true docs=false '

for i in $(seq 1 1500); do echo x > "user-docs/en/page-with-a-rather-long-name-to-pass-64-kilobytes-$i.md"; done
echo c > src/a.php
git add -A && git commit -qm big
[ "$(git diff --name-only HEAD^ HEAD | wc -c)" -gt 65536 ]
expect 'over 64 KB of docs plus one code file' 'code=true docs=true '

git mv src/a.php user-docs/a.php && git commit -qm 'move into docs'
expect 'rename into user-docs/' 'code=true docs=true '

git mv user-docs/en/a.md README.md && git commit -qm 'move out of docs'
expect 'rename out of user-docs/' 'code=true docs=true '
