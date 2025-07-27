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

# Migrations Laravel
docker compose exec app php artisan migrate --force

echo "Initialisation terminée. Laravel est prêt sur http://localhost:8000"
