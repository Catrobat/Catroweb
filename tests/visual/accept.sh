#!/usr/bin/env bash
# Accept the screenshots of a PR's latest CI run as the new visual baselines.
# Usage: tests/visual/accept.sh <pr-number>   (then review, commit and push)
set -euo pipefail
pr="${1:?usage: tests/visual/accept.sh <pr-number>}"
sha=$(gh pr view "$pr" --json headRefOid -q .headRefOid)
run=$(gh run list --workflow tests.yaml --commit "$sha" --limit 1 --json databaseId -q '.[0].databaseId')
[ -n "$run" ] || { echo "No tests.yaml run for $sha yet" >&2; exit 1; }
tmp=$(mktemp -d)
gh run download "$run" -n visual-snapshots -D "$tmp"
cp "$tmp"/*.png tests/visual/baselines/
rm -rf "$tmp"
git add tests/visual/baselines
git status --short tests/visual/baselines
echo "Baselines updated from run $run. Review the images, then commit and push."
