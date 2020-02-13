# Contributing to Catroweb
:tada: Firstly, thank you for contributing to Catroweb! You are one of the pandas now! :tada:

## Styleguides
There is a comprehensive [styleguide](https://github.com/Catrobat/Catroweb-Symfony/wiki/Coding-Standard) available in our [wiki](https://github.com/Catrobat/Catroweb-Symfony/wiki/), aswell as a PHPStorm config file so you do not have to do a thing, just hitting `Refactor Code` and you are good to go! In the future there will be an automated report via Github Actions or our friendly Jenkins server, but this will take time.

## Pull requests
PR's are always appreciated! Please be sure to fill out the [PR Template](https://github.com/Catrobat/Catroweb-Symfony/blob/develop/.github/pull_request_template.md) as detailed as possible, you will be the hero of everyone who does code reviews
## Creating a Pull Request
**Test everything!** When tests are failing the PR will not be accepted.<br/><br/>
 Also, make sure that every commit has the **correct commit message layout** so it shows up on [JIRA](https://jira.catrob.at/).<br/>
  must read: [How to Write a Git Commit Message, by Chirs Beams](http://chris.beams.io/posts/git-commit/)
  ```
   Line 1: SHARE-XXX Fitting Title (< 50 chars)
   Line 2: empty line 
   Lines 3 - X: actual commit message; first focusing on WHY then focusing on WHAT you have changed.
  ```
Squash your commits, there must be only 1 commit! (XX is the number of your commits)
  ```
  git reset --soft HEAD~XX &&
  git commit
  ```
  When there is only one commit and you just need to change the commit message you can use:
  ```
  git commit --amend
  ``` 
 Go to GitHub and **create a Pull-Request** from your forked repository into the official repository.<br/>
   - check that you create the PR from your correct ticket branch!
   - check that PR goes to the _develop_ branch and not _master_ branch!
   - "allow edits from maintainers" must be ticked off, this allows the Seniors to rebase your PR if needed

## After creating the PR
**The PR has merge-conflicts?**<br/> Resolve them and rebase your PR.<br/>

```
git checkout develop
git pull catroweb develop
git checkout SHARE-XX
git rebase -i develop
```
Resolve the conflicts. E.g. Phpstorm has great tools to resolve such conflicts.
```
git rebase --continue
git push -f
```

**I need to make changes?** <br/>After resolving the changes, don't forget to squash your commits and force push the branch.
 
