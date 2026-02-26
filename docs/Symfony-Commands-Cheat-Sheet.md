|command|description|
| ---- | ---- |
| bin/console list | List console commands|
| bin/console catrobat:reset --hard|Recreate database, clear cache |
| bin/console doctrine:migrations:XXX | General command for handling migrations XXX can be: status, migrate, diff |
| bin/console fos:user:promote | With parameter --super promotes an user to an admin. <br /> For all options use parameter --help.|
| bin/console fos:user:demote | With parameter --super demotes an user to a "normal" user. <br /> For all options use parameter --help. |
| bin/console fos:user:change-password <username> <password> | Change the users password. <br /> For all options use parameter --help. |
| sudo service apache2 restart| Restart apache|
| bin/console cache:clear | Clear the cache |
| bin/console cache:clear --env=test | Clear the cache for the test environment|
| bin/console about | Useful information about the symfony environment|