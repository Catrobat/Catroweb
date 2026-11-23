#!/bin/bash
# Prints code=true|false and docs=true|false: did the commits between BASE and HEAD change anything outside / inside
# user-docs/? No pipes: grep -q closing a pipe early under pipefail once turned a large file list into code=false.
# --no-renames: a file moved out of or into user-docs/ counts on both sides.
set -euo pipefail
base=${1:?base commit}
head=${2:-HEAD}
outside=$(git diff --name-only --no-renames "$base" "$head" -- . ':(exclude)user-docs')
inside=$(git diff --name-only --no-renames "$base" "$head" -- user-docs)
echo "code=$([ -n "$outside" ] && echo true || echo false)"
echo "docs=$([ -n "$inside" ] && echo true || echo false)"
