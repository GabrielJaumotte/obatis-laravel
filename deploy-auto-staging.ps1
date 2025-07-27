# DEPLOY AUTO IA → staging

# Aller dans le dossier du projet
$projectPath = "D:\Projets\obatis-laravel"
Set-Location $projectPath

# Vérifier que c'est bien un repo git
if (!(Test-Path ".git")) {
    Write-Host " Ce dossier n'est pas un dépôt Git."
    exit
}

# Récupérer la dernière version de staging
git fetch origin
git checkout staging
git pull origin staging

# Ajouter tous les fichiers
git add .

# Commit automatique horodaté
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
git commit -m " Auto-push vers staging – $timestamp"

# Push vers GitHub
git push origin staging

Write-Host "`n Code IA poussé automatiquement sur la branche staging"
