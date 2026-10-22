# Setup guide macOS (native, no Docker)

Docker is the recommended path ([Developer Quickstart](Developer-Quickstart.md)). This is the native alternative: system Apache + Homebrew PHP-FPM, MariaDB, Node and Elasticsearch. Requires Xcode Command Line Tools and [Homebrew](https://brew.sh).

## 1. Packages

```bash
brew install git php composer nvm mariadb librsvg sox
brew services start php        # PHP-FPM on 127.0.0.1:9000 (macOS 12+ has no mod_php)
brew services start mariadb
nvm install node && corepack enable   # after adding nvm to ~/.zshrc as brew prints
```

```sql
-- mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;
CREATE DATABASE catroweb_dev;
CREATE DATABASE catroweb_test;
```

## 2. Apache (system Apache, not Homebrew httpd)

```bash
cd /path/to/Catroweb
sudo mkdir -p /var/www && sudo ln -s "$(pwd)" /var/www/catroweb
```

In `/etc/apache2/httpd.conf` uncomment `mod_rewrite`, `mod_proxy`, `mod_proxy_fcgi` and `Include /private/etc/apache2/extra/httpd-vhosts.conf`. Add to `/etc/apache2/extra/httpd-vhosts.conf`:

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
  <FilesMatch \.php$>
    SetHandler "proxy:fcgi://127.0.0.1:9000"
  </FilesMatch>
</VirtualHost>
```

```bash
sudo sh -c 'echo "127.0.0.1 catroweb" >> /etc/hosts'
sudo apachectl configtest && sudo apachectl restart
```

## 3. Elasticsearch 9 (archive)

```bash
ARCH=$([ "$(uname -m)" = arm64 ] && echo aarch64 || echo x86_64)
V=9.4.2
cd ~/Downloads
curl -L -O https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-$V-darwin-$ARCH.tar.gz
tar -xzf elasticsearch-$V-darwin-$ARCH.tar.gz
sudo mkdir -p /usr/local/elasticsearch && sudo mv elasticsearch-$V /usr/local/elasticsearch/elasticsearch-9
```

Add to `config/elasticsearch.yml` (ES 9 enables TLS/auth by default; Catroweb expects plain `http://localhost:9200/`):

```yaml
network.host: 127.0.0.1
http.port: 9200
xpack.security.enabled: false
```

Start with `/usr/local/elasticsearch/elasticsearch-9/bin/elasticsearch`; check with `curl -s http://localhost:9200`.

## 4. Catroweb

```bash
composer install && yarn install
sh ./docker/app/init-jwt-config.sh
php bin/console catrobat:reset --hard
yarn dev
```

Open <http://catroweb>.

## Troubleshooting

- **Browser shows raw PHP**: `proxy_fcgi` not enabled or the vhost lacks the `SetHandler`.
- **403 on the symlink**: `_www` needs execute permission on every parent directory of the repo.
- **"Address already in use"**: `sudo lsof -nP -iTCP:80 -sTCP:LISTEN` (often a leftover Homebrew httpd).
- **ES "Unknown error:52"**: security is still enabled; set `xpack.security.enabled: false`.
