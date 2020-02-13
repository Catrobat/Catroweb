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
    php7.3-common \
    php7.3-ldap \
    php7.3-cli \
    php7.3-curl \
    php7.3-sqlite3 \
    php7.3-intl \
    php7.3-gd \
    php7.3-zip \
    php7.3-mysql \
    php7.3-xml \
    php7.3-dom \
    libapache2-mod-php7.3 \
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

# CONFIGURATIONS
ARG APP_ENVIRONMENT
COPY /config config
COPY /docker/config/parameters.yml.dist config/packages/parameters.yml
RUN cp config/packages/test/parameters.yml.dist config/packages/test/parameters.yml && \
    cp config/packages/dev/parameters.yml.dist config/packages/dev/parameters.yml && \
    echo 'APP_ENV=$APP_ENVIRONMENT' > .env.local && \
    echo 'SECURE_SCHEME=http' > .env.local && \
    echo 'SECURE_SCHEME=http' > .env.test.local && \
    echo 'SECURE_SCHEME=http' > .env.dev.local

# ADD CODE
ADD / ./
COPY /docker/config/behat.yml.dist behat.yml.dist
RUN sed -i "s|APP_ENVIRONMENT|$APP_ENVIRONMENT|g" behat.yml.dist

# PERMISSIONS
RUN composer run-script post-install-cmd && \
    sh docker/app/test-permissions.sh

EXPOSE 80
