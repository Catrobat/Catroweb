#!/usr/bin/env bash

# cache an dlog must be writable
sh docker/app/set-var-permissions.sh

# resource dirs must to be writable
chmod o+w public/resources/ -R
chmod o+w public/resources_test/ -R

# some test dirs must be writable
chmod o+w tests -R
