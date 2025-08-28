# 📚 Documentation API GraphQL INTRAFMC

Bienvenue dans la documentation complète de l'API GraphQL pour la gestion interne de la SARL FMC.

## 🗂️ Index de la documentation

### 📖 Documents principaux

1. **[📋 Documentation technique complète](./API_DOCUMENTATION.md)**  
   Guide complet avec tous les endpoints, types, et exemples d'utilisation

2. **[🚀 Guide de démarrage rapide](./QUICK_START_GUIDE.md)**  
   Pour commencer rapidement avec les opérations essentielles

3. **[📝 Collection de requêtes GraphQL](./GRAPHQL_QUERIES_COLLECTION.md)**  
   Toutes les requêtes et mutations avec exemples pratiques

4. **[📋 Référence du schéma GraphQL](./GRAPHQL_SCHEMA_REFERENCE.md)**  
   Référence complète de tous les types, inputs, et enums

## 🎯 Par cas d'usage

### 👨‍💻 Pour les développeurs
- **Premiers pas** : [Guide de démarrage](./QUICK_START_GUIDE.md#-premiers-pas)
- **Authentification** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-authentification)
- **Exemples d'intégration** : [QUICK_START_GUIDE.md](./QUICK_START_GUIDE.md#-exemples-dintégration)

### 🛍️ Gestion e-commerce
- **Produits CBD** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#️-module-produits-cbd)
- **Panier** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-panier)
- **Commandes** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-commandes)
- **Catégories** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-catégories)

