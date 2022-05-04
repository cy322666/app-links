FROM ubuntu:20.04

RUN apt-get update && apt-get install supervisor -y

RUN apt install lsb-release ca-certificates apt-transport-https software-properties-common -y
RUN add-apt-repository ppa:ondrej/php

RUN apt install php8.0 -y
RUN apt install php8.0-pgsql -y \
    php8.0-gd \
    php8.0-fpm \
    php8.0-cli \
    php8.0-common \
    php8.0-imap \
    php8.0-redis \
    php8.0-snmp \
    php8.0-xml \
    php8.0-zip \
    php8.0-mbstring

#RUN systemctl status php8.0-fpm start

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY worker.conf /etc/supervisor/conf.d/worker.conf

EXPOSE 8008

CMD ["/usr/bin/supervisord"]