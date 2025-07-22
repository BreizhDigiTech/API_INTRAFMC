# Script PowerShell pour exécuter les tests GraphQL
# Utilisation: .\run-tests.ps1 [options]

param(
    [string]$TestType = "all"
)

Write-Host "🧪 Lancement des tests GraphQL pour API Intra FMC" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan

# Configuration de l'environnement de test
$env:APP_ENV = "testing"

# Fonction pour afficher les messages
function Write-Info {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

# Vérifier que PHP est installé
try {
    php --version | Out-Null
} catch {
    Write-Error "PHP n'est pas installé ou non accessible dans le PATH"
    exit 1
}

# Vérifier que Composer est installé
try {
    composer --version | Out-Null
} catch {
    Write-Error "Composer n'est pas installé ou non accessible dans le PATH"
    exit 1
}

# Installation des dépendances si nécessaire
if (!(Test-Path "vendor")) {
    Write-Info "Installation des dépendances Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
}

# Copier le fichier de configuration de test si nécessaire
if (!(Test-Path ".env.testing")) {
    Write-Warning "Fichier .env.testing non trouvé, copie depuis .env.example"
    Copy-Item ".env.example" ".env.testing"
}

# Générer la clé d'application pour les tests
Write-Info "Génération de la clé d'application pour les tests..."
php artisan key:generate --env=testing --force

# Génération de la clé JWT pour les tests
Write-Info "Génération de la clé JWT pour les tests..."
php artisan jwt:secret --env=testing --force

# Mise en cache du schéma GraphQL
Write-Info "Mise en cache du schéma GraphQL..."
php artisan lighthouse:cache

# Exécuter les tests selon les paramètres
switch ($TestType.ToLower()) {
    "auth" {
        Write-Info "Exécution des tests d'authentification..."
        php artisan test tests/Feature/GraphQL/AuthTest.php --env=testing
    }
    "products" {
        Write-Info "Exécution des tests de produits CBD..."
        php artisan test tests/Feature/GraphQL/ProductCBDTest.php --env=testing
    }
    "categories" {
        Write-Info "Exécution des tests de catégories..."
        php artisan test tests/Feature/GraphQL/CategoryTest.php --env=testing
    }
    "cart" {
        Write-Info "Exécution des tests de panier..."
        php artisan test tests/Feature/GraphQL/CartTest.php --env=testing
    }
    "graphql" {
        Write-Info "Exécution de tous les tests GraphQL..."
        php artisan test tests/Feature/GraphQL/ --env=testing
    }
    "coverage" {
        Write-Info "Exécution des tests avec couverture de code..."
        php artisan test --coverage --env=testing
    }
    "unit" {
        Write-Info "Exécution des tests unitaires..."
        php artisan test tests/Unit/ --env=testing
    }
    "feature" {
        Write-Info "Exécution des tests de fonctionnalités..."
        php artisan test tests/Feature/ --env=testing
    }
    default {
        Write-Info "Exécution de tous les tests..."
        php artisan test --env=testing
    }
}

# Vérifier le code de sortie
if ($LASTEXITCODE -eq 0) {
    Write-Info "✅ Tous les tests sont passés avec succès!"
} else {
    Write-Error "❌ Certains tests ont échoué."
    exit 1
}

Write-Host ""
Write-Host "📊 Statistiques des tests disponibles:" -ForegroundColor Cyan
Write-Host "  - Tests d'authentification: tests/Feature/GraphQL/AuthTest.php"
Write-Host "  - Tests de produits CBD: tests/Feature/GraphQL/ProductCBDTest.php"
Write-Host "  - Tests de catégories: tests/Feature/GraphQL/CategoryTest.php"
Write-Host "  - Tests de panier: tests/Feature/GraphQL/CartTest.php"
Write-Host ""
Write-Host "💡 Commandes utiles:" -ForegroundColor Cyan
Write-Host "  .\run-tests.ps1 auth       # Tests d'authentification uniquement"
Write-Host "  .\run-tests.ps1 products   # Tests de produits uniquement"
Write-Host "  .\run-tests.ps1 categories # Tests de catégories uniquement"
Write-Host "  .\run-tests.ps1 cart       # Tests de panier uniquement"
Write-Host "  .\run-tests.ps1 graphql    # Tous les tests GraphQL"
Write-Host "  .\run-tests.ps1 coverage   # Tests avec couverture de code"
