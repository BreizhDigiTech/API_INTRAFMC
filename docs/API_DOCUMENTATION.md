# 📚 API GraphQL - SARL FMC (INTRAFMC)

## 🎯 Vue d'ensemble

Cette API GraphQL est conçue pour la gestion interne de la SARL FMC, spécialisée dans la vente de produits CBD. Elle fournit une interface unifiée pour la gestion des produits, commandes, utilisateurs, et statistiques.

### 🔧 Informations techniques
- **Framework** : Laravel 11.x + Lighthouse GraphQL
- **Authentification** : JWT (JSON Web Tokens)
- **Base de données** : MySQL
- **Architecture** : Modulaire avec séparation des responsabilités

---

## 🔑 Authentification

### Types d'authentification
- **JWT Bearer Token** : Requis pour toutes les opérations (sauf login/register)
- **Rôles** : `user` (client) et `admin` (administrateur)

### Endpoints d'authentification

#### 🔓 Connexion
```graphql
mutation Login {
  login(email: "user@example.com", password: "password") {
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

#### 👤 Utilisateur connecté
```graphql
query Me {
  me {
    id
    name
    email
    phone
    address
    is_admin
    is_active
  }
}
```

#### 🚪 Déconnexion
```graphql
mutation Logout {
  logout {
    message
  }
}
```

---

## 🛍️ Module Produits CBD

### Types de données

#### ProductCBD
```graphql
type ProductCBD {
  id: ID!
  name: String!
  description: String
  price: Float!
  stock: Int!
  images: [String!]!
  analysis_file: String
  categories: [Category!]!
  suppliers: [Supplier!]!
  created_at: DateTime
  updated_at: DateTime
}
```

### Requêtes (Queries)

#### 📦 Liste des produits
```graphql
query GetProducts {
  productsCBD(first: 20, page: 1) {
    id
    name
    price
    stock
    images
    categories {
      id
      name
    }
  }
}
```

#### 🔍 Recherche avancée
```graphql
query SearchProducts {
  searchProducts(
    query: "huile",
    first: 10,
    category_id: "1",
    min_price: 10.0,
    max_price: 100.0,
    in_stock: true
  ) {
    id
    name
    price
    stock
    description
  }
}
```

#### 📊 Insights produits (Admin)
```graphql
query ProductInsights {
  productInsights(productId: "1") {
    productId
    productName
    currentPrice
    currentStock
    baseMetrics {
      totalSales
      totalRevenue
      averageRating
    }
    recommendations {
      type
      priority
      description
    }
  }
}
```

### Mutations

#### ➕ Créer un produit
```graphql
mutation CreateProduct {
  createProductCBD(input: {
    name: "Huile CBD 10%"
    description: "Huile de haute qualité"
    price: 49.99
    stock: 100
    category_ids: ["1", "2"]
  }) {
    id
    name
    price
    stock
  }
}
```

#### 📷 Créer produit avec fichiers
```graphql
mutation CreateProductWithFiles {
  createProductCBDWithFiles(
    name: "Huile CBD Premium"
    price: 79.99
    stock: 50
    images: [$imageFile1, $imageFile2]
    analysis_file: $analysisFile
  ) {
    id
    name
    images
    analysis_file
  }
}
```

#### ✏️ Modifier un produit
```graphql
mutation UpdateProduct {
  updateProductCBD(id: "1", input: {
    price: 55.99
    stock: 75
  }) {
    id
    name
    price
    stock
  }
}
```

---

## 🛒 Module Panier

### Types de données

#### Cart
```graphql
type Cart {
  id: ID!
  user_id: ID!
  product_id: ID!
  quantity: Int!
  product: ProductCBD!
  created_at: DateTime
}
```

### Requêtes

#### 🛒 Mon panier
```graphql
query MyCart {
  myCart {
    id
    quantity
    product {
      id
      name
      price
      images
    }
  }
}
```

#### 💡 Suggestions produits
```graphql
query CartSuggestions {
  cartSuggestions(limit: 5, type: ALL) {
    id
    name
    price
    reason
    confidence
  }
}
```

### Mutations

#### ➕ Ajouter au panier
```graphql
mutation AddToCart {
  addToCart(input: {
    product_id: "1"
    quantity: 2
  }) {
    id
    quantity
    product {
      name
      price
    }
  }
}
```

#### 🔄 Modifier quantité
```graphql
mutation UpdateCartItem {
  updateCartItem(id: "1", input: {
    quantity: 3
  }) {
    id
    quantity
  }
}
```

#### 🗑️ Vider le panier
```graphql
mutation ClearCart {
  clearCart {
    success
    message
  }
}
```

---

## 📦 Module Commandes

### Types de données

#### Order
```graphql
type Order {
  id: ID!
  user_id: ID!
  total: Float!
  status: String! # pending, validated, shipped, delivered, cancelled
  user: User!
  products: [ProductCBD!]!
  created_at: DateTime
  updated_at: DateTime
}
```

### Requêtes

#### 📋 Mes commandes
```graphql
query MyOrders {
  myOrders {
    id
    total
    status
    created_at
    products {
      id
      name
      price
      pivot {
        quantity
        unit_price
      }
    }
  }
}
```

#### 📊 Toutes les commandes (Admin)
```graphql
query AllOrders {
  orders {
    id
    total
    status
    user {
      name
      email
    }
    created_at
  }
}
```

### Mutations

#### 💳 Finaliser commande
```graphql
mutation Checkout {
  checkout {
    id
    total
    status
    products {
      id
      name
      pivot {
        quantity
        unit_price
      }
    }
  }
}
```

#### ❌ Annuler commande
```graphql
mutation CancelOrder {
  cancelOrder(id: "1")
}
```

#### 🔄 Changer statut (Admin)
```graphql
mutation UpdateOrderStatus {
  updateOrderStatus(input: {
    id: "1"
    status: "shipped"
  }) {
    id
    status
  }
}
```

---

## 📂 Module Catégories

### Types de données

#### Category
```graphql
type Category {
  id: ID!
  name: String!
  description: String
  products: [ProductCBD!]!
}
```

### Requêtes

#### 📋 Liste des catégories
```graphql
query Categories {
  categories {
    id
    name
    description
  }
}
```

#### 📊 Catégories avec compteurs
```graphql
query CategoriesWithCounts {
  categoriesWithCounts {
    id
    name
    productCount
    isActive
  }
}
```

### Mutations

#### ➕ Créer catégorie
```graphql
mutation CreateCategory {
  createCategory(input: {
    name: "Huiles CBD"
    description: "Huiles de qualité premium"
  }) {
    id
    name
  }
}
```

---

## 👥 Module Utilisateurs

### Types de données

#### User
```graphql
type User {
  id: ID!
  name: String!
  email: String!
  phone: String
  address: String
  birth_date: Date
  avatar: String
  is_admin: Boolean!
  is_active: Boolean!
  created_at: DateTime
}
```

### Requêtes

#### 👥 Liste utilisateurs (Admin)
```graphql
query Users {
  users(first: 20) {
    id
    name
    email
    is_admin
    is_active
    created_at
  }
}
```

### Mutations

#### ✏️ Modifier profil
```graphql
mutation UpdateProfile {
  updateProfile(
    id: "1"
    name: "Nouveau nom"
    phone: "0123456789"
  ) {
    id
    name
    phone
  }
}
```

#### 🔐 Changer mot de passe
```graphql
mutation ChangePassword {
  changePassword(
    current_password: "oldpass"
    new_password: "newpass"
  ) {
    success
    message
  }
}
```

---

## 📊 Module Statistiques

### Requêtes avancées

#### 📈 Statistiques commandes
```graphql
query OrderStatistics {
  orderStatistics(
    startDate: "2024-01-01"
    endDate: "2024-12-31"
  ) {
    totalRevenue
    totalOrders
    averageOrderValue
    uniqueCustomers
    topProducts
    topCustomers
  }
}
```

#### 💰 Timeline des revenus
```graphql
query RevenueTimeline {
  revenueTimeline(
    startDate: "2024-01-01"
    endDate: "2024-12-31"
    groupBy: MONTH
  ) {
    periods
    totalRevenue
    totalOrders
  }
}
```

#### 📊 Dashboard général
```graphql
query DashboardStats {
  dashboardStats(period: MONTH)
}
```

---

## 🏭 Module Fournisseurs

### Types de données

#### Supplier
```graphql
type Supplier {
  id: ID!
  name: String!
  email: String
  phone: String
  address: String
  website: String
  contact_person: String
  products: [ProductCBD!]!
}
```

### Requêtes

#### 🏭 Liste fournisseurs
```graphql
query Suppliers {
  suppliers {
    id
    name
    email
    products {
      id
      name
    }
  }
}
```

### Mutations

#### ➕ Créer fournisseur
```graphql
mutation CreateSupplier {
  createSupplier(
    name: "Fournisseur Bio"
    email: "contact@bio.com"
    phone: "0123456789"
  ) {
    id
    name
  }
}
```

---

## 📦 Module Arrivages

### Types de données

#### CbdArrival
```graphql
type CbdArrival {
  id: ID!
  amount: Float!
  status: String! # pending, validated
  products: [ArrivalProductCbd!]!
  created_at: DateTime
}
```

### Requêtes

#### 📦 Liste arrivages
```graphql
query Arrivals {
  arrivals {
    id
    amount
    status
    products {
      id
      product_id
      quantity
      cost_price
    }
  }
}
```

### Mutations

#### ✅ Valider arrivage
```graphql
mutation ValidateArrival {
  validateArrival(arrival_id: "1") {
    id
    status
  }
}
```

---

## 🔧 APIs d'optimisation

### Résumés rapides

#### 📊 Résumé commandes
```graphql
query OrdersSummary {
  ordersSummary {
    totalOrders
    pendingOrders
    validatedOrders
    totalRevenue
  }
}
```

#### 👥 Résumé utilisateurs
```graphql
query UsersSummary {
  usersSummary {
    totalUsers
    activeUsers
    adminUsers
    recentRegistrations
  }
}
```

#### 🛍️ Résumé e-commerce
```graphql
query EcommerceSummary {
  ecommerceSummary {
    totalProducts
    totalCategories
    lowStockProducts
    outOfStockProducts
    totalValue
  }
}
```

---

## 🔍 Recherches avancées

#### 👥 Recherche utilisateurs
```graphql
query SearchUsers {
  usersSearch(
    search: "john"
    role: "admin"
    status: "active"
    first: 10
  ) {
    id
    name
    email
    is_admin
  }
}
```

#### 🛍️ Recherche produits
```graphql
query SearchProducts {
  productsSearch(
    search: "cbd"
    category: "1"
    minPrice: 10.0
    maxPrice: 100.0
    inStock: true
    first: 20
  ) {
    id
    name
    price
    stock
  }
}
```

---

## 📋 Types communs

### Scalaires personnalisés
```graphql
scalar DateTime # Format: Y-m-d H:i:s
scalar Date     # Format: Y-m-d
scalar Upload   # Upload de fichiers multipart
scalar JSON     # Données JSON arbitraires
```

### Réponses standard
```graphql
type DeleteResponse {
  success: Boolean
  message: String
}

