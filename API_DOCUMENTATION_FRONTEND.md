# 📋 DOCUMENTATION API GRAPHQL - API_INTRAFMC

## 🚀 Configuration Initiale

### Variables d'Environnement
```env
# API Base URL
GRAPHQL_ENDPOINT=http://127.0.0.1:8000/graphql

# Authentication
JWT_SECRET=your_jwt_secret_key
JWT_TTL=60 # minutes

# Headers requis
CONTENT_TYPE=application/json
ACCEPT=application/json
```

### Headers HTTP Obligatoires
```javascript
{
  "Content-Type": "application/json",
  "Accept": "application/json",
  "Authorization": "Bearer YOUR_JWT_TOKEN" // Pour les requêtes authentifiées
}
```

---

## 🔐 AUTHENTIFICATION

### 1. Connexion (Login)
```graphql
mutation Login {
  login(email: "admin@example.com", password: "password") {
    access_token
    token_type
    expires_in
    user {
      id
      name
      email
      is_admin
      is_active
    }
  }
}
```

**Réponse Success:**
```json
{
  "data": {
    "login": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "Bearer",
      "expires_in": 3600,
      "user": {
        "id": "1",
        "name": "Admin User",
        "email": "admin@example.com",
        "is_admin": true,
        "is_active": true
      }
    }
  }
}
```

### 2. Déconnexion (Logout)
```graphql
mutation Logout {
  logout {
    message
  }
}
```

### 3. Inscription (Register)
```graphql
mutation Register {
  register(input: {
    name: "John Doe"
    email: "john@example.com"
    password: "password123"
    password_confirmation: "password123"
  }) {
    access_token
    token_type
    expires_in
    user {
      id
      name
      email
    }
  }
}
```

---

## 📦 PRODUITS CBD

### 1. Liste des Produits (avec pagination automatique)
```graphql
query GetProducts($first: Int, $page: Int) {
  products(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
      id
      name
      description
      price
      stock
      images
      analysis_file_url
      category_id
      categories {
        id
        name
      }
      suppliers {
        id
        name
        email
      }
      created_at
      updated_at
    }
  }
}
```

### 2. Produit Spécifique
```graphql
query GetProduct {
  product(id: "1") {
    id
    name
    description
    price
    stock
    images
    analysis_file_url
    categories {
      id
      name
      description
    }
    suppliers {
      id
      name
      email
      phone
    }
  }
}
```

### 3. Créer un Produit
```graphql
mutation CreateProduct {
  createProduct(input: {
    name: "CBD Oil Premium"
    description: "Huile CBD premium 10%"
    price: 29.99
    stock: 100
    images: ["image1.jpg", "image2.jpg"]
    category_id: 1
    analysis_file: "analysis.pdf"
  }) {
    id
    name
    price
    stock
  }
}
```

### 4. Modifier un Produit
```graphql
mutation UpdateProduct {
  updateProduct(id: "1", input: {
    name: "CBD Oil Premium Updated"
    price: 34.99
    stock: 85
  }) {
    id
    name
    price
    stock
    updated_at
  }
}
```

### 5. Supprimer un Produit
```graphql
mutation DeleteProduct {
  deleteProduct(id: "1") {
    success
    message
  }
}
```

---

## 🏷️ CATÉGORIES

### 1. Liste des Catégories (avec pagination automatique)
```graphql
query GetCategories($first: Int, $page: Int) {
  categories(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
      id
      name
      description
      products {
        id
        name
        price
      }
      created_at
    }
  }
}
```

### 2. Catégorie Spécifique
```graphql
query GetCategory {
  category(id: "1") {
    id
    name
    description
    products {
      id
      name
      price
      stock
      images
    }
  }
}
```

### 3. Créer une Catégorie
```graphql
mutation CreateCategory {
  createCategory(input: {
    name: "Huiles CBD"
    description: "Gamme complète d'huiles CBD"
  }) {
    id
    name
    description
  }
}
```

---