### 📊 Statistiques et analyses
- **Dashboard** : [GRAPHQL_QUERIES_COLLECTION.md](./GRAPHQL_QUERIES_COLLECTION.md#-statistiques)
- **Insights produits** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-insights-produits-admin)
- **Analytics** : [GRAPHQL_QUERIES_COLLECTION.md](./GRAPHQL_QUERIES_COLLECTION.md#-dashboard-global)

### 👥 Gestion des utilisateurs
- **Utilisateurs** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-utilisateurs)
- **Profils** : [GRAPHQL_QUERIES_COLLECTION.md](./GRAPHQL_QUERIES_COLLECTION.md#-profil-complet)
- **Permissions** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#️-autorisations-et-sécurité)

### 🏭 Gestion d'inventaire
- **Fournisseurs** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-fournisseurs)
- **Arrivages** : [API_DOCUMENTATION.md](./API_DOCUMENTATION.md#-module-arrivages)
- **Stock** : [GRAPHQL_QUERIES_COLLECTION.md](./GRAPHQL_QUERIES_COLLECTION.md#️-produits-cbd)

## 🔧 Par module technique

| Module | Documentation | Requêtes | Types |
|--------|---------------|----------|--------|
| **Auth** | [Authentification](./API_DOCUMENTATION.md#-authentification) | [Auth Queries](./GRAPHQL_QUERIES_COLLECTION.md#-authentification) | [Auth Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-dauthentification) |
| **Products** | [Produits CBD](./API_DOCUMENTATION.md#️-module-produits-cbd) | [Product Queries](./GRAPHQL_QUERIES_COLLECTION.md#️-produits-cbd) | [Product Types](./GRAPHQL_SCHEMA_REFERENCE.md#️-types-produits) |
| **Cart** | [Panier](./API_DOCUMENTATION.md#-module-panier) | [Cart Queries](./GRAPHQL_QUERIES_COLLECTION.md#-panier) | [Cart Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-panier) |
| **Orders** | [Commandes](./API_DOCUMENTATION.md#-module-commandes) | [Order Queries](./GRAPHQL_QUERIES_COLLECTION.md#-commandes) | [Order Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-commandes) |
| **Categories** | [Catégories](./API_DOCUMENTATION.md#-module-catégories) | [Category Queries](./GRAPHQL_QUERIES_COLLECTION.md#-catégories) | [Category Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-catégories) |
| **Users** | [Utilisateurs](./API_DOCUMENTATION.md#-module-utilisateurs) | [User Queries](./GRAPHQL_QUERIES_COLLECTION.md#-utilisateurs) | [User Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-utilisateur) |
| **Statistics** | [Statistiques](./API_DOCUMENTATION.md#-module-statistiques) | [Stats Queries](./GRAPHQL_QUERIES_COLLECTION.md#-statistiques) | [Stats Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-statistiques) |
| **Suppliers** | [Fournisseurs](./API_DOCUMENTATION.md#-module-fournisseurs) | [Supplier Queries](./GRAPHQL_QUERIES_COLLECTION.md#-fournisseurs) | [Supplier Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-fournisseurs) |
| **Arrivals** | [Arrivages](./API_DOCUMENTATION.md#-module-arrivages) | [Arrival Queries](./GRAPHQL_QUERIES_COLLECTION.md#-arrivages) | [Arrival Types](./GRAPHQL_SCHEMA_REFERENCE.md#-types-arrivages) |

## 🔍 Recherche rapide

### Opérations courantes
- **Se connecter** : [Login](./QUICK_START_GUIDE.md#1--authentification)
- **Lister produits** : [Products List](./QUICK_START_GUIDE.md#️-gestion-des-produits)
- **Ajouter au panier** : [Add to Cart](./QUICK_START_GUIDE.md#-gestion-du-panier)
- **Passer commande** : [Checkout](./QUICK_START_GUIDE.md#-finaliser-une-commande)
- **Voir statistiques** : [Dashboard](./QUICK_START_GUIDE.md#-statistiques-rapides)

### Types de données essentiels
- **[ProductCBD](./GRAPHQL_SCHEMA_REFERENCE.md#️-types-produits)** : Structure des produits
- **[Order](./GRAPHQL_SCHEMA_REFERENCE.md#-types-commandes)** : Structure des commandes
- **[User](./GRAPHQL_SCHEMA_REFERENCE.md#-types-utilisateur)** : Structure des utilisateurs
- **[Cart](./GRAPHQL_SCHEMA_REFERENCE.md#-types-panier)** : Structure du panier

### Erreurs courantes
- **Token expiré** : [Gestion d'erreurs](./QUICK_START_GUIDE.md#️-cas-derreurs-courants)
- **Permissions** : [Autorisations](./API_DOCUMENTATION.md#️-autorisations-et-sécurité)
- **Validation** : [Types d'erreur](./API_DOCUMENTATION.md#-gestion-derreurs)

## 🛠️ Outils et ressources

### Interfaces de test
- **GraphQL Playground** : `http://localhost:8000/graphql-playground`
- **Endpoint principal** : `http://localhost:8000/graphql`

### Headers requis
```http
Content-Type: application/json
Authorization: Bearer {your_jwt_token}
```

### Clients recommandés
- **[Insomnia](https://insomnia.rest/)** : Client API complet
- **[Postman](https://www.postman.com/)** : Client populaire avec support GraphQL
- **[Apollo Client](https://www.apollographql.com/docs/react/)** : Pour React/Vue.js
- **[GraphQL Code Generator](https://www.graphql-code-generator.com/)** : Génération de types

## 📱 Exemples par plateforme

### JavaScript/TypeScript
```javascript
// Voir: QUICK_START_GUIDE.md#javascript-fetch
```

### React avec Apollo
```jsx
// Voir: API_DOCUMENTATION.md#exemples-d-intégration
```

### Vue.js avec Apollo
```vue
// Voir: API_DOCUMENTATION.md#exemples-d-intégration
```

### cURL
```bash
# Voir: QUICK_START_GUIDE.md#curl
```

## 🔐 Sécurité

### Authentification
- **JWT Bearer Token** requis pour toutes les opérations
- **Durée de vie** : Configurable (défaut: 1 heure)
- **Refresh** : Reconnexion nécessaire

### Autorisations
- **Utilisateur** : Accès aux données personnelles
- **Admin** : Accès complet à toutes les données
- **Permissions granulaires** : Par ressource et action

### Bonnes pratiques
- Toujours vérifier les erreurs GraphQL
- Utiliser HTTPS en production
- Stocker les tokens de manière sécurisée
- Implémenter la rotation des tokens

## 📞 Support et contribution

### Contacts
- **Email technique** : dev@fmc-sarl.fr
- **Chat** : Slack #api-support
- **Issues** : GitHub Issues

### Contribution
- **Bug reports** : [GitHub Issues](https://github.com/BreizhDigiTech/API_INTRAFMC/issues)
- **Feature requests** : Contact l'équipe technique
- **Documentation** : Pull requests bienvenues

### Versioning
- **Version actuelle** : 1.0.0
- **Politique** : Semantic Versioning (SemVer)
- **Changelog** : Disponible dans les releases GitHub

---

## 🎯 Commencer maintenant

1. **Nouveau développeur** → [Guide de démarrage rapide](./QUICK_START_GUIDE.md)
2. **Référence complète** → [Documentation technique](./API_DOCUMENTATION.md)
3. **Exemples concrets** → [Collection de requêtes](./GRAPHQL_QUERIES_COLLECTION.md)
4. **Types et schémas** → [Référence schéma](./GRAPHQL_SCHEMA_REFERENCE.md)

---

**📅 Dernière mise à jour** : 28 août 2025  
**👥 Équipe** : BreizhDigiTech  
**🏢 Client** : SARL FMC  
**🔗 Version API** : 1.0.0
