# Catroweb Coding Standard

A project should look like it was written by one developer. **Consistency** and clean style significantly improve readability and maintainability. In Catroweb, the following commands are the standard local checks.


### PHP (Symfony settings with a few overrrides)
- `bin/php-cs-fixer fix --dry-run --allow-risky=yes --verbose --format=txt` to test
- `bin/php-cs-fixer fix --allow-risky=yes --verbose --format=txt` to automatically try to fix the problems

or
- `yarn test-php` to test
- `yarn fix-php` to automatically try to fix the problems

### Javascript (NPM Standard)
- `yarn test-js` to test
- `yarn fix-js` to automatically try to fix the problems


### SASS / CSS
- `yarn test-css` to test
- `yarn fix-css` to automatically try to fix the problems


### All of the above (PHP + JS + CSS)
- `yarn test` to test all
- `yarn fix` to automatically try to fix all the problems


### Additional information:
- All .scss, .js (and image) files should be in the assets folder.<br>
Do not create/move them directly to the public folder. This is handled by the frontend build.

- For more details feel free to look up the config files in the project if necessary.

- The Continuous Integration system will automatically check your code style on every pull request, but will not fix it and fail on errors. It is advised to run the mentioned commands before pushing.
