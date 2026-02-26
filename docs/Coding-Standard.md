# Catroweb Coding Standard

A project should look like it was written by only one developer. **Consistency** and clean code style significantly improve the readability and hence the maintainability of code. The exact code style does not matter. In the Catroweb project, we mainly use de default industry standards:


### PHP (Symfony settings with a few overrrides)
- `bin/php-cs-fixer fix --dry-run --allow-risky=yes --verbose --format=txt` to test
- `bin/php-cs-fixer fix --allow-risky=yes --verbose --format=txt` to automatically try to fix the problems

or
- `npm run test-php` to test
- `npm run fix-php` to automatically try to fix the problems

### Javascript (NPM Standard)
- `npm run test-js` to test
- `npm run fix-js` to automatically try to fix the problems


### SASS / CSS
- `npm run test-css` to test
- `npm run fix-css` to automatically try to fix the problems


### All of the above (PHP + JS + CSS)
- `npm run test` to test all
- `npm run fix` to automatically try to fix all the problems


### Additional information:
- All .scss, .js (and image) files should be in the assets folder.<br>
Do not create/move them directly to the public folder. This is done automatically by npm.

- For more details feel free to look up the config files in the project if necessary.

- The Continuous Integration system will automatically check your code style on every pull request, but will not fix it and fail on errors. It is advised to run the mentioned commands before pushing.