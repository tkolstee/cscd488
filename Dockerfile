FROM tkolstee/cscd488-docker:latest

COPY . /app
WORKDIR /app
USER root
RUN chown -R laravel:laravel .
USER laravel
RUN ./install.sh
CMD php artisan serve --host=0.0.0.0
