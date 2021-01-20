#!/usr/bin/env bash

rm -rf var/cache/*
rm -rf var/log/*
HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
sudo chmod o+w public/resources/ -R
sudo chmod o+w public/resources_test/ -R
composer install
npm install
bin/console catrobat:reset --hard
grunt
npm run encore dev
