#!/bin/bash

# ===============================================
# SCRIPT D'OPTIMISATION PRODUCTION
# API INTRAFMC - O2SWITCH
# ===============================================

echo "🚀 Optimisation pour la production..."

# 1. Installation des dépendances de production uniquement
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

# 2. Génération de la clé d'application
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# 3. Cache de configuration
echo "⚙️ Cache de configuration..."
php artisan config:cache

# 4. Cache des routes
echo "🛣️ Cache des routes..."
php artisan route:cache

# 5. Cache des vues
echo "👁️ Cache des vues..."
php artisan view:cache

# 6. Cache Lighthouse/GraphQL
echo "🎯 Cache GraphQL..."
php artisan lighthouse:cache

# 7. Optimisation de l'autoloader
echo "🔧 Optimisation autoloader..."
composer dump-autoload --optimize

# 8. Migration de la base de données
echo "🗄️ Migration de la base de données..."
php artisan migrate --force

# 9. Seeding des données initiales (optionnel)
# php artisan db:seed --class=ProductionSeeder

# 10. Permissions des fichiers
echo "🔒 Configuration des permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/product_images public/product_analysis

echo "✅ Optimisation terminée !"
echo "🌐 Application prête pour la production"
