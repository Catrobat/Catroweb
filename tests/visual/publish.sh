#!/usr/bin/env bash
# CI only, after compare.sh: publishes the before|after|diff strips to the orphan branch
# `visual-snapshots` (pr-<n>/<run id>/) so the PR comment can embed them, then creates or
# updates the single PR comment marked <!-- visual-snapshots -->.
# Needs: PR_NUMBER, RUN_ID, GH_TOKEN (contents + pull-requests write), CHANGED (from compare.sh).
set -euo pipefail

ACTUAL=tests/TestReports/Visual
BRANCH=visual-snapshots
repo=$(gh repo view --json nameWithOwner -q .nameWithOwner)
comment_id=$(gh api "repos/$repo/issues/$PR_NUMBER/comments" --paginate \
  -q '.[] | select(.user.login == "github-actions[bot]" and (.body | startswith("<!-- visual-snapshots -->"))) | .id' | head -1)
# Checkout runs without persisted credentials; only this script gets the write token
remote="https://x-access-token:$GH_TOKEN@github.com/$repo.git"

# Nothing changed and nothing said before: stay quiet
if [ "$CHANGED" -eq 0 ] && [ -z "$comment_id" ]; then
  exit 0
fi

if compgen -G "$ACTUAL/strips/*.png" >/dev/null; then
  dir=$(mktemp -d)
  trap 'git worktree remove --force "$dir"; git branch -D "$BRANCH" >/dev/null' EXIT
  if git fetch --depth 1 "$remote" "$BRANCH" 2>/dev/null; then
    git worktree add -B "$BRANCH" "$dir" FETCH_HEAD
  else
    git worktree add --orphan -b "$BRANCH" "$dir"
  fi
  # Only the latest run per PR is kept in the tree; old ones stay in history.
  # ponytail: the branch's history grows with every changed run; delete and recreate it if it gets big.
  rm -rf "$dir/pr-$PR_NUMBER"
  mkdir -p "$dir/pr-$PR_NUMBER/$RUN_ID"
  cp "$ACTUAL"/strips/*.png "$dir/pr-$PR_NUMBER/$RUN_ID/"
  git -C "$dir" add -A
  git -C "$dir" -c user.name='github-actions[bot]' -c user.email='41898282+github-actions[bot]@users.noreply.github.com' \
    commit -qm "Visual snapshots for #$PR_NUMBER (run $RUN_ID)"
  # Other PRs push to the same branch; their folders never overlap, so rebase and retry
  for attempt in 1 2 3; do
    git -C "$dir" push -q "$remote" "$BRANCH" && break
    [ "$attempt" -eq 3 ] && exit 1
    git -C "$dir" pull -q --rebase "$remote" "$BRANCH"
  done
fi

if [ -n "$comment_id" ]; then
  gh api -X PATCH "repos/$repo/issues/comments/$comment_id" -F body=@"$ACTUAL/report.md" >/dev/null
else
  gh pr comment "$PR_NUMBER" --body-file "$ACTUAL/report.md" >/dev/null
fi
