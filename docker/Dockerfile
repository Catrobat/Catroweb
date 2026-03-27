# syntax=docker/dockerfile:1.7

# Use composer image for better caching
FROM composer:2.9.5 AS composer-build
WORKDIR /app
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-scripts --prefer-dist --no-progress --no-interaction

# Use node image for better caching
FROM node:lts AS node-build
WORKDIR /app
COPY package.json yarn.lock .yarnrc.yml ./
RUN corepack enable && corepack prepare yarn@4.12.0 --activate
RUN --mount=type=cache,target=/root/.yarn/berry/cache \
    yarn install --immutable

# Build frontend assets in the node stage (runs in parallel with runtime apt-get)
COPY webpack.config.js postcss.config.js eslint.config.js .prettierrc.json ./
COPY .env .env.dev .env.test .env.prod ./
COPY assets/ assets/
COPY templates/ templates/
RUN yarn run dev

# Run on slim variant for smaller image size (~200MB savings)
FROM debian:bookworm-slim
ARG DEBIAN_FRONTEND=noninteractive

# Build argument to control xdebug installation (default: true for dev)
ARG INSTALL_XDEBUG=true

# Install dependencies
RUN apt-get update && \
    apt-get install -yq --no-install-recommends wget apt-transport-https lsb-release ca-certificates && \
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ bookworm main" >> /etc/apt/sources.list.d/php.list && \
    apt-get update && \
    apt-get install -yq --no-install-recommends \
    php8.5-cli \
    php8.5-common \
    php8.5-curl \
    php8.5-dom \
    php8.5-gd \
    php8.5-imagick \
    php8.5-intl \
    php8.5-mbstring \
    php8.5-mysql \
    php8.5-xml \
    php8.5-zip \
    php8.5-bcmath \
    libapache2-mod-php8.5 \
    acl \
    make \
    libtool \
    apache2 \
    git \
    curl && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -yq --no-install-recommends nodejs && \
    corepack enable && \
    corepack prepare yarn@4.12.0 --activate && \
    rm -rf /var/lib/apt/lists/*

# Conditionally install xdebug (skip in production for better performance)
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
    apt-get update && \
    apt-get install -yq --no-install-recommends php8.5-xdebug && \
    rm -rf /var/lib/apt/lists/*; \
    fi

# Overwrite default apache config
RUN a2enmod rewrite deflate headers
COPY /docker/apache/catroweb.conf /etc/apache2/sites-available/catroweb.conf
RUN a2dissite 000-default.conf && \
    a2ensite catroweb.conf

# Overwrite php config (only if xdebug is installed)
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
    printf "\n%s\n" xdebug.mode=coverage >> /etc/php/8.5/apache2/php.ini; \
    printf "\n%s\n" xdebug.mode=coverage >> /etc/php/8.5/cli/php.ini; \
    fi

# Setting working directory
WORKDIR /var/www/catroweb

# Add libraries and modules from build stages
COPY --from=composer-build /app/vendor vendor
COPY --from=node-build /app/node_modules node_modules

# Generate jwt config in container (will be overwritten with host keys if they exist)
COPY /docker/app/init-jwt-config.sh ./docker/app/init-jwt-config.sh
RUN sh docker/app/init-jwt-config.sh

# Copy configuration files first for better layer caching.
# These change less frequently than source code.
COPY .env .env.dev .env.test .env.prod ./
COPY composer.json composer.lock symfony.lock ./
COPY package.json yarn.lock .yarnrc.yml webpack.config.js ./
COPY behat.yaml.dist phpunit.xml.dist ./
COPY config/ config/
COPY translations/ translations/
COPY docker/ docker/

# Copy source code, templates, and other project files.
# A change to these files clears the cache of all layers defined after this command.
COPY assets/ assets/
COPY bin/ bin/
COPY migrations/ migrations/
COPY public/ public/
COPY src/ src/
COPY templates/ templates/
COPY tests/ tests/

# Copy pre-built frontend assets from node stage (replaces `yarn run dev` in runtime)
COPY --from=node-build /app/public/build/ public/build/

# Overwrite behat config:
RUN cp behat.yaml.dist behat.yaml && \
    sed -i -r "s|(base_url:)(\s+.+)|base_url: http://app.catroweb/index_test.php/|g" behat.yaml && \
    sed -i -r "s|(api_url:)(\s+.+)|api_url: http://chrome.catroweb:9222|g" behat.yaml

# Overwrite project config:
ARG APP_ENVIRONMENT
RUN echo "\n" >> .env.local && \
    echo APP_ENV=$APP_ENVIRONMENT >> .env.local && \
    echo "\n" >> .env.dev.local && \
    printf "%s\n" DATABASE_URL=pdo-mysql://root:root@db.catroweb.dev:3306/catroweb_dev \
     ELASTICSEARCH_URL=http://elasticsearch:9200/ \
     ES_HOST=elasticsearch \
     ES_PORT=9200 >> .env.dev.local && \
    echo "\n" >> .env.test.local && \
    printf "%s\n" DATABASE_URL=pdo-mysql://root:root@db.catroweb.test:3306/catroweb_test \
     ELASTICSEARCH_URL=http://elasticsearch:9200/ \
     ES_HOST=elasticsearch \
     ES_PORT=9200 >> .env.test.local

# Add composer executable to the container
COPY --from=composer-build /usr/bin/composer /usr/bin/composer

# Add library scripts symlinks
COPY --from=composer-build /app/bin bin

# Finally we set all permissions, and create required keys and test fixtures.
RUN sh docker/app/prepare-test-env.sh

EXPOSE 80
