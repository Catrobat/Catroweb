#!/bin/bash
bin/console cache:clear --env=test
php bin/phpunit tests
read -n1 -r -p "Check for failed tests" any_key
php ./bin/php-cs-fixer fix --allow-risky=yes --verbose --format=txt
read -n1 -r -p "Check for failed tests" any_key
php bin/phpspec run
read -n1 -r -p "Make sure Chrome is running. Press any key to continue." any_key
php bin/behat
