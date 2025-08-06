FROM php:8.3-fpm

# 1. Installation des dépendances système nécessaires
RUN apt-get update \
  && apt-get install -y git unzip curl libzip-dev libpq-dev libonig-dev libxml2-dev

RUN docker-php-ext-install pdo_mysql zip pdo_pgsql pgsql sockets pcntl

# 2. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Définition du répertoire de travail
WORKDIR /var/www

# 4. Copie complète du projet ENTIÈREMENT avant d’installer composer
COPY . .

# 5. Installation des dépendances Laravel (à faire après avoir copié tout le projet)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 6. Permissions pour les dossiers nécessaires à Laravel
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

# 7. Artisan : clé + cache + autoload
RUN php artisan key:generate --force \
  && php artisan config:cache \
  && composer dump-autoload --optimize

# 8. Script de démarrage (entrypoint)
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
