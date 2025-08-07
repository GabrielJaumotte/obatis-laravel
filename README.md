# Obatis Laravel Application

![CI Status](https://github.com/GabrielJaumotte/obatis-laravel/actions/workflows/ci.yml/badge.svg)

Ce dépôt contient le code source de l’application Obatis basée sur [Laravel 12](https://laravel.com), propulsée par [Octane](https://laravel.com/docs/12.x/octane) et [RoadRunner](https://roadrunner.dev/). L’objectif est de fournir une API moderne pour Obatis en utilisant PostgreSQL comme base de données et Docker pour l’environnement de développement et de déploiement.

## Prérequis

- PHP 8.3
- Composer
- Docker (et Docker Compose)
- Node.js et npm (pour la compilation des assets via Vite)

## Lancement avec Docker

Pour lancer l’application localement avec Docker :

```bash
# Construire et démarrer les services
docker compose up -d

# Exécuter les migrations et les seeders dans le conteneur app
docker compose exec app php artisan migrate --seed
```

Nginx sert l’application sur `http://localhost:8010`. Octane écoute sur le port `8001` dans le conteneur.

## Structure du projet

- `app/` — Code de l’application Laravel (contrôleurs, modèles, etc.)
- `nginx/` — Configuration Nginx pour servir l’application
- `Dockerfile` — Construction de l’image PHP-FPM avec l’installation des dépendances et Octane
- `docker-compose.yml` — Définit les services `app`, `nginx` et `db` pour l’environnement local
- `.dockerignore` — Liste des fichiers et répertoires ignorés lors du build Docker
- `entrypoint.sh` — Script d’entrée qui prépare l’environnement et lance Octane

## Notes et bonnes pratiques

- Les vues compilées et autres fichiers générés (`storage/framework/views` ...) sont exclus du contrôle de version.
- Les variables d’environnement doivent être définies dans un fichier `.env` (non versionné). Un exemple est fourni dans `.env.example`.
  Après avoir copié `.env.example` vers `.env`, exécutez `php artisan key:generate` pour générer la clé de chiffrement de l’application.
- La dépendance RoadRunner est installée via Composer.

## Intégration continue

Le dépôt inclut un pipeline GitHub Actions (`.github/workflows/ci.yml`) qui construit l’application, exécute les tests, compile l’image Docker et permet d’automatiser le déploiement.