## 🚚 FOURNISSEURS

### 1. Liste des Fournisseurs (avec pagination automatique)
```graphql
query GetSuppliers($first: Int, $page: Int) {
  suppliers(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
      id
      name
      email
      phone
      products {
        id
        name
        price
      }
    }
  }
}
```

### 2. Fournisseur Spécifique
```graphql
query GetSupplier {
  supplier(id: "1") {
    id
    name
    email
    phone
    products {
      id
      name
      price
      stock
    }
  }
}
```

### 3. Créer un Fournisseur
```graphql
mutation CreateSupplier {
  createSupplier(
    name: "Green Lab"
    email: "contact@greenlab.com"
    phone: "+33123456789"
  ) {
    id
    name
    email
    phone
  }
}
```

---

## 🛒 PANIER & COMMANDES

### 1. Ajouter au Panier
```graphql
mutation AddToCart {
  addToCart(product_id: "1", quantity: 2) {
    id
    user_id
    product {
      id
      name
      price
    }
    quantity
    unit_price
  }
}
```

### 2. Voir le Panier
```graphql
query GetCart {
  cart {
    id
    product {
      id
      name
      price
      images
    }
    quantity
    unit_price
  }
}
```

### 3. Finaliser Commande (Checkout)
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
    user {
      name
      email
    }
    created_at
  }
}
```

### 4. Mes Commandes (Utilisateur) - avec pagination automatique
```graphql
query MyOrders($first: Int, $page: Int) {
  myOrders(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
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
      created_at
    }
  }
}
```

### 5. Toutes les Commandes (Admin uniquement) - avec pagination automatique
```graphql
query AllOrders($first: Int, $page: Int) {
  orders(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
      id
      total
      status
      user {
        id
        name
        email
      }
      products {
        id
        name
        pivot {
          quantity
          unit_price
        }
      }
      created_at
    }
  }
}
```

### 6. Modifier Statut Commande (Admin)
```graphql
mutation UpdateOrderStatus {
  updateOrderStatus(input: {
    id: "1"
    status: "shipped"
  }) {
    id
    status
    updated_at
  }
}
```

---

## � ARRIVAGES CBD

### 1. Liste des Arrivages (avec pagination automatique)
```graphql
query GetArrivals($first: Int, $page: Int) {
  arrivals(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
    }
    data {
      id
      amount
      status
      created_at
      updated_at
      products {
        id
        quantity
        unit_price
        product {
          id
          name
          price
          stock
        }
      }
    }
  }
}
```

### 2. Détail d'un Arrivage Spécifique
```graphql
query GetArrivalDetail($arrivalId: ID!) {
  arrival(arrival_id: $arrivalId) {
    id
    amount
    status
    created_at
    updated_at
    products {
      id
      quantity
      unit_price
      product {
        id
        name
        description
        price
        stock
        active
        category {
          id
          name
        }
        supplier {
          id
          name
        }
      }
    }
  }
}
```

**Variables :**
```json
{
  "arrivalId": "1"
}
```

### 3. Valider un Arrivage (Admin uniquement)
```graphql
mutation ValidateArrival($arrivalId: ID!) {
  validateArrival(arrival_id: $arrivalId) {
    id
    amount
    status
    updated_at
    products {
      id
      quantity
      unit_price
      product {
        id
        name
        stock
      }
    }
  }
}
```

**Variables :**
```json
{
  "arrivalId": "1"
}
```

**Réponse Success :**
```json
{
  "data": {
    "validateArrival": {
      "id": "1",
      "amount": 1250.50,
      "status": "validated",
      "updated_at": "2025-07-30 14:30:25",
      "products": [
        {
          "id": "1",
          "quantity": 50,
          "unit_price": 25.01,
          "product": {
            "id": "1",
            "name": "CBD Oil Premium",
            "stock": 150
          }
        }
      ]
    }
  }
}
```

### 4. Créer un Arrivage (Admin uniquement)
```graphql
mutation CreateArrival {
  createArrival(input: {
    amount: 1500.75
    status: "pending"
    products: [
      {
        product_id: 1
        quantity: 100
        unit_price: 15.00
      },
      {
        product_id: 2
        quantity: 25
        unit_price: 0.75
      }
    ]
  }) {
    id
    amount
    status
    created_at
    products {
      id
      quantity
      unit_price
      product {
        id
        name
      }
    }
  }
}
```

### 5. Modifier un Arrivage (Admin uniquement)
```graphql
mutation UpdateArrival {
  updateArrival(arrival_id: "1", input: {
    amount: 1600.00
    status: "pending"
  }) {
    id
    amount
    status
    updated_at
  }
}
```

### 6. Supprimer un Arrivage (Admin uniquement)
```graphql
mutation DeleteArrival {
  deleteArrival(arrival_id: "1") {
    id
    status
  }
}
```

### 📋 Statuts d'Arrivage Possibles
- **"pending"** - En attente de validation
- **"validated"** - Validé (met automatiquement à jour les stocks des produits)

### ⚠️ Points Importants
- **Validation automatique des stocks** : Quand un arrivage passe au statut "validated", les quantités des produits sont automatiquement ajoutées aux stocks
- **Permissions requises** : Seuls les administrateurs peuvent créer, modifier, valider ou supprimer des arrivages
- **Calcul automatique** : Le montant total est calculé automatiquement selon les produits et quantités

---

## �👤 GESTION UTILISATEURS

### 1. Profil Utilisateur
```graphql
query Me {
  me {
    id
    name
    email
    avatar
    is_admin
    is_active
    email_verified_at
  }
}
```

### 2. Modifier Profil
```graphql
mutation UpdateProfile {
  updateUser(input: {
    name: "John Doe Updated"
    email: "john.new@example.com"
  }) {
    id
    name
    email
    updated_at
  }
}
```

### 3. Changer Mot de Passe
```graphql
mutation ChangePassword {
  changePassword(
    current_password: "oldpassword"
    new_password: "newpassword"
    new_password_confirmation: "newpassword"
  ) {
    success
    message
  }
}
```

---

## ⚠️ CODES D'ERREUR STANDARDISÉS

### Codes d'Erreur HTTP
- **200** - Succès
- **400** - Requête invalide
- **401** - Non authentifié
- **403** - Accès refusé
- **404** - Ressource introuvable
- **422** - Erreur de validation
- **500** - Erreur serveur interne

### Types d'Erreurs GraphQL
```json
{
  "errors": [
    {
      "message": "Acces refuse",
      "extensions": {
        "category": "authentication"
      }
    }
  ]
}
```

### Messages d'Erreur Courants
- **"Utilisateur non authentifie"** - Token JWT manquant/invalide
- **"Acces refuse"** - Permissions insuffisantes
- **"Ressource introuvable"** - ID inexistant
- **"Erreur de validation"** - Données invalides
- **"Erreur interne"** - Problème serveur

---

## 🔍 EXEMPLES D'UTILISATION FRONT-END

### JavaScript/Axios
```javascript
// Configuration base
const API_URL = 'http://127.0.0.1:8000/graphql';
const token = localStorage.getItem('jwt_token');

