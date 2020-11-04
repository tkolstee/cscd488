#!/bin/bash

cp ./env.dev  ./.env
composer install
npm install
npm run dev
touch database/database.sqlite
php artisan migrate:fresh

