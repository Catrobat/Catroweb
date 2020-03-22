#!/usr/bin/env bash

rm -rf var/{cache,log}/*
sh docker/app/set-permissions.sh
grunt
bin/console cache:clear -e test
bin/console catrobat:test:generate --force
bin/console assets:install --symlink public
echo "Test environment prepared"