type SuccessResponse {
  success: Boolean!
  message: String!
}

type FileUploadResponse {
  success: Boolean!
  message: String!
  url: String
  path: String
}
```

### Enums
```graphql
enum TrendDirection {
  UP
  DOWN
  STABLE
}

enum TimeGrouping {
  DAY
  WEEK
  MONTH
  QUARTER
  YEAR
}

enum PopularityPeriod {
  WEEK
  MONTH
  QUARTER
  YEAR
}
```

---

## 🛡️ Autorisations et sécurité

### Directives d'autorisation
- `@guard` : Authentification requise
- `@can(ability: "action", model: "Model")` : Vérification des permissions
- `@rules(apply: ["rule1", "rule2"])` : Validation des données

### Niveaux d'accès
- **Public** : Login, register
- **Utilisateur authentifié** : Profil, panier, commandes personnelles
- **Administrateur** : Gestion complète, statistiques, tous les utilisateurs

---

## 📡 Endpoint GraphQL

**URL** : `http://votre-domaine.com/graphql`

### Headers requis
```http
Content-Type: application/json
Authorization: Bearer {votre_jwt_token}
```

### Introspection
```graphql
query IntrospectionQuery {
  __schema {
    types {
      name
      description
    }
  }
}
```

---

## 🔄 Pagination

