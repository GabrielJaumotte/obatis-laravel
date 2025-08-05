FROM php:8.3-fpm

# Installation des dépendances système nécessaires
RUN apt-get update \
  && apt-get install -y git unzip curl libzip-dev libpq-dev libonig-dev libxml2-dev

RUN docker-php-ext-install pdo_mysql zip pdo_pgsql pgsql sockets pcntl

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers nécessaires uniquement pour Composer
COPY composer.json composer.lock ./

# Installer les dépendances SANS lancer les scripts Laravel (artisan)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copie tout le reste des fichiers
COPY . .

# S’assurer que storage et bootstrap/cache existent et sont writable
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

# Génération clé Laravel et cache de config
RUN php artisan key:generate --force \
  && php artisan config:cache \
  && composer dump-autoload --optimize

# Copie et préparation du script d'entrée (entrypoint)
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
