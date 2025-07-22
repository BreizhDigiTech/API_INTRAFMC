# Documentation API GraphQL - API_INTRAFMC

## Vue d'ensemble
Cette API GraphQL fournit un système complet de gestion pour une plateforme e-commerce CBD avec authentification, gestion des produits, commandes et arrivages.

## Authentification
L'API utilise JWT (JSON Web Tokens) pour l'authentification.

### Connexion
```graphql
mutation {
  login(email: "admin@admin.com", password: "L15fddef!") {
    access_token
    token_type
    expires_in
    user {
      id
      name
      email
      is_admin
    }
  }
}
```

### Déconnexion
```graphql
mutation {
  logout {
    message
  }
}
```

### Profil utilisateur
```graphql
query {
  me {
    id
    name
    email
    avatar
    is_admin
    is_active
  }
}
```

## Modules disponibles

### 1. Authentification (Auth)
- ✅ Connexion/Déconnexion
- ✅ Récupération du profil utilisateur
- ✅ Protection JWT

### 2. Enregistrement (Register)
- ✅ Création de compte utilisateur
- ✅ Validation des données
- ✅ Génération automatique de JWT

### 3. Gestion des utilisateurs (User) - Admin uniquement
- ✅ CRUD complet des utilisateurs
- ✅ Gestion des profils
- ✅ Changement de mot de passe
- ✅ Activation/désactivation des comptes

### 4. Catégories (Category)
- ✅ CRUD complet des catégories
- ✅ Association avec les produits
- ✅ Validation de l'unicité

### 5. Produits CBD (ProductCBD)
- ✅ CRUD complet des produits
- ✅ Gestion du stock
- ✅ Images et analyses
- ✅ Association avec catégories et fournisseurs

### 6. Panier (Cart)
- ✅ Ajout/suppression de produits
- ✅ Modification des quantités
- ✅ Calcul automatique du total
- ✅ Validation du stock

### 7. Commandes (Order)
- ✅ Création de commandes
- ✅ Calcul automatique du total
- ✅ Gestion des statuts
- ✅ Historique des commandes

### 8. Fournisseurs (Supplier) - Admin uniquement
- ✅ CRUD complet des fournisseurs
- ✅ Association avec les produits
- ✅ Gestion des relations many-to-many

### 9. Arrivages (Arrival) - Admin uniquement
- ✅ Création d'arrivages avec produits
- ✅ Validation des arrivages
- ✅ Mise à jour automatique du stock
- ✅ Suivi des statuts

## Permissions et sécurité

### Niveaux d'accès :
1. **Public** : Inscription uniquement
2. **Utilisateur connecté** : Gestion du panier, commandes, profil
3. **Administrateur** : Accès complet à tous les modules

### Contrôles de sécurité :
- ✅ Authentification JWT obligatoire
- ✅ Vérification des permissions par rôle
- ✅ Validation des données d'entrée
- ✅ Protection contre les injections SQL
- ✅ Gestion des erreurs sécurisée

## Tests et qualité

### Couverture de tests :
- **69/69 tests** passent (100%)
- Tests d'intégration complets
- Validation des permissions
- Tests des cas d'erreur
- Tests de performance

### Modules testés :
- ✅ Authentification et sécurité
- ✅ CRUD complet pour tous les modules
- ✅ Logique métier complexe
- ✅ Gestion des erreurs
- ✅ Contrôles d'accès

## Architecture technique

### Stack technique :
- **Laravel 12.x** : Framework PHP moderne
- **Lighthouse GraphQL 6.59.0** : Serveur GraphQL pour Laravel
- **JWT Authentication** : Authentification sécurisée
- **PHPUnit 11.5.23** : Framework de tests
- **SQLite/MySQL** : Base de données flexible

### Patterns utilisés :
- **Repository Pattern** : Services dédiés par module
- **Policy Pattern** : Gestion fine des permissions
- **Factory Pattern** : Génération de données de test
- **Exception Handling** : Gestion centralisée des erreurs

## Déploiement et maintenance

### Prérequis :
- PHP 8.2+
- Composer
- Base de données MySQL/SQLite
- Extension JWT

### Installation :
```bash
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan db:seed
```

### Tests :
```bash
php artisan test tests/Feature/GraphQL/
```

## Support et évolutions

Cette API est prête pour la production avec une couverture de tests complète et une architecture robuste. Elle peut facilement être étendue avec de nouveaux modules suivant les mêmes patterns établis.
