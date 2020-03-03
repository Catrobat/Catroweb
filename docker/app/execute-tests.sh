#!/usr/bin/env bash

/usr/sbin/apache2ctl start

printf "\n START TESTS: \n"
printf "\n START PHPSPEC TESTS: \n"
bin/phpspec run --format=dot

printf "\n START PHPUNIT TESTS: \n"
bin/phpunit tests

printf "\n START BEHAT TESTS: \n"
bin/behat
