# 🚀 API INTRAFMC - Version Production

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![GraphQL](https://img.shields.io/badge/GraphQL-Lighthouse-pink.svg)](https://lighthouse-php.com)

## 📋 Vue d'ensemble

API GraphQL complète pour la gestion d'un e-commerce CBD, développée avec Laravel 11 et Lighthouse GraphQL.

### ✨ Fonctionnalités principales

- 🔐 **Authentification JWT** avec gestion des rôles
- 📦 **Gestion des produits CBD** avec images et catégories
- 🛒 **Système de panier** et commandes
- 📊 **Statistiques** et rapports financiers
- 👥 **Gestion des utilisateurs** et administrateurs
- 🏷️ **Catégories** et organisation des produits
- 📁 **Upload de fichiers** (images, analyses)
- 🔍 **Recherche avancée** avec filtres

---

## 🏗️ Architecture

```
API INTRAFMC/
├── app/
│   ├── Models/              # Modèles Eloquent
│   ├── Modules/             # Modules GraphQL par domaine
│   │   ├── Auth/           # Authentification
│   │   ├── Product_CBD/    # Gestion produits
│   │   ├── Cart/           # Panier
│   │   ├── Order/          # Commandes
│   │   ├── User/           # Utilisateurs
│   │   ├── Category/       # Catégories
│   │   ├── FinancialRecap/ # Statistiques
│   │   └── Supplier/       # Fournisseurs
│   ├── Services/           # Services métier
│   └── Policies/           # Autorisations
├── database/
│   ├── migrations/         # Migrations BDD
│   └── seeders/           # Données initiales
├── docs/                  # Documentation
├── public/
│   ├── product_images/    # Images produits
│   └── product_analysis/  # Analyses CBD
└── tests/                 # Tests automatisés
```

---

## 🛠️ Technologies

- **Backend**: Laravel 11, PHP 8.1+
- **GraphQL**: Lighthouse 6
- **BDD**: MySQL 8.0+
- **Auth**: JWT (tymon/jwt-auth)
- **Cache**: File/Redis
- **Upload**: Intervention Image
- **Tests**: PHPUnit

---

## 🚀 Déploiement

### Déploiement rapide O2SWITCH

1. **Uploadez** les fichiers dans `/www/api/`
2. **Configurez** `.env` avec vos paramètres
3. **Exécutez** les migrations : `php artisan migrate --force`
4. **Générez** les clés : `php artisan key:generate && php artisan jwt:secret`
5. **Optimisez** : `php artisan optimize`

📖 **Guide détaillé**: [DEPLOIEMENT_PRODUCTION_O2SWITCH.md](DEPLOIEMENT_PRODUCTION_O2SWITCH.md)

---

## 🎯 API GraphQL

### Endpoint principal
```
POST https://votre-domaine.com/graphql
```

### Authentification
```graphql
mutation Login {
  login(email: "admin@example.com", password: "password") {
    access_token
    token_type
    expires_in
  }
}
```

### Exemple de requête
```graphql
query GetProducts {
  productsCBD(first: 10) {
    data {
      id
      name
      price
      stock
      categories {
        name
      }
    }
  }
}
```

📚 **Référence complète**: [API_REFERENCE_PRODUCTION.md](API_REFERENCE_PRODUCTION.md)

---

## 🔐 Sécurité

### Mesures de sécurité implémentées

- ✅ **HTTPS** obligatoire en production
- ✅ **JWT** avec expiration automatique
- ✅ **CORS** configuré selon domaines autorisés
- ✅ **Validation** stricte des données
- ✅ **Autorisations** par rôle (Policy)
- ✅ **Rate limiting** pour éviter les abus
- ✅ **Headers sécurisés** (HSTS, X-Frame-Options, etc.)
- ✅ **Sanitization** des uploads

### Configuration production

```env
APP_ENV=production
APP_DEBUG=false
HTTPS_ONLY=true
GRAPHQL_PLAYGROUND_ENABLED=false
```

---

## 📊 Modules disponibles

### 🔐 Authentification
- Connexion/déconnexion JWT
- Gestion des sessions
- Récupération utilisateur connecté

### 📦 Produits CBD
- CRUD complet des produits
- Gestion du stock
- Upload d'images multiples
- Analyses CBD (PDF)
- Recherche avancée avec filtres

### 🛒 Panier & Commandes
- Ajout/suppression d'articles
- Gestion des quantités
- Processus de commande complet
- Suivi des statuts

### 👥 Utilisateurs
- Gestion des comptes
- Permissions admin/utilisateur
- Profils utilisateur

### 📊 Statistiques
- Revenus par période
- Analyses des ventes
- Rapports financiers
- Métriques performance

### 🏷️ Catégories
- Organisation des produits
- Hiérarchie des catégories
- Statistiques par catégorie

---

## 🧪 Tests

### Tests disponibles
```bash
# Tests complets
php vendor/bin/phpunit

# Tests spécifiques
php vendor/bin/phpunit --filter ProductTest
php vendor/bin/phpunit --filter OrderTest
```

### Couverture
- ✅ Tests unitaires des services
- ✅ Tests d'intégration GraphQL
- ✅ Tests des policies
- ✅ Tests des mutations

---

## 📁 Structure des données

### Principales entités

**Users** → **Orders** → **Products** ← **Categories**
   ↓           ↓           ↓
**Cart**   **OrderProduct** **ProductImages**

### Relations clés
- User `hasMany` Orders, Cart
- Order `belongsToMany` Products (pivot)
- Product `belongsToMany` Categories
- Product `hasMany` Images

---

## 🔧 Configuration

### Variables d'environnement essentielles

```env
# Application
APP_NAME="API INTRAFMC"
APP_ENV=production
APP_URL=https://votre-domaine.com

# Base de données
DB_HOST=votre-host.mysql.db
DB_DATABASE=intrafmc_prod
DB_USERNAME=intrafmc_user
DB_PASSWORD=votre_password

# JWT
JWT_SECRET=votre_jwt_secret
JWT_TTL=60

# CORS
CORS_ALLOWED_ORIGINS=https://votre-frontend.com
```

---

## 📈 Performance

### Optimisations implémentées

- ✅ **Eager loading** pour éviter N+1
- ✅ **Cache** de configuration et routes
- ✅ **Pagination** automatique
- ✅ **Index BDD** sur colonnes critiques
- ✅ **Compression Gzip**
- ✅ **Cache navigateur** pour les assets

### Métriques typiques
- **Temps de réponse**: < 200ms
- **Concurrent users**: 100+
- **Throughput**: 1000+ req/min

---

## 🔧 Maintenance

### Commandes utiles

```bash
# Optimisation production
php artisan optimize

# Cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Base de données
php artisan migrate --force
php artisan db:seed

# Logs
tail -f storage/logs/laravel.log
```

### Monitoring recommandé
- **Uptime**: Pingdom, UptimeRobot
- **Performance**: New Relic, DataDog
- **Erreurs**: Sentry, Bugsnag
- **Logs**: ELK Stack, Splunk

---

## 📞 Support

### Documentation
- 📖 [Guide de déploiement](DEPLOIEMENT_PRODUCTION_O2SWITCH.md)
- 📚 [Référence API](API_REFERENCE_PRODUCTION.md)
- 📋 [Module Order](docs/MODULE_ORDER.md)
- 📦 [Module Produits & Recherche](docs/MODULE_ARRIVAGES_ET_RECHERCHE.md)

### Ressources techniques
- **Laravel**: https://laravel.com/docs
- **Lighthouse**: https://lighthouse-php.com
- **GraphQL**: https://graphql.org

### Contact
- **Repository**: BreizhDigiTech/API_INTRAFMC
- **Branche prod**: `production`
- **Issues**: GitHub Issues

---

## 📋 Checklist de production

- [ ] ✅ Tests passés
- [ ] ✅ Configuration sécurisée (.env)
- [ ] ✅ HTTPS activé
- [ ] ✅ Cache optimisé
- [ ] ✅ Logs configurés
- [ ] ✅ Sauvegardes en place
- [ ] ✅ Monitoring actif
- [ ] ✅ Documentation à jour

---

## 🎉 Changelog

### v1.0.0 (Production)
- ✨ API GraphQL complète
- 🔐 Authentification JWT
- 📦 Gestion produits CBD
- 🛒 Système commandes
- 📊 Statistiques financières
- 🏷️ Gestion catégories
- 👥 Administration utilisateurs
- 📁 Upload de fichiers
- 🔍 Recherche avancée

---

**Prêt pour la production ! 🚀**

L'API INTRAFMC est maintenant optimisée et sécurisée pour un environnement de production sur O2SWITCH.
