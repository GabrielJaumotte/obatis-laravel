## Suivi des opérations IA (août 2025)

Cette liste documente les modifications apportées au dépôt `obatis-laravel` via l’agent IA sur la branche `staging`.

- Suppression de l’ancien dossier `docker/` et de ses fichiers obsolètes (Dockerfile Swoole, docker-compose.yml, configuration Nginx et supervisord pour Swoole).
- Suppression des scripts PowerShell de déploiement `deploy-auto-staging.ps1` et `deploy-obatis.ps1`.
- Suppression de `Dockerfile.save` (ancien exemple).
- Suppression du script en double `init.obatis.sh` (on conserve `init-obatis.sh`).
- Suppression du répertoire de vues compilées `obatis-api/storage/framework/views`.
- Ajout du fichier `.dockerignore` pour ignorer les fichiers et dossiers non nécessaires au build Docker (git, node_modules, vendor, rr, stockages temporaires, etc.).
- Mise à jour de `docker-compose.yml` :
  - Ajout de `container_name: app` pour le service `app`.
  - Correction du chemin de configuration Nginx (`./nginx:/etc/nginx/conf.d:ro`).
- Mise à jour du `Dockerfile` pour éviter l’écrasement du dossier `vendor` et exécuter `composer install` après la copie du code.
- Mise à jour de la configuration Nginx (`nginx/default.conf`) pour utiliser Octane/RoadRunner sur le port 8001 et servir correctement les assets statiques.
- Mise à jour du fichier `README.md` pour décrire le projet Obatis, les prérequis, les instructions de lancement et la structure du dépôt.
- Création du présent fichier de suivi `suivi_ia.md` pour consigner les actions effectuées.

Chacun de ces changements a été commis séparément sur la branche `staging` afin de faciliter l’audit et le retour en arrière si nécessaire.
