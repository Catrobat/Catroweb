# Contributing to Catroweb

:tada: Firstly, thank you for contributing to Catroweb! You are one of the pandas now! :tada:

**PR's** are always appreciated! Please be sure to fill out the
[PR Template](https://github.com/Catrobat/Catroweb-Symfony/blob/develop/.github/pull_request_template.md)
as detailed as possible. You will be the hero of everyone who does code reviews. Every pull request will be automatically
checked against various tests in our GitHub Actions CI system. Make sure all tests are passing!
When tests are failing the PR will not be accepted.

### Guides

There are comprehensive Guides and Cheat Sheets available in our [wiki](https://github.com/Catrobat/Catroweb-Symfony/wiki/).
Make sure to check them out if you have any questions, before asking a project maintainer.

#### Workflow (TDD/BDD)

1. Plan your work
2. Write tests (that fail)
3. Implement your logic
4. Check that all tests pass
5. Repeat 1-4 if necessary

##### 6. Create a Pull Request:

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
- check that PR goes to the `develop` branch and not `main` branch!
- enable "allow edits from maintainers", this allows the Seniors to rebase your PR if needed

##### 7. Keep your PR up-to-date and be responsive to requested changes. Make sure the CI tests pass

- **merge-conflicts?** Resolve them and rebase your PR.<br/>

  ```
  git checkout develop
  git pull catroweb develop
  git checkout SHARE-XX
  git rebase -i develop
  ```

  Resolve conflicts if necessary (Note: IDE's like Phpstorm have great tools to resolve such conflicts).

  ```
  git rebase --continue
  git push -f
  ```

- **requested changes?**

  Feel free to discuss them with your code reviewer before blindly accepting them.
  Then if necessary apply the changes.
  After resolving the changes, don't forget to squash your commits and force push the branch.
