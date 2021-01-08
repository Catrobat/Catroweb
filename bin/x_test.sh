#!/bin/bash
grunt
npm run encore dev
rm -rf var/cache/test/*
bin/console cache:clear --env=test
php bin/phpunit tests
read -p "Check for failed tests. Press any key to continue." any_key
php bin/phpspec run
read -p "Check for failed tests. Press any key to continue." any_key
read -p "Make sure Chrome is running. Press any key to continue." any_key
php bin/behat
