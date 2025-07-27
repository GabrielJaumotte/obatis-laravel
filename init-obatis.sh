#!/bin/bash
# Script d'initialisation Obatis

set -e

# Génération du .env si absent
if [ ! -f .env ]; then
  cp .env.example .env
  echo ".env généré depuis .env.example"
fi

# Reset des conteneurs et volumes
docker compose down -v --remove-orphans

# Build et démarrage
docker compose build --no-cache
docker compose up -d


# S’assurer que www-data possède les répertoires
docker compose exec --user root app \
   chown -R www-data:www-data storage bootstrap/cache

# Migrations Laravel
docker compose exec app php artisan migrate --force

echo "Initialisation terminée. Laravel est prêt sur http://localhost:8000"
