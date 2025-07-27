# DEPLOY IA - création auto de branche feature/* + push
$projectPath = "D:\Obatis-MEGA\code\obatis-api\obatis-laravel"
Set-Location $projectPath

# Vérifie que c’est un dépôt Git
if (!(Test-Path ".git")) {
    Write-Host " Ce dossier n’est pas un dépôt Git valide."
    exit
}

# Nom de branche auto horodaté
$date = Get-Date -Format "yyyyMMdd-HHmmss"
$branchName = "feature/ia-$date"

# Saisir un message de commit
$msg = Read-Host " Message du commit"

# Checkout + commit + push
git checkout -b $branchName
git add .
git commit -m "$msg"
git push origin $branchName

Write-Host "`n Code poussé dans la branche $branchName"
Write-Host " Tu peux créer une Pull Request vers staging quand tu es prêt."
