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
mutation Login($email: String!, $password: String!) {
  login(email: $email, password: $password) {
    access_token
    token_type
    expires_in
    user {
      id
      name
      email
      avatar
      is_admin
      is_active
      email_verified_at
    }
  }
}
```

**Variables :**
```json
{
  "email": "admin@example.com",
  "password": "password"
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
        "avatar": null,
        "is_admin": true,
        "is_active": true,
        "email_verified_at": "2025-08-18T10:00:00.000000Z"
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
mutation Register(
  $name: String!
  $email: String!
  $password: String!
  $password_confirmation: String!
  $avatar: String
) {
  register(
    name: $name
    email: $email
    password: $password
    password_confirmation: $password_confirmation
    avatar: $avatar
  ) {
    access_token
    token_type
    expires_in
    user {
      id
      name
      email
      avatar
      is_admin
      is_active
      email_verified_at
    }
  }
}
```

**Variables :**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "avatar": "https://example.com/avatar.jpg"
}
```

### 4. Vérification Email
```graphql
mutation VerifyEmail($token: String!) {
  verifyEmail(token: $token) {
    success
    message
  }
}
```

### 5. Renvoyer Email de Vérification
```graphql
mutation ResendVerificationEmail($email: String!) {
  resendVerificationEmail(email: $email) {
    success
    message
  }
}
```

---

## 📦 PRODUITS CBD

### 1. Liste des Produits (avec pagination automatique)
```graphql
query GetProductsCBD($first: Int = 20, $page: Int, $name: String, $category_id: ID) {
  productsCBD(first: $first, page: $page, name: $name, category_id: $category_id) {
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
      image_urls
      image_metadata
      analysis_file
      analysis_file_url
      analysis_file_original_name
      analysis_file_size
      analysis_file_mime_type
      category_id
      category {
        id
        name
      }
      categories {
        id
        name
      }
      created_at
      updated_at
    }
  }
}
```

**Variables d'exemple :**
```json
{
  "first": 10,
  "page": 1,
  "name": "CBD",
  "category_id": "1"
}
```

### 2. Produit Spécifique
```graphql
query GetProductCBD($id: ID!) {
  productCBD(id: $id) {
    id
    name
    description
    price
    stock
    images
    image_urls
    image_metadata
    analysis_file
    analysis_file_url
    analysis_file_original_name
    analysis_file_size
    analysis_file_mime_type
    category_id
    category {
      id
      name
    }
    categories {
      id
      name
    }
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1"
}
```

### 3. Créer un Produit
```graphql
mutation CreateProductCBD($input: CreateProductCBDInput!) {
  createProductCBD(input: $input) {
    id
    name
    description
    price
    stock
    images
    image_urls
    image_metadata
    analysis_file
    analysis_file_url
    analysis_file_original_name
    analysis_file_size
    analysis_file_mime_type
    category_id
    category {
      id
      name
    }
    categories {
      id
      name
    }
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "input": {
    "name": "CBD Oil Premium",
    "description": "Huile CBD premium avec certificat d'analyse",
    "price": 29.99,
    "stock": 100,
    "category_id": 1,
    "category_ids": [1, 2]
  }
}
```

### 4. Modifier un Produit
```graphql
mutation UpdateProductCBD($id: ID!, $input: UpdateProductCBDInput!) {
  updateProductCBD(id: $id, input: $input) {
    id
    name
    description
    price
    stock
    images
    image_urls
    image_metadata
    analysis_file
    analysis_file_url
    analysis_file_original_name
    analysis_file_size
    analysis_file_mime_type
    category_id
    category {
      id
      name
    }
    categories {
      id
      name
    }
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1",
  "input": {
    "name": "CBD Oil Premium Updated",
    "price": 34.99,
    "stock": 85
  }
}
```

### 5. Supprimer un Produit
```graphql
mutation DeleteProductCBD($id: ID!) {
  deleteProductCBD(id: $id) {
    success
    message
  }
}
```

**Variables :**
```json
{
  "id": "1"
}
```

### 6. Upload d'Images Produit
```graphql
mutation UploadProductImages($productId: ID!, $images: [Upload!]!) {
  uploadProductImages(productId: $productId, images: $images) {
    success
    message
    url
    path
  }
}
```

---

## 🏷️ CATÉGORIES

### 1. Liste des Catégories (avec pagination automatique)
```graphql
query GetCategories($first: Int = 20, $page: Int) {
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
        stock
      }
      created_at
      updated_at
    }
  }
}
```

### 2. Catégorie Spécifique
```graphql
query GetCategory($id: ID!) {
  category(id: $id) {
    id
    name
    description
    products {
      id
      name
      description
      price
      stock
      images
      image_urls
      image_metadata
      analysis_file
      analysis_file_url
      analysis_file_original_name
      analysis_file_size
      analysis_file_mime_type
      category_id
    }
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1"
}
```

### 3. Créer une Catégorie
```graphql
mutation CreateCategory($input: CreateCategoryInput!) {
  createCategory(input: $input) {
    id
    name
    description
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "input": {
    "name": "Huiles CBD",
    "description": "Gamme complète d'huiles CBD"
  }
}
```

### 4. Modifier une Catégorie
```graphql
mutation UpdateCategory($id: ID!, $input: UpdateCategoryInput!) {
  updateCategory(id: $id, input: $input) {
    id
    name
    description
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1",
  "input": {
    "name": "Huiles CBD Premium",
    "description": "Gamme premium d'huiles CBD"
  }
}
```

### 5. Supprimer une Catégorie
```graphql
mutation DeleteCategory($id: ID!) {
  deleteCategory(id: $id) {
    success
    message
  }
}
```

### 6. Attacher Catégorie à Produit
```graphql
mutation AttachCategoryToProduct($category_id: ID!, $product_id: ID!) {
  attachCategoryToProduct(category_id: $category_id, product_id: $product_id) {
    id
    name
    products {
      id
      name
    }
  }
}
```

### 7. Détacher Catégorie d'un Produit
```graphql
mutation DetachCategoryFromProduct($category_id: ID!, $product_id: ID!) {
  detachCategoryFromProduct(category_id: $category_id, product_id: $product_id) {
    id
    name
    products {
      id
      name
    }
  }
}
```

---

## 🚚 FOURNISSEURS

### 1. Liste des Fournisseurs (avec pagination automatique)
```graphql
query GetSuppliers($first: Int = 20, $page: Int) {
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
      address
      website
      contact_person
      description
      products {
        id
        name
        price
        stock
      }
      created_at
      updated_at
    }
  }
}
```

### 2. Fournisseur Spécifique
```graphql
query GetSupplier($id: ID!) {
  supplier(id: $id) {
    id
    name
    email
    phone
    address
    website
    contact_person
    description
    products {
      id
      name
      description
      price
      stock
      images
    }
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1"
}
```

### 3. Créer un Fournisseur
```graphql
mutation CreateSupplier(
  $name: String!
  $email: String
  $phone: String
  $address: String
  $website: String
  $contact_person: String
  $description: String
) {
  createSupplier(
    name: $name
    email: $email
    phone: $phone
    address: $address
    website: $website
    contact_person: $contact_person
    description: $description
  ) {
    id
    name
    email
    phone
    address
    website
    contact_person
    description
    created_at
  }
}
```

**Variables :**
```json
{
  "name": "Green Lab",
  "email": "contact@greenlab.com",
  "phone": "+33123456789",
  "address": "123 Rue de la Paix, Paris",
  "website": "https://greenlab.com",
  "contact_person": "Jean Dupont",
  "description": "Fournisseur premium de produits CBD"
}
```

### 4. Modifier un Fournisseur
```graphql
mutation UpdateSupplier($id: ID!, $input: UpdateSupplierInput!) {
  updateSupplier(id: $id, input: $input) {
    id
    name
    email
    phone
    address
    website
    contact_person
    description
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1",
  "input": {
    "name": "Green Lab Premium",
    "email": "contact@greenlab-premium.com",
    "phone": "+33123456790"
  }
}
```

### 5. Supprimer un Fournisseur
```graphql
mutation DeleteSupplier($id: ID!) {
  deleteSupplier(id: $id) {
    success
    message
  }
}
```

### 6. Attacher Fournisseur à Produit
```graphql
mutation AttachSupplierToProduct($supplier_id: ID!, $product_id: ID!) {
  attachSupplierToProduct(supplier_id: $supplier_id, product_id: $product_id) {
    id
    name
    products {
      id
      name
    }
  }
}
```

### 7. Détacher Fournisseur d'un Produit
```graphql
mutation DetachSupplierFromProduct($supplier_id: ID!, $product_id: ID!) {
  detachSupplierFromProduct(supplier_id: $supplier_id, product_id: $product_id) {
    id
    name
    products {
      id
      name
    }
  }
}
```

---

## 🛒 PANIER & COMMANDES

### 1. Ajouter au Panier
```graphql
mutation AddToCart($input: AddToCartInput!) {
  addToCart(input: $input) {
    id
    user_id
    product_id
    quantity
    product {
      id
      name
      price
      images
      stock
    }
    created_at
    updated_at
  }
}
```

**Variables :**
```json
{
  "input": {
    "product_id": "1",
    "quantity": 2
  }
}
```

### 2. Voir mon Panier
```graphql
query MyCart {
  myCart {
    id
    user_id
    product_id
    quantity
    product {
      id
      name
      description
      price
      images
      stock
  image_urls
  image_metadata
  analysis_file
  analysis_file_url
  analysis_file_original_name
  analysis_file_size
  analysis_file_mime_type
      categories {
        id
        name
      }
    }
    created_at
    updated_at
  }
}
```

### 3. Total du Panier
```graphql
query CartTotal {
  cartTotal {
    total
    itemCount
  }
}
```

### 4. Modifier Quantité dans le Panier
```graphql
mutation UpdateCartItem($id: ID!, $input: UpdateCartItemInput!) {
  updateCartItem(id: $id, input: $input) {
    id
    quantity
    product {
      id
      name
      price
    }
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1",
  "input": {
    "quantity": 3
  }
}
```

### 5. Supprimer du Panier
```graphql
mutation RemoveFromCart($id: ID!) {
  removeFromCart(id: $id) {
    success
    message
  }
}
```

**Variables :**
```json
{
  "id": "1"
}
```

### 6. Vider le Panier
```graphql
mutation ClearCart {
  clearCart {
    success
    message
  }
}
```

### 3. Finaliser Commande (Checkout)
```graphql
mutation Checkout {
  checkout {
    id
    user_id
    total
    status
    created_at
    updated_at
    
    # Relations
    user {
      id
      name
      email
    }
    
    products {
      id
      name
      description
      price
      images
  image_urls
  image_metadata
  analysis_file
  analysis_file_url
  analysis_file_original_name
  analysis_file_size
  analysis_file_mime_type
      stock
      
      categories {
        id
        name
      }
      
      # Données de la table pivot order_product
      pivot {
        quantity
        unit_price
        created_at
        updated_at
      }
    }
    
    # Propriétés calculées
    total_items
    product_count
    formatted_status
  }
}
```

### 4. Mes Commandes (Utilisateur) - avec pagination automatique
```graphql
query MyOrders($first: Int = 10, $page: Int = 1) {
  myOrders(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
      count
    }
    data {
      id
      user_id
      total
      status
      created_at
      updated_at
      
      user {
        id
        name
        email
      }
      
      # Propriétés calculées
      total_items
      product_count
      formatted_status
    }
  }
}
```

### 5. Détails Complets d'une Commande
```graphql
query GetOrderDetails($id: ID!) {
  orderDetails(id: $id) {
    id
    user_id
    total
    status
    created_at
    updated_at
    
    user {
      id
      name
      email
    }
    
    # Produits via la relation many-to-many
    products {
      id
      name
      description
      price
      images
  image_urls
  image_metadata
  analysis_file
  analysis_file_url
  analysis_file_original_name
  analysis_file_size
  analysis_file_mime_type
      stock
      
      categories {
        id
        name
      }
      
      # Données de la table pivot order_product
      pivot {
        quantity
        unit_price
        created_at
        updated_at
      }
    }
    
    # Accès direct aux enregistrements de la table pivot (optionnel)
    orderProducts {
      id
      quantity
      unit_price
      created_at
      updated_at
      
      product {
        id
        name
        description
        price
        images
        
        categories {
          id
          name
        }
      }
    }
    
    # Propriétés calculées
    total_items
    product_count
    formatted_status
  }
}
```

**Variables :**
```json
{
  "id": "123"
}
```

### 6. Toutes les Commandes (Admin uniquement) - avec pagination automatique
```graphql
query AllOrders($first: Int = 15, $page: Int = 1) {
  orders(first: $first, page: $page) {
    paginatorInfo {
      currentPage
      hasMorePages
      total
      perPage
      lastPage
      count
    }
    data {
      id
      user_id
      total
      status
      created_at
      updated_at
      
      user {
        id
        name
        email
      }
      
      # Propriétés calculées
      total_items
      product_count
      formatted_status
    }
  }
}
```

### 7. Statistiques d'une Commande
```graphql
query GetOrderStats($id: ID!) {
  orderStats(id: $id) {
    order_id
    total_items
    product_count
    total_amount
    average_item_price
    created_at
    status
  }
}
```

### 8. Annuler une Commande
```graphql
mutation CancelOrder($id: ID!) {
  cancelOrder(id: $id)
}
```

**Variables :**
```json
{
  "id": "123"
}
```

**Réponse :**
```json
{
  "data": {
    "cancelOrder": true
  }
}
```

### 9. Modifier Statut Commande (Admin)
```graphql
mutation UpdateOrderStatus($input: UpdateOrderStatusInput!) {
  updateOrderStatus(input: $input) {
    id
    user_id
    total
    status
    created_at
    updated_at
    
    user {
      name
      email
    }
    
    products {
      name
      pivot {
        quantity
        unit_price
      }
    }
    
    formatted_status
  }
}
```

**Variables :**
```json
{
  "input": {
    "id": "123",
    "status": "shipped"
  }
}
```

**Statuts valides :**
- `pending` : En attente
- `processing` : En cours de traitement  
- `shipped` : Expédiée
- `delivered` : Livrée
- `cancelled` : Annulée
- `refunded` : Remboursée

**Transitions de statut autorisées :**
- `pending` → `processing`, `cancelled`
- `processing` → `shipped`, `cancelled`
- `shipped` → `delivered`
- `delivered` → `refunded`
- `cancelled` → (aucune transition)
- `refunded` → (aucune transition)

---

## 📦 ARRIVAGES CBD

### 1. Liste des Arrivages (avec pagination automatique)
```graphql
query GetArrivals($first: Int = 15, $page: Int) {
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
        arrival_id
        product_id
        quantity
        unit_price
        product {
          id
          name
          price
          stock
          images
        }
      }
    }
  }
}
```

### 2. Détail d'un Arrivage Spécifique
```graphql
query GetArrival($arrival_id: ID!) {
  arrival(arrival_id: $arrival_id) {
    id
    amount
    status
    created_at
    updated_at
    products {
      id
      arrival_id
      product_id
      quantity
      unit_price
      product {
        id
        name
        description
        price
        stock
        images
        image_metadata
  analysis_file
  analysis_file_url
        analysis_file_original_name
        analysis_file_size
  analysis_file_mime_type
        categories {
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
  "arrival_id": "1"
}
```

### 3. Créer un Arrivage (Admin uniquement)
```graphql
mutation CreateArrival($input: CreateArrivalInput!) {
  createArrival(input: $input) {
    id
    amount
    status
    created_at
    products {
      id
      product_id
      quantity
      unit_price
      product {
        id
        name
        price
      }
    }
  }
}
```

**Variables :**
```json
{
  "input": {
    "amount": 1500.75,
    "status": "pending",
    "products": [
      {
        "product_id": "1",
        "quantity": 100,
        "unit_price": 15.00
      },
      {
        "product_id": "2",
        "quantity": 25,
        "unit_price": 0.75
      }
    ]
  }
}
```

### 4. Modifier un Arrivage (Admin uniquement)
```graphql
mutation UpdateArrival($arrival_id: ID!, $input: UpdateArrivalInput!) {
  updateArrival(arrival_id: $arrival_id, input: $input) {
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
      }
    }
  }
}
```

**Variables :**
```json
{
  "arrival_id": "1",
  "input": {
    "amount": 1600.00,
    "status": "pending"
  }
}
```

### 5. Valider un Arrivage (Admin uniquement)
```graphql
mutation ValidateArrival($arrival_id: ID!) {
  validateArrival(arrival_id: $arrival_id) {
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
  "arrival_id": "1"
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
      "updated_at": "2025-08-18 14:30:25",
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

### 6. Supprimer un Arrivage (Admin uniquement)
```graphql
mutation DeleteArrival($arrival_id: ID!) {
  deleteArrival(arrival_id: $arrival_id) {
    id
    status
  }
}
```

**Variables :**
```json
{
  "arrival_id": "1"
}
```

### 📋 Statuts d'Arrivage Possibles
- **"pending"** - En attente de validation
- **"validated"** - Validé (met automatiquement à jour les stocks des produits)

### ⚠️ Points Importants
- **Validation automatique des stocks** : Quand un arrivage passe au statut "validated", les quantités des produits sont automatiquement ajoutées aux stocks
- **Permissions requises** : Seuls les administrateurs peuvent créer, modifier, valider ou supprimer des arrivages
- **Calcul automatique** : Le montant total peut être calculé automatiquement selon les produits et quantités

---

## 👤 GESTION UTILISATEURS

### 1. Profil Utilisateur Connecté
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

### 2. Liste des Utilisateurs (Admin uniquement)
```graphql
query GetUsers($first: Int = 15, $page: Int) {
  users(first: $first, page: $page) {
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
      avatar
      is_admin
      is_active
      email_verified_at
    }
  }
}
```

### 3. Utilisateur Spécifique (Admin uniquement)
```graphql
query GetUser($id: ID!) {
  user(id: $id) {
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

**Variables :**
```json
{
  "id": "1"
}
```

### 4. Modifier Profil Personnel
```graphql
mutation UpdateProfile(
  $id: ID!
  $name: String
  $email: String
  $avatar: String
) {
  updateProfile(
    id: $id
    name: $name
    email: $email
    avatar: $avatar
  ) {
    id
    name
    email
    avatar
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "1",
  "name": "John Doe Updated",
  "email": "john.new@example.com",
  "avatar": "https://example.com/new-avatar.jpg"
}
```

### 5. Modifier Utilisateur (Admin uniquement)
```graphql
mutation UpdateUser(
  $id: ID!
  $name: String
  $email: String
  $is_active: Boolean
  $is_admin: Boolean
  $password: String
  $password_confirmation: String
) {
  updateUser(
    id: $id
    name: $name
    email: $email
    is_active: $is_active
    is_admin: $is_admin
    password: $password
    password_confirmation: $password_confirmation
  ) {
    id
    name
    email
    is_admin
    is_active
    updated_at
  }
}
```

**Variables :**
```json
{
  "id": "2",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "is_active": true,
  "is_admin": false
}
```

### 6. Changer Mot de Passe
```graphql
mutation ChangePassword(
  $current_password: String!
  $new_password: String!
) {
  changePassword(
    current_password: $current_password
    new_password: $new_password
  ) {
    success
    message
  }
}
```

**Variables :**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123"
}
```

### 7. Supprimer Utilisateur (Admin uniquement)
```graphql
mutation DeleteUser($id: ID!) {
  deleteUser(id: $id) {
    success
    message
  }
}
```

**Variables :**
```json
{
  "id": "2"
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
- **"Utilisateur non authentifié"** - Token JWT manquant/invalide
- **"Accès refusé"** - Permissions insuffisantes
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

// Exemple requête avec pagination (liste de produits CBD)
const getProducts = async (page = 1, perPage = 10) => {
  const query = `
    query GetProductsCBD($first: Int, $page: Int) {
      productsCBD(first: $first, page: $page) {
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
    // La requête racine correcte est productsCBD (schéma GraphQL)
    return response.data.data.productsCBD;
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
  phone: String
  address: String
  birth_date: Date
  avatar: String
  avatar_original_name: String
  avatar_size: Int
  is_admin: Boolean
  is_active: Boolean
  email_verified_at: DateTime
  created_at: DateTime
  updated_at: DateTime
}

type ProductCBD {
  id: ID!
  name: String!
  description: String
  price: Float!
  images: [String!]
  image_urls: [String!]
  image_metadata: JSON
  stock: Int!
  analysis_file: String
  analysis_file_url: String
  analysis_file_original_name: String
  analysis_file_size: Int
  analysis_file_mime_type: String
  category_id: ID
  category: Category @belongsTo
  categories: [Category!]! @belongsToMany
  created_at: DateTime
  updated_at: DateTime
}

type Category {
  id: ID!
  name: String!
  description: String
  products: [ProductCBD!]! @hasMany
  allProducts: [ProductCBD!]! @belongsToMany
  created_at: DateTime
  updated_at: DateTime
}

type Order {
  id: ID!
  user_id: ID!
  user: User!
  total: Float!
  status: String!
  created_at: DateTime
  updated_at: DateTime
  
  # Relations avec les produits
  products: [ProductCBD!]! @belongsToMany(relation: "products")
  orderProducts: [OrderProduct!]!
  
  # Accesseurs calculés
  total_items: Int!
  product_count: Int!
  formatted_status: String!
}

type OrderProduct {
  id: ID!
  order_id: ID!
  product_id: ID!
  quantity: Int!
  unit_price: Float!
  created_at: DateTime
  updated_at: DateTime
  
  # Relations
  order: Order @belongsTo
  product: ProductCBD @belongsTo
}

type Cart {
  id: ID!
  user_id: ID!
  product_id: ID!
  quantity: Int!
  user: User
  product: ProductCBD
  created_at: DateTime
  updated_at: DateTime
}

type CartSummary {
  total: Float!
  itemCount: Int!
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
  created_at: DateTime
  updated_at: DateTime
}
```

### Queries Disponibles
```graphql
# Authentification
me: User

# Produits CBD
productsCBD(first: Int, page: Int, name: String, category_id: ID): [ProductCBD!]!
productCBD(id: ID!): ProductCBD

# Catégories
categories(first: Int, page: Int): [Category!]!
category(id: ID!): Category

# Fournisseurs
suppliers(first: Int, page: Int): [Supplier!]!
supplier(id: ID!): Supplier

# Panier
myCart: [Cart!]!
cartTotal: CartSummary

# Commandes
orders(first: Int, page: Int): [Order!]!          # Admin uniquement
myOrders(first: Int, page: Int): [Order!]!        # Utilisateur connecté
order(id: ID!): Order
orderDetails(id: ID!): Order
orderStats(id: ID!): OrderStats

# Arrivages
arrivals(first: Int, page: Int): [CbdArrival!]!
arrival(arrival_id: ID!): CbdArrival

# Utilisateurs (Admin uniquement)
users(first: Int, page: Int): [User!]!
user(id: ID!): User
```

> Note: Tous les champs avec paramètres `first` et `page` utilisent la pagination Lighthouse et renvoient un objet avec la forme `{ paginatorInfo, data }`.

### Mutations Disponibles
```graphql
# Authentification
login(email: String!, password: String!): AuthPayload
logout: LogoutResponse
register(name: String!, email: String!, password: String!, password_confirmation: String!, avatar: String): AuthPayload

# Produits CBD
createProductCBD(input: CreateProductCBDInput!): ProductCBD!
updateProductCBD(id: ID!, input: UpdateProductCBDInput!): ProductCBD!
deleteProductCBD(id: ID!): DeleteResponse!
uploadProductImages(productId: ID!, images: [Upload!]!): [FileUploadResponse!]!

# Catégories
createCategory(input: CreateCategoryInput!): Category
updateCategory(id: ID!, input: UpdateCategoryInput!): Category
deleteCategory(id: ID!): DeleteResponse
attachCategoryToProduct(category_id: ID!, product_id: ID!): Category
detachCategoryFromProduct(category_id: ID!, product_id: ID!): Category

# Fournisseurs
createSupplier(...): Supplier
updateSupplier(id: ID!, input: UpdateSupplierInput!): Supplier
deleteSupplier(id: ID!): DeleteResponse
attachSupplierToProduct(supplier_id: ID!, product_id: ID!): Supplier
detachSupplierFromProduct(supplier_id: ID!, product_id: ID!): Supplier

# Panier
addToCart(input: AddToCartInput!): Cart
updateCartItem(id: ID!, input: UpdateCartItemInput!): Cart
removeFromCart(id: ID!): DeleteResponse
clearCart: DeleteResponse

# Commandes
checkout: Order
cancelOrder(id: ID!): Boolean
updateOrderStatus(input: UpdateOrderStatusInput!): Order

# Arrivages (Admin uniquement)
createArrival(input: CreateArrivalInput!): CbdArrival
updateArrival(arrival_id: ID!, input: UpdateArrivalInput!): CbdArrival
validateArrival(arrival_id: ID!): CbdArrival
deleteArrival(arrival_id: ID!): CbdArrival

# Utilisateurs
updateProfile(id: ID!, name: String, email: String, avatar: String): User
updateUser(id: ID!, ...): User    # Admin uniquement
deleteUser(id: ID!): DeleteResponse    # Admin uniquement
changePassword(current_password: String!, new_password: String!): ChangePasswordResponse
```

### Statuts de Commande Possibles
- **"pending"** - En attente
- **"processing"** - En cours de traitement
- **"shipped"** - Expédiée
- **"delivered"** - Livrée
- **"cancelled"** - Annulée
- **"refunded"** - Remboursée


### Statuts d'Arrivage Possibles
- **"pending"** - En attente de validation
- **"validated"** - Validé (stocks mis à jour automatiquement)

---

## 🌐 EXEMPLES D'INTÉGRATION FRONTEND MODERNE

### TypeScript - Types pour les Commandes
```typescript
// Types pour le système Order optimisé
export interface Order {
  id: string;
  user_id: string;
  total: number;
  status: OrderStatus;
  created_at: string;
  updated_at: string;
  
  // Relations
  user?: User;
  products?: ProductCBD[];
  orderProducts?: OrderProduct[];
  
  // Propriétés calculées
  total_items?: number;
  product_count?: number;
  formatted_status?: string;
}

export interface OrderProduct {
  id: string;
  order_id: string;
  product_id: string;
  quantity: number;
  unit_price: number;
  created_at: string;
  updated_at: string;
  
  // Relations
  order?: Order;
  product?: ProductCBD;
}

export interface ProductCBD {
  id: string;
  name: string;
  description?: string;
  price: number;
  images?: string[];
  image_urls?: string[];
  image_metadata?: any;
  analysis_file?: string;
  analysis_file_url?: string;
  analysis_file_original_name?: string;
  analysis_file_size?: number;
  analysis_file_mime_type?: string;
  stock: number;
  category_id?: string;
  category?: Category;
  categories?: Category[];
  created_at: string;
  updated_at: string;
  
  // Données pivot si récupérées via la relation many-to-many
  pivot?: {
    quantity: number;
    unit_price: number;
    created_at: string;
    updated_at: string;
  };
}

export type OrderStatus = 
  | 'pending'
  | 'processing'
  | 'shipped'
  | 'delivered'
  | 'cancelled'
  | 'refunded'


export interface PaginatorInfo {
  currentPage: number;
  lastPage: number;
  hasMorePages: boolean;
  total: number;
  count: number;
  perPage: number;
}

export interface OrdersResponse {
  data: Order[];
  paginatorInfo: PaginatorInfo;
}

// Types génériques de pagination pour d'autres ressources
export interface Paginated<T> {
  data: T[];
  paginatorInfo: PaginatorInfo;
}
```

### Vue.js + Composition API
```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import { gql } from 'graphql-tag';

// Queries GraphQL optimisées
const GET_MY_ORDERS = gql`
  query GetMyOrders($first: Int = 10, $page: Int = 1) {
    myOrders(first: $first, page: $page) {
      data {
        id
        total
        status
        created_at
        total_items
        product_count
        formatted_status
        
        user {
          name
          email
        }
      }
      
      paginatorInfo {
        currentPage
        lastPage
        hasMorePages
        total
        count
      }
    }
  }
`;

const GET_ORDER_DETAILS = gql`
  query GetOrderDetails($id: ID!) {
    orderDetails(id: $id) {
      id
      total
      status
      created_at
      formatted_status
      
      user {
        name
        email
      }
      
      products {
        id
        name
        description
        price
        images
  image_metadata
  analysis_file
  analysis_file_url
        analysis_file_original_name
        analysis_file_size
  analysis_file_mime_type
        
        categories {
          name
        }
        
        pivot {
          quantity
          unit_price
        }
      }
      
      total_items
      product_count
    }
  }
`;

const CHECKOUT = gql`
  mutation Checkout {
    checkout {
      id
      total
      status
      formatted_status
      created_at
      
      products {
        name
        pivot {
          quantity
          unit_price
        }
      }
    }
  }
`;

const CANCEL_ORDER = gql`
  mutation CancelOrder($id: ID!) {
    cancelOrder(id: $id)
  }
`;

// État réactif
const currentPage = ref(1);
const selectedOrderId = ref<string | null>(null);

// Récupération des commandes avec pagination
const { result: ordersResult, loading: ordersLoading, refetch: refetchOrders } = useQuery(
  GET_MY_ORDERS,
  () => ({
    first: 10,
    page: currentPage.value
  })
);

// Récupération des détails d'une commande
const { result: orderDetailsResult, loading: detailsLoading } = useQuery(
  GET_ORDER_DETAILS,
  () => ({ id: selectedOrderId.value }),
  () => ({ enabled: !!selectedOrderId.value })
);

// Mutation checkout
const { mutate: checkout, loading: checkoutLoading } = useMutation(CHECKOUT);

// Mutation annulation
const { mutate: cancelOrder, loading: cancelLoading } = useMutation(CANCEL_ORDER);

// Données calculées
const orders = computed(() => ordersResult.value?.myOrders?.data || []);
const paginatorInfo = computed(() => ordersResult.value?.myOrders?.paginatorInfo);
const orderDetails = computed(() => orderDetailsResult.value?.orderDetails);

// Méthodes
const handleCheckout = async () => {
  try {
    const result = await checkout();
    console.log('Commande créée:', result?.data?.checkout);
    await refetchOrders();
  } catch (error) {
    console.error('Erreur checkout:', error);
  }
};

const handleCancelOrder = async (orderId: string) => {
  try {
    await cancelOrder({ id: orderId });
    console.log('Commande annulée');
    await refetchOrders();
  } catch (error) {
    console.error('Erreur annulation:', error);
  }
};

const selectOrder = (orderId: string) => {
  selectedOrderId.value = orderId;
};

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price);
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Lifecycle
onMounted(() => {
  // Peut être utilisé pour des initialisations supplémentaires
});
</script>

<template>
  <div class="orders-container">
    <!-- Liste des commandes -->
    <div class="orders-list">
      <h2>Mes Commandes</h2>
      
      <div v-if="ordersLoading" class="loading">
        Chargement des commandes...
      </div>
      
      <div v-else-if="orders.length === 0" class="empty">
        Aucune commande trouvée
      </div>
      
      <div v-else>
        <div 
          v-for="order in orders" 
          :key="order.id"
          class="order-item"
          @click="selectOrder(order.id)"
          :class="{ active: selectedOrderId === order.id }"
        >
          <div class="order-header">
            <span class="order-id">Commande #{{ order.id }}</span>
            <span class="order-status" :class="`status-${order.status}`">
              {{ order.formatted_status }}
            </span>
          </div>
          
          <div class="order-info">
            <p><strong>Total:</strong> {{ formatPrice(order.total) }}</p>
            <p><strong>Date:</strong> {{ formatDate(order.created_at) }}</p>
            <p><strong>Articles:</strong> {{ order.total_items }} ({{ order.product_count }} produits)</p>
          </div>
          
          <div class="order-actions">
            <button 
              v-if="order.status === 'pending'"
              @click.stop="handleCancelOrder(order.id)"
              :disabled="cancelLoading"
              class="btn-cancel"
            >
              Annuler
            </button>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="paginatorInfo" class="pagination">
          <button 
            @click="currentPage--"
            :disabled="currentPage <= 1"
            class="btn-page"
          >
            Précédent
          </button>
          
          <span class="page-info">
            Page {{ paginatorInfo.currentPage }} sur {{ paginatorInfo.lastPage }}
            ({{ paginatorInfo.total }} total)
          </span>
          
          <button 
            @click="currentPage++"
            :disabled="!paginatorInfo.hasMorePages"
            class="btn-page"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>
    
    <!-- Détails de la commande sélectionnée -->
    <div v-if="selectedOrderId" class="order-details">
      <h2>Détails de la commande #{{ selectedOrderId }}</h2>
      
      <div v-if="detailsLoading" class="loading">
        Chargement des détails...
      </div>
      
      <div v-else-if="orderDetails" class="details-content">
        <div class="details-header">
          <h3>Informations générales</h3>
          <p><strong>Total:</strong> {{ formatPrice(orderDetails.total) }}</p>
          <p><strong>Statut:</strong> {{ orderDetails.formatted_status }}</p>
          <p><strong>Client:</strong> {{ orderDetails.user.name }}</p>
          <p><strong>Date:</strong> {{ formatDate(orderDetails.created_at) }}</p>
        </div>
        
        <div class="products-list">
          <h3>Produits commandés</h3>
          <div 
            v-for="product in orderDetails.products" 
            :key="product.id"
            class="product-item"
          >
            <div class="product-info">
              <h4>{{ product.name }}</h4>
              <p>{{ product.description }}</p>
              
              
              <div v-if="product.categories?.length" class="categories">
                <span 
                  v-for="category in product.categories" 
                  :key="category.id"
                  class="category-tag"
                >
                  {{ category.name }}
                </span>
              </div>
            </div>
            
            <div class="product-order-details">
              <p><strong>Quantité:</strong> {{ product.pivot.quantity }}</p>
              <p><strong>Prix unitaire:</strong> {{ formatPrice(product.pivot.unit_price) }}</p>
              <p><strong>Total ligne:</strong> {{ formatPrice(product.pivot.quantity * product.pivot.unit_price) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bouton checkout -->
  <div class="checkout-section">
    <button 
      @click="handleCheckout"
      :disabled="checkoutLoading"
      class="btn-checkout"
    >
      {{ checkoutLoading ? 'Création...' : 'Finaliser ma commande' }}
    </button>
  </div>
</template>

<style scoped>
.orders-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.order-item {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.order-item:hover {
  border-color: #4299e1;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-item.active {
  border-color: #3182ce;
  background-color: #ebf8ff;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.order-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending { background: #fed7d7; color: #c53030; }
.status-processing { background: #fef5e7; color: #dd6b20; }
.status-shipped { background: #bee3f8; color: #2b6cb0; }
.status-delivered { background: #c6f6d5; color: #38a169; }
.status-cancelled { background: #fed7d7; color: #e53e3e; }

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 16px;
  background: #f7fafc;
  border-radius: 8px;
}

.btn-page, .btn-cancel, .btn-checkout {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-page {
  background: #4299e1;
  color: white;
}

.btn-page:disabled {
  background: #cbd5e0;
  cursor: not-allowed;
}

.btn-cancel {
  background: #e53e3e;
  color: white;
}

.btn-checkout {
  background: #38a169;
  color: white;
  font-size: 16px;
  padding: 12px 24px;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #4a5568;
}

.product-specs {
  display: flex;
  gap: 8px;
  margin: 8px 0;
}

.product-specs span {
  background: #e6fffa;
  color: #00a3a3;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}

.categories {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.category-tag {
  background: #edf2f7;
  color: #2d3748;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
}
</style>
```

### Store Pinia pour la Gestion des Commandes
```typescript
// stores/orderStore.ts
import { defineStore } from 'pinia';
import { apolloClient } from '@/plugins/apollo';
import { gql } from 'graphql-tag';
import type { Order, OrdersResponse, PaginatorInfo } from '@/types/order';

export const useOrderStore = defineStore('order', {
  state: () => ({
    orders: [] as Order[],
    currentOrder: null as Order | null,
    paginatorInfo: null as PaginatorInfo | null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    // Calculer le total vérifié côté frontend
    orderTotal: (state) => {
      if (!state.currentOrder?.products) return 0;
      
      return state.currentOrder.products.reduce((total, product) => {
        return total + (product.pivot!.quantity * product.pivot!.unit_price);
      }, 0);
    },

    // Produits avec détails calculés
    orderProductsWithDetails: (state) => {
      if (!state.currentOrder?.products) return [];
      
      return state.currentOrder.products.map(product => ({
        ...product,
        lineTotal: product.pivot!.quantity * product.pivot!.unit_price,
        formattedPrice: new Intl.NumberFormat('fr-FR', {
          style: 'currency',
          currency: 'EUR'
        }).format(product.pivot!.unit_price),
        formattedLineTotal: new Intl.NumberFormat('fr-FR', {
          style: 'currency',
          currency: 'EUR'
        }).format(product.pivot!.quantity * product.pivot!.unit_price)
      }));
    },

    // Filtrer les commandes par statut
    ordersByStatus: (state) => (status: string) => {
      return state.orders.filter(order => order.status === status);
    },

    // Statistiques rapides
    ordersStats: (state) => {
      const total = state.orders.length;
      const totalAmount = state.orders.reduce((sum, order) => sum + order.total, 0);
      const statusCounts = state.orders.reduce((acc, order) => {
        acc[order.status] = (acc[order.status] || 0) + 1;
        return acc;
      }, {} as Record<string, number>);

      return {
        totalOrders: total,
        totalAmount,
        averageAmount: total > 0 ? totalAmount / total : 0,
        statusCounts
      };
    }
  },

  actions: {
    async fetchMyOrders(page: number = 1, limit: number = 10) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await apolloClient.query({
          query: gql`
            query GetMyOrders($first: Int, $page: Int) {
              myOrders(first: $first, page: $page) {
                data {
                  id
                  user_id
                  total
                  status
                  created_at
                  updated_at
                  total_items
                  product_count
                  formatted_status
                  
                  user {
                    name
                    email
                  }
                }
                
                paginatorInfo {
                  currentPage
                  lastPage
                  hasMorePages
                  total
                  count
                  perPage
                }
              }
            }
          `,
          variables: { first: limit, page },
          fetchPolicy: 'cache-first'
        });

        this.orders = data.myOrders.data;
        this.paginatorInfo = data.myOrders.paginatorInfo;
        return data.myOrders;
      } catch (error: any) {
        console.error('Erreur fetchMyOrders:', error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchOrderDetails(orderId: string) {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await apolloClient.query({
          query: gql`
            query GetOrderDetails($id: ID!) {
              orderDetails(id: $id) {
                id
                user_id
                total
                status
                created_at
                updated_at
                formatted_status
                
                user {
                  id
                  name
                  email
                }
                
                products {
                  id
                  name
                  description
                  price
                  images
                  image_metadata
                  analysis_file
                  analysis_file_url
                  analysis_file_original_name
                  analysis_file_size
                  analysis_file_mime_type
                  
                  categories {
                    id
                    name
                  }
                  
                  pivot {
                    quantity
                    unit_price
                    created_at
                    updated_at
                  }
                }
                
                total_items
                product_count
              }
            }
          `,
          variables: { id: orderId },
          fetchPolicy: 'cache-first'
        });

        this.currentOrder = data.orderDetails;
        return data.orderDetails;
      } catch (error: any) {
        console.error('Erreur fetchOrderDetails:', error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async checkout() {
      this.loading = true;
      this.error = null;

      try {
        const { data } = await apolloClient.mutate({
          mutation: gql`
            mutation Checkout {
              checkout {
                id
                user_id
                total
                status
                created_at
                formatted_status
                
                user {
                  name
                  email
                }
                
                products {
                  id
                  name
                  price
                  
                  pivot {
                    quantity
                    unit_price
                  }
                }
                
                total_items
                product_count
              }
            }
          `
        });

        const newOrder = data.checkout;
        this.orders.unshift(newOrder); // Ajouter au début de la liste
        return newOrder;
      } catch (error: any) {
        console.error('Erreur checkout:', error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async cancelOrder(orderId: string) {
      this.loading = true;
      this.error = null;

      try {
        await apolloClient.mutate({
          mutation: gql`
            mutation CancelOrder($id: ID!) {
              cancelOrder(id: $id)
            }
          `,
          variables: { id: orderId }
        });

        // Mettre à jour localement
        const orderIndex = this.orders.findIndex(o => o.id === orderId);
        if (orderIndex !== -1) {
          this.orders[orderIndex].status = 'cancelled';
          this.orders[orderIndex].formatted_status = 'Annulée';
        }

        if (this.currentOrder?.id === orderId) {
          this.currentOrder.status = 'cancelled';
          this.currentOrder.formatted_status = 'Annulée';
        }

        return true;
      } catch (error: any) {
        console.error('Erreur cancelOrder:', error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Méthodes utilitaires
    formatPrice(price: number): string {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
      }).format(price);
    },

    formatDate(dateString: string): string {
      return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    clearCurrentOrder() {
      this.currentOrder = null;
    },

    clearError() {
      this.error = null;
    }
  }
});
```

---

## 📞 SUPPORT & CONTACT

Pour toute question technique concernant l'API :
- **Documentation technique** : Ce fichier
- **Tests** : Utiliser GraphQL Playground à `http://127.0.0.1:8000/graphql-playground`
- **Validation** : Toutes les requêtes sont validées côté serveur

---

*Documentation mise à jour le 18 août 2025*  
*API Version: 2.0 - Laravel 12 + Lighthouse GraphQL*  
*Système Order optimisé avec gestion complète des commandes*
