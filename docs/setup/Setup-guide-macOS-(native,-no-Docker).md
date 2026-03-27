# Setup guide macOS (native, no Docker)

This guide sets up **Catroweb** on **macOS** using **system Apache**, **PHP-FPM**, **MariaDB**, **Node**, and **Elasticsearch**.

> Note: Catroweb’s default config expects Elasticsearch on plain HTTP at `http://localhost:9200/` (`ELASTICSEARCH_URL`, `ES_HOST`, `ES_PORT`). The Docker dev setup also uses `http://localhost:9200`. We recommend ES 8.17.x for local development.

## 1) Prerequisites

- macOS 12+ (Monterey or newer)
- Xcode Command Line Tools
- Homebrew

### Install Xcode CLI tools

```
xcode-select --install
```

### Install Homebrew

```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

## 2) Install required packages

### Git

```
brew install git
```

### PHP (via Homebrew) + PHP-FPM

> PHP via Apache `mod_php` was removed from macOS 12+. Use PHP-FPM + Apache proxy_fcgi.

```
brew install php
brew services start php
```

Verify PHP-FPM listens on 9000:

```
grep -n "^listen" /opt/homebrew/etc/php/*/php-fpm.d/www.conf
```

It should show:

```
listen = 127.0.0.1:9000
```

### Composer

```
brew install composer
```

### Node / npm

```
brew install nvm
mkdir -p ~/.nvm
```

Add to `~/.zshrc`:

```
export NVM_DIR="$HOME/.nvm"
source "$(brew --prefix nvm)/nvm.sh"
```

Then:

```
nvm install node
node -v
npm -v
```

### MariaDB (MySQL)

```
brew install mariadb
brew services start mariadb
mysql_secure_installation
```

Create dev + test DBs:

```
mysql -u root -p
```

```
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;

CREATE DATABASE catroweb_dev;
CREATE DATABASE catroweb_test;
EXIT;
```

## 3) Apache setup (system Apache) + vhost + symlink

### 3.1 Ensure you’re using system Apache, not Homebrew httpd

If you previously installed Homebrew Apache:

```
brew services stop httpd 2>/dev/null
brew uninstall httpd 2>/dev/null
```

System Apache uses `/etc/apache2` and logs at `/private/var/log/apache2/`.

### 3.2 Create symlink like Ubuntu setup

The Ubuntu setup script symlinks the repo into `/var/www/catroweb`.

From your Catroweb repo root:

```
cd /Users/<you>/path/to/Catroweb
sudo mkdir -p /var/www
sudo ln -s "$(pwd)" /var/www/catroweb
ls -l /var/www
```

### 3.3 Enable required Apache modules

Edit:

```
sudo nano /etc/apache2/httpd.conf
```

Ensure these lines are uncommented:

```apache
LoadModule rewrite_module libexec/apache2/mod_rewrite.so

LoadModule proxy_module libexec/apache2/mod_proxy.so
LoadModule proxy_fcgi_module libexec/apache2/mod_proxy_fcgi.so

Include /private/etc/apache2/extra/httpd-vhosts.conf
```

Optional to remove the common warning:

```apache
ServerName localhost
```

Restart:

```
sudo apachectl configtest
sudo apachectl restart
```

### 3.4 Add vhost

Edit:

```
sudo nano /etc/apache2/extra/httpd-vhosts.conf
```

Add:

```apache
<VirtualHost *:80>
  ServerName catroweb
  DocumentRoot "/var/www/catroweb/public"

  <Directory "/var/www/catroweb/public">
    Options +FollowSymLinks
    AllowOverride All
    Require all granted
    DirectoryIndex index.php
    FallbackResource /index.php
  </Directory>

  # PHP via PHP-FPM (brew), default listens on 127.0.0.1:9000
  <FilesMatch \.php$>
    SetHandler "proxy:fcgi://127.0.0.1:9000"
  </FilesMatch>
</VirtualHost>
```

Add hosts entry:

```
sudo sh -c 'echo "127.0.0.1 catroweb" >> /etc/hosts'
```

Restart:

```
sudo apachectl configtest
sudo apachectl restart
```

Open:

http://catroweb

## 4) Elasticsearch (recommended: 8.17.x via archive)

Install ES 8.17.x from the official archive.

### 4.1 Download (choose your architecture)

Check architecture:

```
uname -m
```

- `arm64` → Apple Silicon
- `x86_64` → Intel

Example for 8.17.0 (replace version if needed):

For Apple Silicon:

```
cd ~/Downloads
curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-8.17.0-darwin-aarch64.tar.gz
curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-8.17.0-darwin-aarch64.tar.gz.sha512
shasum -a 512 -c elasticsearch-8.17.0-darwin-aarch64.tar.gz.sha512
tar -xzf elasticsearch-8.17.0-darwin-aarch64.tar.gz
```

For Intel:

```
cd ~/Downloads
curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-8.17.0-darwin-x86_64.tar.gz
curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-8.17.0-darwin-x86_64.tar.gz.sha512
shasum -a 512 -c elasticsearch-8.17.0-darwin-x86_64.tar.gz.sha512
tar -xzf elasticsearch-8.17.0-darwin-x86_64.tar.gz
```

Move it:

```
sudo mkdir -p /usr/local/elasticsearch
sudo mv elasticsearch-8.17.0 /usr/local/elasticsearch/elasticsearch-8
```

### 4.2 Configure local dev settings

```
sudo nano /usr/local/elasticsearch/elasticsearch-8/config/elasticsearch.yml
```

Add/ensure:

```yaml
cluster.name: catroweb-dev
node.name: node-1
network.host: 127.0.0.1
http.port: 9200
xpack.security.enabled: false
```

> **Note:** `xpack.security.enabled: false` disables TLS/auth for local development. ES 8.x enables security by default; without this setting, you'd need HTTPS and credentials.

### 4.3 Start Elasticsearch

```
cd /usr/local/elasticsearch/elasticsearch-8
./bin/elasticsearch
```

Verify:

```
curl -s http://localhost:9200 | head
```

## 5) Catroweb project setup

Clone and install dependencies:

```
git clone https://github.com/Catrobat/Catroweb.git
cd Catroweb
composer install
yarn install
```

Reset dev DB + dummy data:

```
php bin/console catrobat:reset --hard
```

Build assets:

```
yarn dev
```

## 6) Troubleshooting

### Apache says “Address already in use”

Find the process:

```
sudo lsof -nP -iTCP:80 -sTCP:LISTEN
```

### Browser shows raw PHP code

PHP isn’t wired to Apache. Ensure:

- `proxy_module` + `proxy_fcgi_module` enabled
- vhost has `SetHandler "proxy:fcgi://127.0.0.1:9000"`

### 403 Forbidden / symlink not accessible

Ensure vhost directory has:

```apache
Options +FollowSymLinks
Require all granted
```

And make sure Apache can traverse the symlink target folders (on macOS, `_www` needs execute permission on parent directories).

### Elasticsearch errors like “Unknown error:52”

Usually means ES has security/TLS enabled (ES 8.x default). Ensure `xpack.security.enabled: false` in `elasticsearch.yml` for local development, or set up TLS certificates.

## 7) Useful references

- Default Elasticsearch env vars in Catroweb `.env`: `ELASTICSEARCH_URL=http://localhost:9200/`, `ES_HOST=localhost`, `ES_PORT=9200`.
- Docker dev env sets `ELASTICSEARCH_URL=http://elasticsearch:9200/`.
- Ubuntu setup installs Elasticsearch from the 8.x repo.
