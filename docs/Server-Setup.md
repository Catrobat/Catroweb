## Warning: Outdated!

1. **Connect** (via SSH) to the server.
     Make sure to replace 'username' and 'host' with the correct username and host!
```
ssh username@host
```

2. **Install**: 
  ```
  sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
  sudo apt update
  sudo apt upgrade
  sudo apt install php8.4-common php8.4-ldap php8.4-cli php8.4-curl php8.4-intl php-apcu php-imagick php-mbstring php8.4-gettext git curl nginx php8.4-fpm php8.4-mysql npm mariadb-server php8.4-gd php8.4-zip php8.4-xml php-bcmath
  sudo apt autoremove 
  sudo apt install -y unzip php-zip
  ```

3. Configure and **set up MariaDb** + DB. 
   Don't forget to create a different root password and optional a new user+password: 
 ```
  sudo mysql
  update mysql.user set plugin='' where user='root';
  update mysql.user set password=password('root') where user='root';

  CREATE USER 'catroweb' IDENTIFIED BY 'catroweb';
  GRANT ALL PRIVILEGES ON * . * TO 'catroweb';

  FLUSH PRIVILEGES;

  CREATE DATABASE catroweb;
  ```

4. To be able to use our **deploy script** the /etc/sudoers file needs to be updated. Add the following lines at the bottom of the file.
   Make sure to replace 'username' with the correct username!
```
username ALL = (ALL:ALL) ALL
username ALL = (www-data) NOPASSWD:/usr/bin/php*
username ALL = NOPASSWD:/bin/setfacl
username ALL = NOPASSWD:/usr/sbin/service nginx *
username ALL = NOPASSWD:/usr/sbin/service php*
```

5. Update the following values in the **/etc/php/X/fpm/php.ini** file. Make sure to replace X by the correct version number. (Eg. 8.1) Else the project upload will not work. Specify the values as you need them. The following are only example values!
```
memory_limit = 2G
post_max_size = 256M
upload_max_filesize = 256M
```

6. Create the sites-available in the nginx config:
```
sudo nano /etc/nginx/sites-available/catroweb
```
with
```
server {
    listen 80;
    listen [::]:80;
    client_max_body_size 100M;

    root /var/www/share/current/public/;

    server_name HERE_USE_actual_server_name;

    location / {
        # try to serve file directly, fallback to index.php
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS off;
        keepalive_timeout 90;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
        proxy_read_timeout 300;
        send_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;

        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/index.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    access_log /var/log/nginx/access.log combined;
    error_log /var/log/nginx/error.log warn;


    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    client_max_body_size 100M;

    ssl_certificate     /etc/ssl/certs/ssl-cert-snakeoil.pem;
    ssl_certificate_key /etc/ssl/private/ssl-cert-snakeoil.key;
    ssl_protocols       SSLv3 TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers         ECDHE-RSA-AES256-SHA384:AES256-SHA256:RC4:HIGH:!MD5:!aNULL:!EDH:!AESGCM;
    fastcgi_param HTTPS on;

    root /var/www/share/current/public/;

    server_name HERE_USE_actual_server_name;

    location / {
        # try to serve file directly, fallback to index.php
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_SCHEME https;
        keepalive_timeout 90;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
        proxy_read_timeout 300;
        send_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;

        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/index.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    access_log /var/log/nginx/access.log combined;
    error_log /var/log/nginx/error.log warn;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }
}
```
Make sure to update the php version number if needed!

7. Now we enable the site
```
sudo ln -s /etc/nginx/sites-available/catroweb /etc/nginx/sites-enabled 
sudo rm /etc/nginx/sites-enabled/default
```

8. Create our web folder and give the correct rights
```
sudo mkdir /var/www/share
sudo chmod -R 0777 /var/www/share
```

9. Deploy onto the server. For more details look into the "How to Deploy" section.

Make sure to use the correct db name, and define a secret! Credentials can only be found on Confluence.
 (https://confluence.catrob.at/display/MAN/%5BCredentials%5D+Catroweb+Servers+Parameters#space-menu-link-content)

    The same accounts for the local .env files. (.env.dev.local  .env.prod.local).
    (E.g. the mail system -> https://confluence.catrob.at/display/MAN/%5BCredentials%5D+No-Reply+Mail)

 Those files keep the same content between every deployment and will not be overwritten!

10. Now deploy again. It should work. Might need to restart the services on the server.



    
 
