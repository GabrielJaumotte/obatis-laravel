@echo off
echo ============================
echo 🚀 Lancement Obatis API
echo ============================

:: Aller dans le dossier du projet
cd /d C:\Users\Utilisateur\obatis-api

:: Activer l'environnement virtuel (si tu as un venv)
call .\venv\Scripts\activate

:: Lancer uvicorn
uvicorn main:app --reload

pause
