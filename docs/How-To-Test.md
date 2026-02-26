# Testing

In the Catroweb project, we use a mix up between behavior-driven and test-driven development. Both philosophies require developers to write tests before implementing code. New lines of code must only be written if a test case fails! Therefore, the project should easily high code coverage and developers have to think about a problem before starting to code. Hence, the quality should be improved. The main difference between BDD and TDD: BDD tests are written in a human-readable language (Gherkin + PHP) and TDD uses regular code PHP).

To ensure the quality of every contribution to the project, a CI system runs various static and dynamic analysis checks on every pull request. After that, a developer of the Catroweb team does a manual code review (mainly Seniors, but everyone is allowed and encouraged to do so). After that, the product owners do an additional review. More information about the CI system (=GitHub Actions) can be found [here](./GitHub-Actions-FAQ-(Workflow-Automation).md).

This section focuses on what you as a developer can do to test your contributions locally. It is recommended to test your contributions already on your own machine before creating a pull request. This section describes how to test the Share community platform. Besides, you find various tips on how to improve the testing process. However, the provided information is only a short overview, for more information just search the web - It is full of tutorials and documentation. Besides, you can always take a look into the automated workflows of the GitHub Actions to see how tests are executed automatically (`.github`).

# Dynamic testing - Testing Automation Frameworks:
Dynamic tests evaluate the software while it is running. Therefore, they can tests not just the implementation but also the behavior. However, only the executed paths are tested. Therefore, it es crucial to develop test cases for every scenario. Following best practices like the testing-pyramid can reduce the testing overhead. In a nutshell, the testing pyramid points out one should write many simple unit tests and only a few complex tests. Code coverage metrics provide feedback on how much is tested (not how well!). For our project, we use two different testing automation frameworks: [PHPUnit](https://phpunit.de/)([PHPUnit Tutorial](https://phpunit.de/getting-started/phpunit-9.html)) and Behat ([Behat Tutorial](http://behat.org/en/latest/guides.html)). 

##### Step 0:
To run the tests for the Catroweb project you need a full set up dev or test environment.
You can either follow the tutorial to set up the project natively or use Docker.

E.g.:
For testing with docker, there is a [testing container](./Docker.md#running-catroweb-test-in-docker) available.

##### Step 1:
Before starting your tests clear the cache for the test environment! 
```
bin/console cache:clear --env=test
```

##### Step 2:
Run the tests. Pro-tip to save valuable time: Only run the test-suites you are interested in on every change. Running them all at once takes up multiple hours. Only run them all at once, before creating a PR. However, you could also use the CI to run all tests optimized with parallel running suites. Just make sure to create a Draft pull request, until your work is finished. Else someone might start to review your pull request.

## PhpUnit
PHPUnit tests are not limited. You can write anything from unit tests to functional tests with them. However, the goal of our project is to write mainly unit tests with PHPUnit. Hence, the PHPUnit tests should be very fast and can be executed frequently.

#### Where do I find the files for these tests?
- Configuration: `phpunit.xml(.dist)?`
- Tests: `tests/PhpUnit/`

#### How do I run these tests?
```bin/phpunit tests```

#### How do I run a single test?
```bin/phpunit tests --filter <methode-name> <file-path>```

#### How do I generate code coverage reports?
```bin/phpunit --coverage-html tests/TestReports/CoverageReports/PhpUnit --coverage-clover=tests/TestReports/CoverageReports/PhpUnit/coverage.xml```

## Behat
Behat is a StoryBDD tool. It is optimized for behavior-driven development (BDD). With Behat, tests are written as feature specification in the Gherkin format - a human-readable business language. Therefore, tests can be specified and read even by non-developers. The actual logic is hidden in PHP context files and mapped to the specifications. Currently, Behat consists of around ~50% UI tests. UI tests are very slow. Consider writing many unit tests and only a few UI integration tests.

#### Where do I find the files for these tests?
- Configuration: `behat.yml(.dist)?`
- Feature descriptions: `tests/BehatFeatures/web/`
- Context logic: `src/System/Behat/Context/`

#### How do I run these tests?
Step 0: Since most tests are UI tests it is essential to start the headless chrome in the background before running the tests:
```
google-chrome-stable --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222
```

Step 1: Next, you could run all tests. However, it will take up a few hours:
```
bin/behat
```

#### How to run a single test suite?
To get the most recent suites, take a look into behat.yml.dist inside the root of the repository. E.g.: `web-admin`:
```
bin/behat -s web-admin
```
If you create new suites - Also add them to the CI! (`.github/tests.yml`)

#### How to run a single test case?

Provide the path to the file to run only a specific feature file. (Could also be a whole directory!) If you add the line number of a scenario to the path you can run a particular scenario. Make sure to define the suite when you are checking a specific feature. The test will be way much faster, since not every hook from every suite must be executed. Just from that one suite.
```
bin/behat -s web-admin tests/BehatFeatures/web/admin/<file-name>.feature:<line-number>
```

#### What if a test case fails?
- UI tests that fail generate a screenshot: `tests/TestReports/TestScreenshots/`
- Additional info might be logged in **errors.json** in `tests/TestReports/Behat/`

#### I have no idea why a test failed?
- The **first Behat UI-test** might fail after clearing the cache.
Just try to rerun the failed tests again.

- **Flaky tests**: Some Behat tests might just fail because of timing issues. In the end, just rerun the failed tests. This will just execute the failed tests again. (Attention: it will not rerun, pending/missing steps or chrome expectations!)
```
bin/behat --rerun
```

- **Chrome Exceptions** Behat + Mink + Chrome may result in stream read exception killing the whole Behat test run.
In order to prevent this (a simple solution)-> use fewer features per suite. It seems to be a problem with the cache of Symfony within a suite.
Maybe try a different chrome version (docker-compose) or try tweaking the settings.
Might use a different version for the `dmore/chrome/driver`. Or maybe you can find a better solution to the problem!

- After a fresh project setup: you might get fatal errors. Just reset **SQLite permissions**:
```
chmod o+w+x tests/BehatFeatures/sqlite/ -R
```

- **Caching issues:** If your changes in feature files seem to change nothing you can try to clear the cache
```
bin/console cache:clear -e test
```

---
# Static Analysis

Static analysis does not require the software to be executed. Every path and its implementation is tested by just looking at the source code. Compared to dynamic tests, automated static testing is cheap since no test cases have to be developed. The included checks in this project vary between code style issues and programming errors to the detection of deprecated functionality or duplicated code. All static checks are also integrated into the CI services. However, the CI system will not fix the errors for you, just highlight them. Hence to locally run the tools, the following tools are the most important ones for you. More information about the coding standards can be found [here](./Coding-Standard.md).

- [Eslint](https://eslint.org/):
  - Statically analyzes javascript code (depends on node and npm)
  - Config defined in `.eslintrc.yml`
  - Run: `npm run test-js`
  - Automatic fix support: `npm run fix-js`

- [StyleLint](https://stylelint.io/):
  - Statically analyzes Sass and CSS files (depends on node and npm)
  - Config defined in `.stylelintrc.json`
  - Run: `npm run test-css`
  - Automatic fix support: `npm run fix-css`

- [Php-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer):
  - Reports code style issues in php files
  - config defined in `.php_cs(.dist)`
  - Run: `npm run test-php` or `bin/php-cs-fixer fix --diff --dry-run --allow-risky=yes --verbose --format=txt`
  - Automatic fix support: `npm run fix-php` or `bin/php-cs-fixer fix --diff --allow-risky=yes --verbose --format=txt`

- [PhpStan](https://phpstan.org/):
  - Statically analyzes php files (The more strongly-typed the code is, the more information we get)
  - config defined in `phpstan.neon(.dist)`
  - Run: `bin/phpstan analyze`
  - Has multiple levels of details!

- [Psalm](https://phpstan.org/):
  - Statically analyzes php files (The more strongly-typed the code is, the more information we get)
  - config defined in `psalm.xml(.dist)`
  - Run: `bin/psalm`
  - Has multiple levels of details!

The other tools integrated into CI are:
- [Copy paste detector](https://github.com/sebastianbergmann/phpcpd)
- [Statistics](https://github.com/sebastianbergmann/phploc)
- Symfony's built-in linters:
```
bin/console lint:twig templates/
bin/console lint:yaml translations/ config/ .github/ docker/ behat.yml.dist .eslintrc.yml
bin/console lint:container
```




 
