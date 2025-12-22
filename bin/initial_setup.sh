#!/bin/bash

## This script is used to install the necessary packages for a new Ubuntu 24 distro

# Start by upgrading and updating the system
sudo apt update
sudo apt upgrade

# Install the necessary packages
sudo apt install curl acl

## Php and its extensions, composer (php package manager)
sudo apt install php8.5-common php8.5-ldap php8.5-cli php8.5-curl php8.5-intl php8.5-apcu php8.5-imagick php8.5-mbstring php8.5-xml php8.5-fpm php8.5-mysql php8.5-gd php8.5-zip php8.5-bcmath
sudo apt install composer

## Node and npm (node package manager)
curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash
nvm install node
sudo apt install npm

## Git (Version control system)
sudo apt install git
echo "Please enter your git username:"
read git_username
git config --global user.name "$git_username"
echo "Please enter your git email:"
read git_email
git config --global user.email "$git_email"

## MySQL (Database)
sudo apt install mariadb-server
sudo apt install mariadb-client
sudo service mariadb start
sudo mysql_secure_installation

## Web server (Apache)
sudo apt install apache2 libapache2-mod-php
sudo a2enmod php8.5
sudo service apache2 start

## Install google chrome browser (needed for testing with selenium/behat)
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo apt install ./google-chrome-stable_current_amd64.deb
rm google-chrome-stable_current_amd64.deb

## Install elastic search
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list
sudo apt update
sudo apt install elasticsearch
sudo service elasticsearch start

## Install the project dependencies
npm install
composer install
php bin/console catrobat:reset --hard
npm run dev

## Setup the database with a development and test database
sudo phpenmod mbstring
sudo mysql -e "
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;
CREATE DATABASE catroweb_test;
CREATE DATABASE catroweb_dev;
"

## Setup the Apache configuration
### Create a symbolic link in /var/www
sudo ln -s $(pwd) /var/www/catroweb
### Copy the Apache configuration file
sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/catroweb.conf
### Define the path to the configuration file
CONF_FILE="/etc/apache2/sites-available/catroweb.conf"
echo "
<VirtualHost *:80>
    Servername catroweb
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/catroweb
    <Directory /var/www/catroweb>
        DirectoryIndex /index.php
        FallbackResource /index.php
    </Directory>
    SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=\$1
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
" | sudo tee $CONF_FILE
### Add entry to hosts file
echo "127.0.0.1 catroweb" | sudo tee -a /etc/hosts
### Set the correct Apache configuration
sudo a2dissite 000-default.conf
sudo a2ensite catroweb.conf
sudo service apache2 restart

## Setup some final permissions
sh ./docker/app/init-jwt-config.sh
sudo sh ./docker/app/set-permissions.sh

echo "Setup complete. You can now access the project at http://catroweb"