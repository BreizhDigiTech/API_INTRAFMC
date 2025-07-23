#!/bin/bash

# Script de nettoyage automatique pour API Intra FMC
# Utilisation: ./cleanup.sh

echo "🧹 Nettoyage du projet API Intra FMC"
echo "===================================="

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}[INFO]${NC} Nettoyage des fichiers de cache..."

# Nettoyage du cache Laravel
php artisan cache:clear 2>/dev/null || echo -e "${YELLOW}[WARNING]${NC} Impossible de vider le cache Laravel"
php artisan config:clear 2>/dev/null || echo -e "${YELLOW}[WARNING]${NC} Impossible de vider la config"
php artisan route:clear 2>/dev/null || echo -e "${YELLOW}[WARNING]${NC} Impossible de vider les routes"
php artisan view:clear 2>/dev/null || echo -e "${YELLOW}[WARNING]${NC} Impossible de vider les vues"

# Suppression des fichiers de cache
rm -f .phpunit.result.cache
rm -f storage/logs/*.log
rm -f storage/framework/cache/data/*
rm -f storage/framework/sessions/*
rm -f storage/framework/views/*

echo -e "${GREEN}[INFO]${NC} Nettoyage des dépendances..."

# Nettoyage de Composer
composer dump-autoload 2>/dev/null || echo -e "${YELLOW}[WARNING]${NC} Composer non disponible"

echo -e "${GREEN}[INFO]${NC} Régénération des fichiers de configuration..."

# Régénération du cache de configuration (optionnel en développement)
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo -e "${GREEN}[SUCCESS]${NC} Nettoyage terminé !"
echo "📋 Résumé:"
echo "   - Cache Laravel vidé"
echo "   - Fichiers temporaires supprimés"
echo "   - Configuration rechargée"
