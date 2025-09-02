#!/bin/bash

# ===============================================
# VÉRIFICATION FINALE AVANT DÉPLOIEMENT
# API INTRAFMC - Version Production
# ===============================================

echo "🔍 Vérification finale de la branche production..."

# Vérification de la branche
if [[ $(git branch --show-current) != "production" ]]; then
    echo "❌ Erreur: Vous n'êtes pas sur la branche production"
    exit 1
fi

echo "✅ Branche production active"

# Vérification des fichiers critiques
FILES=(
    ".env.example"
    "public/.htaccess"
    "composer.json"
    "DEPLOIEMENT_PRODUCTION_O2SWITCH.md"
    "API_REFERENCE_PRODUCTION.md"
    "README_PRODUCTION.md"
    "check-production.php"
)

echo "📁 Vérification des fichiers critiques..."
for file in "${FILES[@]}"; do
    if [[ -f "$file" ]]; then
        echo "✅ $file"
    else
        echo "❌ $file manquant"
        exit 1
    fi
done

# Vérification que les fichiers de test sont supprimés
TEST_FILES=(
    "test_all_products.graphql"
    "test_pagination_graphql.graphql"
    "validate_fix.php"
)

echo "🧹 Vérification du nettoyage..."
for file in "${TEST_FILES[@]}"; do
    if [[ -f "$file" ]]; then
        echo "❌ $file devrait être supprimé"
        exit 1
    else
        echo "✅ $file supprimé"
    fi
done

# Vérification des dossiers
DIRECTORIES=(
    "app/Modules"
    "docs"
    "public/product_images"
    "public/product_analysis"
    "storage/logs"
    "bootstrap/cache"
)

echo "📂 Vérification des dossiers..."
for dir in "${DIRECTORIES[@]}"; do
    if [[ -d "$dir" ]]; then
        echo "✅ $dir"
    else
        echo "❌ $dir manquant"
        exit 1
    fi
done

# Vérification de la configuration
echo "⚙️ Vérification de la configuration..."

if grep -q "APP_ENV=production" .env.example; then
    echo "✅ Configuration production dans .env.example"
else
    echo "❌ Configuration production manquante"
    exit 1
fi

if grep -q "APP_DEBUG=false" .env.example; then
    echo "✅ Debug désactivé"
else
    echo "❌ Debug non désactivé"
    exit 1
fi

# Vérification du composer.json
if grep -q '"laravel/framework"' composer.json; then
    echo "✅ Laravel présent"
else
    echo "❌ Laravel manquant"
    exit 1
fi

# Vérification des modules
MODULES=(
    "app/Modules/Auth"
    "app/Modules/Product_CBD"
    "app/Modules/Cart"
    "app/Modules/Order"
    "app/Modules/User"
    "app/Modules/Category"
    "app/Modules/FinancialRecap"
)

echo "🎯 Vérification des modules..."
for module in "${MODULES[@]}"; do
    if [[ -d "$module" ]]; then
        echo "✅ $module"
    else
        echo "❌ $module manquant"
        exit 1
    fi
done

# Vérification de la documentation
echo "📚 Vérification de la documentation..."
if [[ -f "docs/MODULE_ORDER.md" ]] && [[ -f "docs/MODULE_ARRIVAGES_ET_RECHERCHE.md" ]]; then
    echo "✅ Documentation modules complète"
else
    echo "❌ Documentation modules incomplète"
    exit 1
fi

# Compte final des fichiers
echo "📊 Statistiques finales:"
echo "- Modules GraphQL: $(find app/Modules -name "*.php" | wc -l) fichiers"
echo "- Tests: $(find tests -name "*.php" | wc -l) fichiers"
echo "- Documentation: $(find docs -name "*.md" | wc -l) fichiers"
echo "- Images produits: $(find public/product_images -type f 2>/dev/null | wc -l) fichiers"

echo ""
echo "🎉 VÉRIFICATION TERMINÉE AVEC SUCCÈS !"
echo "✅ La branche production est prête pour le déploiement"
echo ""
echo "📝 Prochaines étapes:"
echo "1. git push origin production"
echo "2. Télécharger l'archive depuis GitHub"
echo "3. Suivre le guide DEPLOIEMENT_PRODUCTION_O2SWITCH.md"
echo "4. Exécuter check-production.php après upload"
echo ""
echo "🚀 Bon déploiement !"
