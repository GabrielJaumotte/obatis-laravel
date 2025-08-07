##
## Dockerfile multistage pour Laravel Octane avec RoadRunner
##
# Étape de build : installation des dépendances et compilation
FROM php:8.3-cli AS build

# Dépendances système nécessaires (versions minimales)
RUN apt-get update \ 
    && apt-get install -y --no-install-recommends git unzip curl libzip-dev libpq-dev libonig-dev libxml2-dev \ 
    && docker-php-ext-install pdo_mysql zip pdo_pgsql pgsql sockets pcntl

# Installation de Composer à partir de l’image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier les manifestes afin de mettre en cache l’installation des vendors
COPY composer.json composer.lock ./

# Installation des dépendances PHP (sans dev) et des autoload optimisés
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Copier le code restant
COPY . .

# Télécharger le binaire RoadRunner (ne pas le committer)
RUN php vendor/bin/rr get

# Génération de l’autoload optimisé
RUN composer dump-autoload --optimize


# Étape finale : image d’exécution minimaliste
FROM php:8.3-cli

# Installer uniquement les extensions nécessaires
RUN apt-get update \ 
    && apt-get install -y --no-install-recommends libzip-dev libpq-dev libonig-dev \ 
    && docker-php-ext-install pdo_pgsql zip sockets pcntl \ 
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copier l’application compilée depuis l’étape de build
COPY --from=build /app /var/www

## Entrypoint
# Copier le script d'entrypoint qui prépare les droits des dossiers puis exécute la commande
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Exposer le port utilisé par Octane
EXPOSE 8001

# Entrypoint: prépare l'environnement et exécute la commande passée
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Commande par défaut : démarrage d’Octane avec RoadRunner
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8001", "--max-requests=500"]
