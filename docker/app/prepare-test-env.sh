#!/usr/bin/env bash

rm -rf var/{cache,log}/*
sh docker/app/set-test-permissions.sh
grunt
bin/console cache:clear -e test
bin/console catrobat:test:generate --force
echo "Test environment prepared"