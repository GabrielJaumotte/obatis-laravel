#!/usr/bin/env bash
# Script d'initialisation Obatis

set -euo pipefail

# Valeurs configurables via variables d'environnement
APP_PORT=${APP_PORT:-8001}
NGINX_PORT=${NGINX_PORT:-8010}

# Nom du projet Compose pour éviter les conteneurs orphelins (compatible docker-compose v1)
export COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME:-obatis}

echo "[init-obatis] utilisation des ports app=${APP_PORT}, nginx=${NGINX_PORT}"

# Génération du .env si absent
if [ ! -f .env ]; then
  cp .env.example .env
  echo "[init-obatis] .env généré depuis .env.example"
fi

# Arrêt et suppression des conteneurs/volumes existants
docker compose down -v --remove-orphans

# Construction des images (sans cache pour refléter les changements)
docker compose build --no-cache

# Démarrage des services en arrière-plan
docker compose up -d

# Correction des permissions pour Laravel (stockage et cache)
docker compose exec --user root app \
  chown -R www-data:www-data storage bootstrap/cache

# Exécution des migrations en mode non interactif
docker compose exec app php artisan migrate --force

echo "[init-obatis] initialisation terminée. L'application est accessible via Nginx sur http://localhost:${NGINX_PORT}"
