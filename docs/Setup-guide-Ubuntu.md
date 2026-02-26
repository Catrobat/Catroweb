# Setup Guide Ubuntu (WSL)

Fork and check-out the project

```
# Setup the Catroweb project
git clone <your repo URL>
cd Catroweb
git remote add catroweb git@github.com:Catrobat/Catroweb.git
git checkout develop
git pull catroweb develop
```

Then run this script: `./bin/initial_setup.sh`
Just use root as the new DB password, and also answer Y to all DB related questions :)

```
#!/bin/bash

## This script is used to install the necessary packages for a new Ubuntu 24 distro

# Start by upgrading and updating the system
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php # Press enter to confirm.
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
yarn install
composer install
php bin/console catrobat:reset --hard
yarn dev

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
```

---- Or manually:

### 1. Setup operating system

Download and install **Ubuntu** from [here.](http://www.ubuntu.com/) You need Ubuntu 24.04 or higher to install MariaDB from apt for this step to work smoothly! Also works with WSL, just visit the Microsoft Store.

If you are already using an old Ubuntu version, here is a quick how-to upgrade:

```
sudo apt update
sudo apt upgrade
sudo do-release-upgrade
```

_(if this command fails, you have to open the file /etc/update-manager/release-upgrades with Sudo rights and change promt=lts to promt=normal)_
_Follow the installation guide (several times pressing y)_

### 2. Install prerequisites:

```
sudo apt install php8.5-common php8.5-ldap php8.5-cli php8.5-curl php8.5-intl php8.5-apcu php8.5-imagick php8.5-mbstring php8.5-xml php8.5-fpm php8.5-mysql php8.5-gd php8.5-zip
sudo apt install apache2 curl npm composer acl
curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash
nvm install node
```

#### Install and Setup Git

```
sudo apt install git
git config --global user.name "user_name"
git config --global user.email "email_id"
```

### 3. Install MariaDB:

```
sudo apt install mariadb-server
```

```
sudo mysql_secure_installation
```

```
sudo apt install mariadb-client
```

#### Configure and **set up MariaDb**:

```
sudo phpenmod mbstring
sudo systemctl restart apache2
sudo mysql
update mysql.user set plugin='' where user='root';
update mysql.user set password=password('root') where user='root';
FLUSH PRIVILEGES;
create database catroweb_test;
create database catroweb_dev;
exit
```

Now you should be able to login to phpMyAdmin with **username**: root **passsword**: 'root'

### 4. Install **elasticsearch**

https://ourcodeworld.com/articles/read/1508/how-to-install-elasticsearch-7-in-ubuntu-2004

```
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list
sudo apt update
sudo apt install elasticsearch
sudo systemctl start elasticsearch
sudo systemctl enable elasticsearch
```

Check if it works with:

```
curl -X GET "localhost:9200/?pretty"
```

### 5. Go to https://github.com/Catrobat/Catroweb and **fork** the repository

### 6. Setup Catroweb:

```
  git clone <your-forked-repo>
  cd Catroweb
  git remote add catroweb https://github.com/Catrobat/Catroweb.git
  git checkout develop
  git pull catroweb develop
```

```
 yarn install
 composer install
 php bin/console catrobat:reset --hard
 yarn dev
```

### 8. Setup Apache :

```
sudo ln -s PATH/TO/YOUR/CATROWEB_PUBLIC/FOLDER /var/www/catroweb
sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/catroweb.conf
```

Edit the catroweb.conf file

```
sudo gedit /etc/apache2/sites-available/catroweb.conf
```

to look like this:

```
      ...

	ServerName catroweb
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/catroweb
	<Directory /var/www/catroweb>
		DirectoryIndex /index.php
		FallbackResource /index.php
	</Directory>
      SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
      ...
```

Add at the top of the hosts file:

> 127.0.0.1 catroweb

```
sudo gedit /etc/hosts
```

Then set the correct configuration and restart apache2

```
sudo a2dissite 000-default.conf
sudo a2ensite catroweb.conf
sudo service apache2 restart
```

### 9. Install **Google Chrome** for testing

```
sudo apt install gdebi-core wget
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo gdebi google-chrome-stable_current_amd64.deb
```

### 10. Go to the root of the project & and generate the ssh keys and set the **permissions**:

```
sh ./docker/app/init-jwt-config.sh
sh ./docker/app/set-permissions.sh
```
