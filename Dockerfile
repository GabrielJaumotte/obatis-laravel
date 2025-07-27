 FROM php:8.3-fpm

 RUN apt-get update \
  && apt-get install -y git unzip curl libzip-dev libpq-dev \
  && docker-php-ext-install pdo_mysql zip pdo_pgsql pgsql

 COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

 WORKDIR /var/www
 COPY . .

# S’assurer que bootstrap/cache existe et est writable pour Composer
RUN mkdir -p bootstrap/cache \
   && chown -R www-data:www-data bootstrap/cache

 RUN composer install --no-interaction --prefer-dist --optimize-autoloader

 RUN php artisan key:generate \
   && php artisan config:clear \
   && php artisan config:cache

 COPY entrypoint.sh /usr/local/bin/entrypoint.sh
 RUN chmod +x /usr/local/bin/entrypoint.sh

# USER www-data

 ENTRYPOINT ["entrypoint.sh"]
 CMD ["php-fpm"]
