#!/usr/bin/env bash

# In case you need access to container libraries just run this command from the root project directory.
# It copies the vendor and node_modules from the container to the host.
#
# A simple docker cp might be enough, but on windows machines this commands often crashes.
# Therefore this tar.gz workaround is used.
#
docker exec app.catroweb mkdir sharedLibraries
docker exec app.catroweb tar -czf libraries.tar.gz node_modules vendor
docker exec app.catroweb mv libraries.tar.gz sharedLibraries
docker cp app.catroweb:/var/www/catroweb/sharedLibraries ./
docker exec app.catroweb rm -rf sharedLibraries
rm -Rf vendor
rm -Rf node_modules
chmod 777 -R sharedLibraries
cd sharedLibraries || exit
tar -xf libraries.tar.gz
mv vendor ../
mv node_modules ../
cd ..
rm -Rf sharedLibraries
