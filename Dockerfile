FROM php:7.4-fpm

RUN apt-get update && apt-get install -y npm git curl libpng-dev libonig-dev libxml2-dev zip unzip sqlite3 && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN npm install npm@latest -g

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /app

WORKDIR /app
RUN useradd -G www-data,root -u 1000 -d /home/laravel laravel && mkdir -p /home/laravel/.composer
RUN chown -R laravel:laravel /home/laravel /app

USER laravel
RUN ./install.sh
CMD php artisan serve --host=0.0.0.0