const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  ...(token && { 'Authorization': `Bearer ${token}` })
};

// Exemple requête avec pagination
const getProducts = async (page = 1, perPage = 10) => {
  const query = `
    query GetProducts($first: Int, $page: Int) {
      products(first: $first, page: $page) {
        paginatorInfo {
          currentPage
          hasMorePages
          total
          perPage
          lastPage
        }
        data {
          id
          name
          price
          stock
          images
        }
      }
    }
  `;
  
  try {
    const response = await axios.post(API_URL, { 
      query, 
      variables: { first: perPage, page } 
    }, { headers });
    return response.data.data.products;
  } catch (error) {
    console.error('Erreur:', error.response.data.errors);
    throw error;
  }
};

// Exemple validation d'arrivage (Admin)
const validateArrival = async (arrivalId) => {
  const mutation = `
    mutation ValidateArrival($arrivalId: ID!) {
      validateArrival(arrival_id: $arrivalId) {
        id
        amount
        status
        updated_at
        products {
          id
          quantity
          product {
            id
            name
            stock
          }
        }
      }
    }
  `;
  
  try {
    const response = await axios.post(API_URL, { 
      query: mutation, 
      variables: { arrivalId } 
    }, { headers });
    return response.data.data.validateArrival;
  } catch (error) {
    console.error('Erreur validation arrivage:', error.response.data.errors);
    throw error;
  }
};
```

### React Hook Exemple avec Pagination
```javascript
import { useState, useEffect } from 'react';

