FROM php:7.4.11-apache

ENV PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:/var/www/vendor/bin
RUN apt-get update && apt -y install sqlite3 wget unzip git

WORKDIR /var/www

RUN mkdir /var/www/db
COPY install /install
COPY www /var/www
RUN /bin/bash -x /install/install.sh

WORKDIR /var/www

