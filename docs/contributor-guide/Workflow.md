# Workflow

## Forking Workflow

Don't create branches on the official repository: fork it and open pull requests against `main`, the single integration and production branch. `release/*` branches are created by the release workflow and only bump the version; merging one into `main` deploys.

## Checking out the newest version of the project

Start from the latest upstream `main` (remote `catroweb`) and reset the project:

```
git checkout main && git pull catroweb main
composer install && yarn install
bin/console catrobat:reset --hard
yarn dev
git checkout -b SHARE-XXX
```

## Creating a Pull Request

**Test everything!** When tests are failing the PR will not be accepted.

Also, make sure that every commit has the **correct commit message layout** so it shows up on JIRA.

Must read: [How to Write a Git Commit Message, by Chris Beams](https://cbea.ms/git-commit/)

```
Line 1: SHARE-XXX Fitting Title (< 50 chars)
Line 2: empty line
Lines 3 - X: actual commit message; first focusing on WHY then focusing on WHAT you have changed.
```

Squash your commits, there must be only 1 commit! (X is the number of your commits)

```
git reset --soft HEAD~X &&
git commit
```

When there is only one commit and you just need to change the commit message you can use:

```
git commit --amend
```

Go to GitHub and **create a Pull-Request** from your forked repository into the official repository.

- check that you create the PR from your correct ticket branch!
- check that PR targets the _main_ branch!
- "allow edits from maintainers" must be ticked off, this allows the Seniors to rebase your PR if needed

## After creating the PR

**The PR has merge-conflicts?**<br/>Resolve them and rebase your PR.

```
git checkout main
git pull catroweb main
git checkout SHARE-XXX
git rebase -i main
```

Resolve the conflicts. E.g. Phpstorm has great tools to resolve such conflicts.

```
git rebase --continue
git push -f
```

**I need to make changes?**<br>After resolving the changes, don't forget to squash your commits and force push the branch.
