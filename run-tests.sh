#!/bin/bash

# Script pour exécuter les tests GraphQL
# Utilisation: ./run-tests.sh [options]

echo "🧪 Lancement des tests GraphQL pour API Intra FMC"
echo "================================================"

# Configuration de l'environnement de test
export APP_ENV=testing

# Couleurs pour les messages
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifier que PHP est installé
if ! command -v php &> /dev/null; then
    print_error "PHP n'est pas installé ou non accessible dans le PATH"
    exit 1
fi

# Vérifier que Composer est installé
if ! command -v composer &> /dev/null; then
    print_error "Composer n'est pas installé ou non accessible dans le PATH"
    exit 1
fi

# Installation des dépendances si nécessaire
if [ ! -d "vendor" ]; then
    print_message "Installation des dépendances Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Copier le fichier de configuration de test si nécessaire
if [ ! -f ".env.testing" ]; then
    print_warning "Fichier .env.testing non trouvé, copie depuis .env.example"
    cp .env.example .env.testing
fi

# Générer la clé d'application pour les tests
print_message "Génération de la clé d'application pour les tests..."
php artisan key:generate --env=testing --force

# Exécuter les tests selon les paramètres
case "${1:-all}" in
    "auth")
        print_message "Exécution des tests d'authentification..."
        php artisan test tests/Feature/GraphQL/AuthTest.php --env=testing
        ;;
    "products")
        print_message "Exécution des tests de produits CBD..."
        php artisan test tests/Feature/GraphQL/ProductCBDTest.php --env=testing
        ;;
    "categories")
        print_message "Exécution des tests de catégories..."
        php artisan test tests/Feature/GraphQL/CategoryTest.php --env=testing
        ;;
    "cart")
        print_message "Exécution des tests de panier..."
        php artisan test tests/Feature/GraphQL/CartTest.php --env=testing
        ;;
    "graphql")
        print_message "Exécution de tous les tests GraphQL..."
        php artisan test tests/Feature/GraphQL/ --env=testing
        ;;
    "coverage")
        print_message "Exécution des tests avec couverture de code..."
        php artisan test --coverage --env=testing
        ;;
    "unit")
        print_message "Exécution des tests unitaires..."
        php artisan test tests/Unit/ --env=testing
        ;;
    "feature")
        print_message "Exécution des tests de fonctionnalités..."
        php artisan test tests/Feature/ --env=testing
        ;;
    "all"|*)
        print_message "Exécution de tous les tests..."
        php artisan test --env=testing
        ;;
esac

# Vérifier le code de sortie
if [ $? -eq 0 ]; then
    print_message "✅ Tous les tests sont passés avec succès!"
else
    print_error "❌ Certains tests ont échoué."
    exit 1
fi

echo ""
echo "📊 Statistiques des tests disponibles:"
echo "  - Tests d'authentification: tests/Feature/GraphQL/AuthTest.php"
echo "  - Tests de produits CBD: tests/Feature/GraphQL/ProductCBDTest.php"
echo "  - Tests de catégories: tests/Feature/GraphQL/CategoryTest.php"
echo "  - Tests de panier: tests/Feature/GraphQL/CartTest.php"
echo ""
echo "💡 Commandes utiles:"
echo "  ./run-tests.sh auth       # Tests d'authentification uniquement"
echo "  ./run-tests.sh products   # Tests de produits uniquement"
echo "  ./run-tests.sh categories # Tests de catégories uniquement"
echo "  ./run-tests.sh cart       # Tests de panier uniquement"
echo "  ./run-tests.sh graphql    # Tous les tests GraphQL"
echo "  ./run-tests.sh coverage   # Tests avec couverture de code"
