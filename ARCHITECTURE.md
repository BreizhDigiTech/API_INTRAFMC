# Architecture de l'API INTRAFMC

## Vue d'ensemble
API GraphQL modulaire construite avec Laravel et Lighthouse pour la gestion d'un e-commerce CBD.

## Structure architecturale

### 1. Architecture GraphQL Pure
- **Endpoint unique** : `/graphql`
- **Aucune route REST** : Toutes les opérations passent par GraphQL
- **Rate limiting** : 60 requêtes/minute avec middleware de sécurité
- **Authentification JWT** : Tokens sécurisés pour l'authentification

### 2. Organisation modulaire

#### Modules métier (app/Modules/)
- **Auth** : Authentification et gestion des sessions
- **Register** : Inscription des utilisateurs
- **User** : Gestion des profils utilisateurs
- **Product_CBD** : Catalogue des produits CBD
- **Category** : Classification des produits
- **Supplier** : Gestion des fournisseurs
- **Arrival** : Réception des stocks
- **Cart** : Panier d'achat
- **Order** : Gestion des commandes
- **Statistics** : Métriques et analytiques

#### Structure d'un module
```
app/Modules/{Module}/
├── GraphQL/
│   ├── schema.graphql       # Définitions des types GraphQL
│   ├── Queries/            # Résolveurs de requêtes
│   ├── Mutations/          # Résolveurs de mutations
│   └── Resolvers/          # Résolveurs de champs
└── Services/               # Logique métier
```

### 3. Modèles de données

#### Modèles principaux (app/Models/)
- **User** : Utilisateurs avec JWT et rôles
- **ProductCBD** : Produits avec gestion d'images
- **Category** : Catégories hiérarchiques
- **Order** : Commandes avec statuts
- **Cart** : Paniers temporaires
- **Supplier** : Fournisseurs
- **CbdArrival** : Arrivages de stock

### 4. Optimisations GraphQL

#### Requêtes d'optimisation (app/GraphQL/Queries/)
- **DashboardStatsQuery** : Statistiques de tableau de bord
- **OrdersSummaryQuery** : Résumé des commandes
- **UsersSummaryQuery** : Résumé des utilisateurs
- **EcommerceSummaryQuery** : Vue d'ensemble e-commerce
- **ProductsSearchQuery** : Recherche de produits
- **CategoriesListQuery** : Liste optimisée des catégories

### 5. Sécurité de production

#### Middleware de sécurité
- **SecurityHeaders** : Headers de sécurité HTTP
- **GraphQLRateLimiter** : Limitation de taux pour GraphQL
- **AttemptAuthentication** : Authentification JWT

#### Configuration de sécurité
- Rate limiting : 60 req/min par IP
- Headers sécurisés : CSP, HSTS, X-Frame-Options
- Complexité des requêtes limitée
- Validation stricte des inputs

### 6. Tests

#### Suite de tests complète (tests/Feature/)
- **AuthTest** : Tests d'authentification
- **RegisterTest** : Tests d'inscription
- **ProductTest** : Tests des produits
- **CategoryTest** : Tests des catégories
- **OptimizationAPIsTest** : Tests des APIs d'optimisation

#### Couverture
- 15 tests avec 57 assertions
- 100% de réussite
- Authentification, CRUD, et filtrage

## Avantages de cette architecture

### Modularité
- Séparation claire des responsabilités
- Modules indépendants et réutilisables
- Évolutivité facilitée

### Performance
- Requêtes GraphQL optimisées
- Pagination et filtrage efficaces
- Cache et rate limiting

### Sécurité
- JWT avec expiration
- Validation stricte
- Middleware de protection

### Maintenabilité
- Code organisé par domaine
- Tests automatisés
- Documentation intégrée

## Utilisation

### Authentification
```graphql
mutation {
  login(email: "user@example.com", password: "password") {
    access_token
    user { id name email }
  }
}
```

### Requête de données
```graphql
query {
  products(filters: { category_id: 1 }) {
    data { id name price image_url }
    paginatorInfo { total }
  }
}
```

### Statistiques
```graphql
query {
  dashboardStats {
    orders { total thisMonth }
    revenue { total }
    users { totalUsers }
  }
}
```

Cette architecture assure une API robuste, sécurisée et évolutive pour l'e-commerce CBD.
