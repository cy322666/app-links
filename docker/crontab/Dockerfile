FROM php:8.0-cli-alpine3.13

#WORKDIR /app

#RUN docker-php-ext-install pdo pdo_mysql

#COPY crontab.sh /etc/crontabs/root

RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo openssh libxml2-dev postgresql-dev oniguruma-dev autoconf g++ make npm freetype-dev libjpeg-turbo-dev libpng-dev libzip-dev

RUN pecl channel-update pecl.php.net
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install mbstring xml iconv pcntl gd zip sockets pdo pdo_pgsql bcmath soap
RUN docker-php-ext-enable mbstring xml gd iconv zip pcntl sockets bcmath pdo pdo_pgsql soap

RUN #apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

COPY crontab.sh /

RUN ["chmod", "+x", "/crontab.sh"]

ENTRYPOINT ["/crontab.sh"]