La plupart des listes supportent la pagination :
```graphql
query Products {
  productsCBD(first: 20, page: 2) {
    # ...
  }
}
```

**Paramètres** :
- `first` : Nombre d'éléments (défaut: 20, max: 100)
- `page` : Numéro de page (démarre à 1)

---

## 📝 Notes de développement

### Optimisations disponibles
- Eager loading automatique des relations
- Cache des requêtes fréquentes
- Optimisation des requêtes N+1

### Upload de fichiers
Supporté via multipart/form-data avec le scalar `Upload`.

### Validation
Toutes les mutations incluent une validation automatique avec des messages d'erreur détaillés.

---

## 🐛 Gestion d'erreurs

Les erreurs GraphQL incluent :
- **Code d'erreur** : Type d'erreur (validation, autorisation, etc.)
- **Message** : Description détaillée
- **Extensions** : Informations de debug (en mode développement)

Exemple de réponse d'erreur :
```json
{
  "errors": [
    {
      "message": "Validation failed",
      "extensions": {
        "validation": {
          "email": ["Le champ email est obligatoire"]
        }
      }
    }
  ]
}
```

---

**📅 Dernière mise à jour** : 28 août 2025  
**📧 Support** : contact@fmc-sarl.fr  
**🔗 Version** : 1.0.0
