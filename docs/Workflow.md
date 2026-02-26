# Workflow 


### Forking Workflow

Do not create any branches on the official public repository. Instead: Fork the project and use Pull Requests to merge back into develop.
[(Atlassian Tutorial explaining this workflow)](https://www.atlassian.com/git/tutorials/comparing-workflows/forking-workflow)


### Official Public Repository Branches

Never push any changes directly to those branches! Always create Pull Requests!
- **develop**: The develop branch is our development branch and represents the current state of the project including all already merged changes for the next release. This is the branch you should be working with and merge your PRs into. 
- **master**: The master branch represents the current state of the [share](https://share.catrob.at/pocketcode/) and should be the only branch that gets released to the public. 
- **release/XYZ**: Before releasing a new release branch is created (from _develop_). This branch will be tested and hot-fixed, but no new features will be added. When releasing this branch will be merged into _develop_ and _master_ and then deleted.


## Checking out the newest version of the project

Before starting to work on your ticket make sure to work with the latest version of the projects official public repository, lets call the remote _catroweb_.
  ```
  git checkout develop
  git pull catroweb develop
  ```

Do not forget to reset the project. 
  ```
  # install packages
  composer install
  npm install

  # reset db
  bin/console catrobat:reset --hard

  # build assets
  npm run dev
  ```

Then create and check out a new branch for your ticket XXX.
  ```
  git checkout -b SHARE-XXX
  ```

That's it! Now you can start working on your ticket.


## Creating a Pull Request

**Test everything!** When tests are failing the PR will not be accepted.

Also, make sure that every commit has the **correct commit message layout** so it shows up on [JIRA](https://jira.catrob.at/).

Must read: [How to Write a Git Commit Message, by Chirs Beams](http://chris.beams.io/posts/git-commit/)
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
  - check that PR goes to the _develop_ branch and not _master_ branch!
  - "allow edits from maintainers" must be ticked off, this allows the Seniors to rebase your PR if needed


## After creating the PR

**The PR has merge-conflicts?**<br/>Resolve them and rebase your PR.

  ```
  git checkout develop
  git pull catroweb develop
  git checkout SHARE-XXX
  git rebase -i develop
  ```

Resolve the conflicts. E.g. Phpstorm has great tools to resolve such conflicts.
  ```
  git rebase --continue
  git push -f
  ```

**I need to make changes?**<br>After resolving the changes, don't forget to squash your commits and force push the branch.
 