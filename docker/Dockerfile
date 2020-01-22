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
    wget \
    dpkg \
    libappindicator3-1 \
    libasound2 \
    libatk-bridge2.0-0 \
    libatspi2.0-0 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libx11-xcb1 \
    libxss1 \
    libxtst6 \
    xdg-utils \
    fonts-liberation \
    libgconf-2-4 \
    grunt && \
    gem install sass

# CHROME
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb && \
    dpkg -i google-chrome-stable_current_amd64.deb


# APACHE CONFIG
COPY /docker/apache/catroweb.conf /etc/apache2/sites-available/catroweb.conf
RUN a2dissite 000-default.conf && \
    a2ensite catroweb.conf
WORKDIR /var/www/catroweb


# CONFIGURATIONS
COPY /config config
COPY /docker/config/parameters.yml.dist config/packages/parameters.yml
RUN cp config/packages/test/parameters.yml.dist config/packages/test/parameters.yml && \
    cp config/packages/dev/parameters.yml.dist config/packages/dev/parameters.yml && \
    echo 'APP_ENV=dev' > .env.local


# LIBRARIES
ADD /package.json /package-lock.json /composer.json /composer.lock ./
RUN npm install && npm install grunt
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install --no-scripts


# GRUNT
COPY /assets assets
COPY /public public
COPY /Gruntfile.js Gruntfile.js
RUN grunt

# ADD CODE
ADD / ./

# PERMISSIONS
RUN composer run-script post-install-cmd && \
    rm -rf var/cache/* && \
    rm -rf var/log/* && \
    adduser root www-data && \
    chown -R www-data:www-data var/cache var/log && \
    chmod o+w public/resources/ -R && \
    chmod o+w+x tests/behat/sqlite/ -R && \
    chmod +x docker/php/startup.sh && \
    chmod +x docker/php/wait-for-it.sh

EXPOSE 80

# CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]