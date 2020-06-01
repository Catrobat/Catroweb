# Use composer image for better caching
FROM composer AS composer-build
COPY composer.json composer.lock ./
RUN composer install --no-scripts --prefer-dist

# Use node image for better caching
FROM node AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install && npm install grunt

# Run on:
FROM ubuntu:20.04
ARG DEBIAN_FRONTEND=noninteractive

# Install dependencies
RUN apt-get update && \
    apt-get install -yq --no-install-recommends software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get install -yq --no-install-recommends \
    php7.4-common \
    php7.4-cli \
    php7.4-curl \
    php7.4-intl \
    php7.4-gd \
    php7.4-zip \
    php7.4-mysql \
    php7.4-xml \
    php7.4-dom \
    libapache2-mod-php7.4 \
    zlibc \
    acl \
    make \
    libtool \
    php-apcu \
    apache2 \
    php-imagick \
    php-mbstring \
    php-gettext \
    git \
    curl \
    php-xdebug \
    libgconf-2-4 \
    grunt \
    npm && \
    npm install -g n && \
    n stable && \
    npm install -g sass

# Overwrite default apache config
COPY /docker/apache/catroweb.conf /etc/apache2/sites-available/catroweb.conf
RUN a2dissite 000-default.conf && \
    a2ensite catroweb.conf

# Setting working directory
WORKDIR /var/www/catroweb

# Generate jwt config in container (will be overwritten with host keys if they exist)
COPY /docker/app/init-jwt-config.sh ./docker/app/init-jwt-config.sh
RUN sh docker/app/init-jwt-config.sh

# Copy all files to the container.
# A change to any project file clears the cache of all layers defined after this command!
# Make sure to define all project code indepandant commands above to improve performance.
COPY / ./

# Overwrite behat config:
COPY behat.yml.dist ./behat.yml
RUN sed -i -r "s|(base_url:)(\s+.+)|base_url: http://app.catroweb/index_test.php/|g" behat.yml && \
    sed -i -r "s|(api_url:)(\s+.+)|api_url: chrome.catroweb:9222|g" behat.yml

# Overwrite project config:
ARG APP_ENVIRONMENT
RUN echo "\n" >> .env.local && \
    echo APP_ENV=$APP_ENVIRONMENT >> .env.local && \
    echo "\n" >> .env.dev.local && \
    echo DATABASE_URL=pdo-mysql://root:root@db.catroweb.dev:3306/catroweb_dev >> .env.dev.local && \
    echo "\n" >> .env.test.local && \
    echo DATABASE_URL=pdo-mysql://root:root@db.catroweb.test:3306/catroweb_test >> .env.test.local

# Add composer executable to the container
COPY --from=composer-build /usr/bin/composer /usr/bin/composer

# Add libraries and modules
COPY --from=composer-build /app/vendor vendor
COPY --from=node-build /app/node_modules node_modules

# Add library scripts symlinks
COPY --from=composer-build /app/bin bin

# Finally we set all permissions, and create required keys and test fixtures.
RUN sh docker/app/prepare-test-env.sh

# (Re-)compile public css/js files
RUN grunt

EXPOSE 80
