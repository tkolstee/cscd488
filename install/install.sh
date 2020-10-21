#!/bin/bash

mkdir /var/www/db
sqlite3 /var/www/db/main.db < /install/init_schema.sql
chown -R www-data /var/www/db