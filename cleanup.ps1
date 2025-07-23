# Script PowerShell de nettoyage automatique pour API Intra FMC
# Utilisation: .\cleanup.ps1

Write-Host "🧹 Nettoyage du projet API Intra FMC" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan

function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

Write-Info "Nettoyage des fichiers de cache..."

# Nettoyage du cache Laravel
try {
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
} catch {
    Write-Warning "Impossible d'exécuter certaines commandes Artisan"
}

# Suppression des fichiers de cache
$filesToRemove = @(
    ".phpunit.result.cache",
    "storage\logs\*.log",
    "storage\framework\cache\data\*",
    "storage\framework\sessions\*",
    "storage\framework\views\*"
)

foreach ($pattern in $filesToRemove) {
    try {
        Remove-Item $pattern -Force -Recurse -ErrorAction SilentlyContinue
    } catch {
        # Ignore errors silently
    }
}

Write-Info "Nettoyage des dépendances..."

# Nettoyage de Composer
try {
    composer dump-autoload
} catch {
    Write-Warning "Composer non disponible"
}

Write-Info "Régénération des fichiers de configuration..."

# Régénération du cache de configuration (optionnel en développement)
if ($env:APP_ENV -eq "production") {
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

Write-Host "✅ Nettoyage terminé !" -ForegroundColor Green
Write-Host "📋 Résumé:" -ForegroundColor Cyan
Write-Host "   - Cache Laravel vidé" -ForegroundColor White
Write-Host "   - Fichiers temporaires supprimés" -ForegroundColor White
Write-Host "   - Configuration rechargée" -ForegroundColor White
