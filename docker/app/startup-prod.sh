#!/usr/bin/env bash

if ./docker/app/wait-for-it.sh db.catroweb.dev:3306 -t 60; then
    /usr/sbin/apache2ctl -D FOREGROUND
fi
