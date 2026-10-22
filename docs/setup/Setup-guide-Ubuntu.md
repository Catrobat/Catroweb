# Setup Guide Ubuntu (native, also WSL)

Docker is the recommended path ([Developer Quickstart](Developer-Quickstart.md)). This is the native alternative on Ubuntu 24.04+ (needed for MariaDB from apt).

## 1. Packages

```bash
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt update && sudo apt upgrade
sudo apt install php8.5-common php8.5-ldap php8.5-cli php8.5-curl php8.5-intl php8.5-apcu php8.5-imagick \
  php8.5-mbstring php8.5-xml php8.5-fpm php8.5-mysql php8.5-gd php8.5-zip php8.5-bcmath \
  composer apache2 libapache2-mod-php curl acl git mariadb-server mariadb-client
curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash
nvm install node && corepack enable
sudo phpenmod mbstring
sudo a2enmod php8.5
```

Google Chrome (for Behat):

```bash
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo apt install ./google-chrome-stable_current_amd64.deb && rm google-chrome-stable_current_amd64.deb
```

## 2. MariaDB

```bash
sudo service mariadb start
sudo mysql -e "
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;
CREATE DATABASE catroweb_test;
CREATE DATABASE catroweb_dev;
"
```

## 3. Elasticsearch

```bash
curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elasticsearch-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/elasticsearch-keyring.gpg] https://artifacts.elastic.co/packages/9.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-9.x.list
sudo apt update && sudo apt install elasticsearch
# ES 9 enables security by default; disable it for local development
grep -q '^xpack.security.enabled' /etc/elasticsearch/elasticsearch.yml \
  && sudo sed -i 's/^xpack.security.enabled:.*/xpack.security.enabled: false/' /etc/elasticsearch/elasticsearch.yml \
  || echo 'xpack.security.enabled: false' | sudo tee -a /etc/elasticsearch/elasticsearch.yml
sudo systemctl enable --now elasticsearch
curl "localhost:9200/?pretty"
```

To cap memory, put `-Xms1g` / `-Xmx1g` in `/etc/elasticsearch/jvm.options.d/jvm.options` and restart.

## 4. Catroweb

```bash
git clone <your-fork-url> && cd Catroweb
git remote add catroweb git@github.com:Catrobat/Catroweb.git
yarn install && composer install
sh ./docker/app/init-jwt-config.sh
php bin/console catrobat:reset --hard
yarn dev
```

## 5. Apache

```bash
sudo ln -s $(pwd) /var/www/catroweb
sudo tee /etc/apache2/sites-available/catroweb.conf <<'CONF'
<VirtualHost *:80>
    ServerName catroweb
    DocumentRoot /var/www/catroweb/public
    <Directory /var/www/catroweb/public>
        DirectoryIndex /index.php
        FallbackResource /index.php
    </Directory>
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
CONF
echo "127.0.0.1 catroweb" | sudo tee -a /etc/hosts
sudo a2dissite 000-default.conf && sudo a2ensite catroweb.conf && sudo service apache2 restart
sudo sh ./docker/app/set-permissions.sh
```

Open <http://catroweb>.
