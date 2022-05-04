FROM php:8.0-cli-alpine3.13

WORKDIR /app

# PHP: Copy configuration files & remove dist files
RUN mkdir /phpIni
COPY files/bin /usr/local/bin/
COPY files/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY files/php/ext /phpIni

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo openssh libxml2-dev postgresql-dev oniguruma-dev autoconf g++ make npm freetype-dev libjpeg-turbo-dev libpng-dev libzip-dev

# COMPOSER: install binary and prestissimo
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net
RUN pecl install pcov swoole ssh2
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install mbstring xml iconv pcntl gd zip sockets pdo pdo_pgsql bcmath soap
RUN docker-php-ext-enable mbstring xml gd iconv zip swoole pcov pcntl sockets bcmath pdo pdo_pgsql soap

#
RUN #apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# fix work iconv library with alphine
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ --allow-untrusted gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
ENV NODE_PATH "/home/www-data/.npm-global/lib/node_modules"

RUN apk add --no-cache nodejs npm && \
    mkdir "/home/www-data/.npm-global/" && \
    npm config set prefix "/home/www-data/.npm-global/" && \
    npm install -g chokidar

ENV SWOOLE_MAX_REQUESTS "500"
ENV SWOOLE_TASK_WORKERS "auto"
ENV SWOOLE_WATCH $true
ENV SWOOLE_WORKERS "auto"

RUN chown -R www-data:www-data /app

#RUN chmod -R 777 /app/storage

COPY ./start.sh /
RUN chmod +x /start.sh
ENTRYPOINT ["/start.sh"]