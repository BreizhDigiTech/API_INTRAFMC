# 🚀 API INTRAFMC - Référence Rapide Production

## 🔗 Endpoints Principaux

### 🏠 Base URL
```
https://votre-domaine.com
```

### 🎯 GraphQL Endpoint
```
POST https://votre-domaine.com/graphql
Content-Type: application/json
Authorization: Bearer {token}
```

---

## 🔐 Authentification

### Connexion
```graphql
mutation Login {
  login(email: "admin@example.com", password: "votre-password") {
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

### Utilisateur connecté
```graphql
query Me {
  me {
    id
    name
    email
    is_admin
    is_active
  }
}
```

---

## 📦 Produits CBD

### Liste des produits (avec pagination)
```graphql
query GetProducts {
  productsCBD(first: 20, page: 1) {
    data {
      id
      name
      description
      price
      stock
      images
      categories {
        id
        name
      }
    }
    paginatorInfo {
      count
      currentPage
      total
      hasMorePages
    }
  }
}
```

### Recherche de produits
```graphql
query SearchProducts {
  searchProducts(
    query: "cbd"
    category_id: 1
    min_price: 10.0
    max_price: 100.0
    in_stock: true
  ) {
    id
    name
    price
    stock
  }
}
```

### Créer un produit (Admin)
```graphql
mutation CreateProduct {
  createProductCBD(input: {
    name: "Nouveau Produit"
    description: "Description du produit"
    price: 29.99
    stock: 100
    thc_content: 0.2
    cbd_content: 15.0
    category_ids: [1, 2]
  }) {
    id
    name
    price
    stock
  }
}
```

---

## 🛒 Panier

### Mon panier
```graphql
query MyCart {
  myCart {
    id
    quantity
    created_at
    product {
      id
      name
      price
      stock
      images
    }
    total_price
  }
}
```

### Ajouter au panier
```graphql
mutation AddToCart {
  addToCart(input: {
    product_id: 1
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

---

## 📋 Commandes

### Mes commandes
```graphql
query MyOrders {
  myOrders {
    id
    total
    status
    formatted_status
    created_at
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

### Passer commande (Checkout)
```graphql
mutation Checkout {
  checkout {
    id
    total
    status
    total_items
    product_count
    created_at
  }
}
```

### Annuler commande
```graphql
mutation CancelOrder {
  cancelOrder(id: 1)
}
```

---

## 👥 Utilisateurs (Admin)

### Liste des utilisateurs
```graphql
query GetUsers {
  users(first: 20) {
    data {
      id
      name
      email
      is_admin
      is_active
      created_at
    }
  }
}
```

### Créer utilisateur
```graphql
mutation CreateUser {
  updateUser(
    id: 0
    name: "Nouvel Utilisateur"
    email: "user@example.com"
    password: "password123"
    is_active: true
    is_admin: false
  ) {
    id
    name
    email
  }
}
```

---

## 📊 Statistiques

### Statistiques des commandes
```graphql
query OrderStats {
  orderStatistics(
    startDate: "2025-01-01"
    endDate: "2025-12-31"
  ) {
    totalRevenue
    totalOrders
    averageOrderValue
    uniqueCustomers
  }
}
```

### Revenus mensuels
```graphql
query MonthlyRevenue {
  monthlyRevenue(months: 12) {
    data {
      month
      revenue
      orderCount
    }
    total_revenue
    total_orders
  }
}
```

---

## 🏷️ Catégories

### Liste des catégories
```graphql
query GetCategories {
  categories {
    data {
      id
      name
      description
      products_count
    }
  }
}
```

### Créer catégorie
```graphql
mutation CreateCategory {
  createCategory(input: {
    name: "Nouvelle Catégorie"
    description: "Description de la catégorie"
  }) {
    id
    name
    description
  }
}
```

---

## 📁 Upload de fichiers

### Upload d'images produit
```graphql
mutation UploadImages {
  uploadProductImages(
    productId: 1
    images: [$file1, $file2]
  ) {
    filename
    original_name
    size
    url
  }
}
```

---

## ⚠️ Gestion d'erreurs

### Format de réponse d'erreur
```json
{
  "errors": [
    {
      "message": "Message d'erreur",
      "extensions": {
        "category": "validation",
        "code": "VALIDATION_ERROR"
      }
    }
  ]
}
```

### Codes d'erreur courants
- `UNAUTHENTICATED`: Token manquant ou invalide
- `VALIDATION_ERROR`: Données de saisie invalides
- `FORBIDDEN`: Permissions insuffisantes
- `NOT_FOUND`: Ressource introuvable

---

## 🔧 Headers HTTP requis

```bash
# Authentification
Authorization: Bearer {your-jwt-token}

# Content-Type
Content-Type: application/json

# CORS (si nécessaire)
Origin: https://votre-frontend.com
```

---

## 📝 Exemples cURL

### Connexion
```bash
curl -X POST https://votre-domaine.com/graphql \
  -H "Content-Type: application/json" \
  -d '{
    "query": "mutation { login(email: \"admin@example.com\", password: \"password\") { access_token } }"
  }'
```

### Requête authentifiée
```bash
curl -X POST https://votre-domaine.com/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "query": "query { me { id name email } }"
  }'
```

---

## 🚨 Limites et quotas

- **Taille max upload**: 32MB
- **Timeout requête**: 30 secondes
- **Pagination max**: 100 éléments par page
- **Rate limiting**: 1000 requêtes/heure par IP

---

## 📞 Support

- **Documentation complète**: `/docs/`
- **Logs d'erreur**: `storage/logs/laravel.log`
- **Mode debug**: Désactivé en production
- **Cache**: Activé (config, routes, vues)

---

**Version**: 1.0 - Production  
**Dernière MAJ**: Septembre 2025
