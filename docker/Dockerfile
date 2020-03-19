FROM composer AS composer-build
COPY composer.json composer.lock ./
RUN composer install --no-scripts


FROM node AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install && npm install grunt


FROM ubuntu:18.04
ARG DEBIAN_FRONTEND=noninteractive

# DEPDEDENCIES
RUN apt-get update && \
    apt-get install -yq --no-install-recommends software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get install -yq --no-install-recommends \
    php7.4-common \
    php7.4-ldap \
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
    npm \
    ruby \
    ruby-dev \
    libgconf-2-4 \
    grunt && \
    gem install sass

# APACHE CONFIG
COPY /docker/apache/catroweb.conf /etc/apache2/sites-available/catroweb.conf
RUN a2dissite 000-default.conf && \
    a2ensite catroweb.conf

WORKDIR /var/www/catroweb

# LIBRARIES
COPY --from=composer-build /app/vendor vendor
COPY --from=node-build /app/node_modules node_modules
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock package.json package-lock.json ./

# ADD CODE
ADD / ./

# CONFIGURATIONS
ARG APP_ENVIRONMENT
RUN sed -i -r "s|(base_url:)(\s+.+)|base_url: http://app.catroweb.$APP_ENVIRONMENT/index_test.php/|g" behat.yml.dist && \
    sed -i -r "s|(api_url:)(\s+.+)|api_url: chrome.catroweb.$APP_ENVIRONMENT:9222|g" behat.yml.dist && \
    cp config/packages/parameters.yml.dist config/packages/parameters.yml && \
    echo "\n" >> .env.local && \
    echo APP_ENV=$APP_ENVIRONMENT >> .env.local && \
    echo "\n" >> .env.$APP_ENVIRONMENT.local && \
    echo DATABASE_URL=pdo-mysql://root:root@db.catroweb.$APP_ENVIRONMENT:3306/catroweb_$APP_ENVIRONMENT >> .env.$APP_ENVIRONMENT.local

# PERMISSIONS
RUN sh docker/app/prepare-test-env.sh

EXPOSE 80
