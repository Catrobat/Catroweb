#!/usr/bin/env bash

# Prepares app.catroweb container to be able to use our deploy script:
# - Installs ssh
# - Copies local ssh config and keys
# - Sets correct permissions
#
# Requires you to have access to live share server via a ssh key to be able to deploy afterwards
# Ask someone from Catroweb or DevOps if you have any questions
#
# Note: Run outside of the container

docker exec app.catroweb apt install -y ssh
docker cp ~/.ssh app.catroweb:/root/
docker exec app.catroweb chmod -R 700 /root/.ssh && docker exec app.catroweb chown -R root /root/.ssh
docker exec app.catroweb rm /root/.ssh/known_hosts