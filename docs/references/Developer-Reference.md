# Developer Reference

Central quick-reference for day-to-day Catroweb development.

## Core Console Commands

| Command                                   | Purpose                                  |
| ----------------------------------------- | ---------------------------------------- |
| `bin/console list`                        | List Symfony console commands            |
| `bin/console about`                       | Show environment/runtime information     |
| `bin/console catrobat:reset --hard`       | Recreate DB, seed data, clear cache      |
| `bin/console doctrine:migrations:status`  | Show migration status                    |
| `bin/console doctrine:migrations:migrate` | Run pending migrations                   |
| `bin/console doctrine:migrations:diff`    | Generate a migration from entity changes |
| `bin/console cache:clear`                 | Clear default cache                      |
| `bin/console cache:clear --env=test`      | Clear test cache                         |

## Testing Quick Commands

| Command                                 | Purpose                             |
| --------------------------------------- | ----------------------------------- |
| `bin/phpunit tests`                     | Run PHPUnit test suite              |
| `bin/behat`                             | Run all Behat suites                |
| `bin/behat -s <suite>`                  | Run one Behat suite                 |
| `bin/behat -f pretty`                   | Run Behat with detailed output      |
| `bin/behat path/to/file.feature`        | Run scenarios from one feature file |
| `bin/behat path/to/file.feature:<line>` | Run one scenario by line number     |

If you use Docker locally, prefix commands with:

```bash
docker exec -it app.catroweb <command>
```

Example:

```bash
docker exec -it app.catroweb bin/phpunit tests
```

## Frontend / Tooling Commands

| Command      | Purpose                               |
| ------------ | ------------------------------------- |
| `yarn test`  | Run all configured static checks      |
| `yarn fix`   | Apply automatic fixes where supported |
| `yarn dev`   | Build frontend assets for development |
| `yarn watch` | Rebuild assets on file changes        |
| `yarn build` | Build production assets               |

## External Documentation

- [Symfony Framework docs](https://symfony.com/)
- [Symfony Quick Tour](https://symfony.com/doc/current/quick_tour/the_big_picture.html)
- [PHPUnit docs](https://phpunit.de/)
- [Behat docs](http://behat.org/en/latest/)
- [Composer docs](https://getcomposer.org/)
- [Packagist](https://packagist.org/)
- [Sass docs](https://sass-lang.com/)
- [Webpack Encore docs](https://symfony.com/doc/current/frontend/encore/simple-example.html)
- Project-specific Webpack notes: [Using Webpack (material components)](<../development/Using-Webpack-(material-components).md>)