const useProducts = (page = 1, perPage = 10) => {
  const [products, setProducts] = useState([]);
  const [paginatorInfo, setPaginatorInfo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const result = await getProducts(page, perPage);
        setProducts(result.data);
        setPaginatorInfo(result.paginatorInfo);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, [page, perPage]);

  return { 
    products, 
    paginatorInfo, 
    loading, 
    error,
    hasNextPage: paginatorInfo?.hasMorePages,
    currentPage: paginatorInfo?.currentPage,
    total: paginatorInfo?.total
  };
};

// Hook pour la validation d'arrivages (Admin)
const useArrivalValidation = () => {
  const [validating, setValidating] = useState(false);
  const [error, setError] = useState(null);

  const validateArrival = async (arrivalId) => {
    setValidating(true);
    setError(null);
    
    try {
      const result = await validateArrival(arrivalId);
      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setValidating(false);
    }
  };

  return { validateArrival, validating, error };
};
```

---

## 🛠️ SCHÉMA GRAPHQL COMPLET

### Types Principaux
```graphql
type User {
  id: ID!
  name: String!
  email: String!
  avatar: String
  is_admin: Boolean
  is_active: Boolean
  email_verified_at: String
}

type ProductCBD {
  id: ID!
  name: String!
  description: String
  price: Float!
  images: [String]
  stock: Int!
  analysis_file: String
  analysis_file_url: String
  category_id: Int
  categories: [Category!]!
  suppliers: [Supplier!]!
  created_at: String
  updated_at: String
}

type Category {
  id: ID!
  name: String!
  description: String
  products: [ProductCBD!]!
  created_at: String
}

type Supplier {
  id: ID!
  name: String!
  email: String
  phone: String
  products: [ProductCBD!]!
}

type Order {
  id: ID!
  user: User!
  total: Float!
  status: String!
  products: [OrderProduct!]!
  created_at: DateTime
  updated_at: DateTime
}

type Cart {
  id: ID!
  user_id: ID!
  product: ProductCBD!
  quantity: Int!
  unit_price: Float!
}

type CbdArrival {
  id: ID!
  amount: Float!
  status: String!
  products: [ArrivalProductCbd]
  created_at: DateTime
  updated_at: DateTime
}

type ArrivalProductCbd {
  id: ID!
  arrival_id: ID!
  product_id: ID!
  quantity: Int!
  unit_price: Float!
  product: ProductCBD
}
```

### Statuts de Commande Possibles
- **"pending"** - En attente
- **"confirmed"** - Confirmée
- **"processing"** - En traitement
- **"shipped"** - Expédiée
- **"delivered"** - Livrée
- **"cancelled"** - Annulée

### Statuts d'Arrivage Possibles
- **"pending"** - En attente de validation
- **"validated"** - Validé (stocks mis à jour automatiquement)

---

## 📞 SUPPORT & CONTACT

Pour toute question technique concernant l'API :
- **Documentation technique** : Ce fichier
- **Tests** : Utiliser GraphQL Playground à `http://127.0.0.1:8000/graphql-playground`
- **Validation** : Toutes les requêtes sont validées côté serveur

---

*Documentation générée le 30 juillet 2025*  
*API Version: 1.0 - Laravel 12 + Lighthouse GraphQL*
