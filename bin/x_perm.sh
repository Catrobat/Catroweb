rm -rf var/cache/*
rm -rf var/log/*

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log 

sudo chmod o+w public/resources/ -R
sudo chmod o+w public/resources_test/ -R

bin/console cache:clear -e test
bin/console cache:clear -e prod
bin/console cache:clear -e dev

###########

rm -rf var/cache/*
rm -rf var/log/*

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log

sudo chmod o+w public/resources/ -R
sudo chmod o+w public/resources_test/ -R