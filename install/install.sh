#!/bin/bash

cd /var/www

wget -O ./composer-setup.php https://getcomposer.org/installer 
php ./composer-setup.php
mv composer.phar /usr/local/bin/composer
rm -f ./composer-setup.php

composer install
composer dump-autoload

mkdir /var/www/db
sqlite3 /var/www/db/main.db < /install/init_schema.sql

