#!/bin/bash

cp ./env.dev  ./.env
composer install
npm install
npm run dev
touch database/database.sqlite
touch database/database_minigame.sqlite
php artisan migrate:fresh --seed

