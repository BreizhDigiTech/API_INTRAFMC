# 📚 Collection de requêtes GraphQL - API INTRAFMC

## 🔐 Authentification

### Connexion
```graphql
mutation Login {
  login(email: "admin@fmc.com", password: "password") {
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

### Utilisateur connecté
```graphql
query Me {
  me {
    id
    name
    email
    phone
    address
    birth_date
    avatar
    is_admin
    is_active
    created_at
  }
}
```

### Déconnexion
```graphql
mutation Logout {
  logout {
    message
  }
}
```

---

## 🛍️ Produits CBD

### Liste des produits avec pagination
```graphql
query GetProducts($first: Int, $page: Int, $category_id: ID) {
  productsCBD(first: $first, page: $page, category_id: $category_id) {
    id
    name
    description
    price
    stock
    images
    analysis_file
    categories {
      id
      name
    }
    suppliers {
      id
      name
    }
    created_at
    updated_at
  }
}
```

### Détail d'un produit
```graphql
query GetProduct($id: ID!) {
  productCBD(id: $id) {
    id
    name
    description
    price
    stock
    images
    analysis_file
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
    created_at
    updated_at
  }
}
```

### Recherche avancée de produits
```graphql
query SearchProducts(
  $query: String,
  $first: Int,
  $category_id: ID,
  $min_price: Float,
  $max_price: Float,
  $in_stock: Boolean
) {
  searchProducts(
    query: $query,
    first: $first,
    category_id: $category_id,
    min_price: $min_price,
    max_price: $max_price,
    in_stock: $in_stock
  ) {
    id
    name
    description
    price
    stock
    images
    categories {
      name
    }
  }
}
```

### Recherche rapide par nom
```graphql
query SearchByName($name: String!, $limit: Int) {
  searchProductsByName(name: $name, limit: $limit) {
    id
    name
    price
    stock
    images
  }
}
```

### Insights produits (Admin)
```graphql
query ProductInsights($productId: ID!) {
  productInsights(productId: $productId) {
    productId
    productName
    currentPrice
    currentStock
    baseMetrics {
      totalSales
      totalRevenue
      averageRating
      conversionRate
      returnRate
    }
    timelinePerformance {
      salesTrend
      revenueTrend
      stockMovements
    }
    recommendations {
      type
      priority
      description
      estimatedImpact
    }
    lastUpdated
  }
}
```

### Performance globale des produits
```graphql
query ProductPerformance($startDate: Date, $endDate: Date, $limit: Int) {
  productPerformanceInsights(
    startDate: $startDate,
    endDate: $endDate,
    limit: $limit
  ) {
    overview {
      totalProducts
      activeProducts
      topPerformers
      underPerformers
    }
    trends {
      bestSellers
      risingStars
      declining
    }
    recommendations {
      restockAlerts
      pricingOptimizations
      categoryInsights
    }
  }
}
```

### Créer un produit
```graphql
mutation CreateProduct($input: CreateProductCBDInput!) {
  createProductCBD(input: $input) {
    id
    name
    description
    price
    stock
    created_at
  }
}
```

### Créer produit avec fichiers
```graphql
mutation CreateProductWithFiles(
  $name: String!,
  $price: Float!,
  $stock: Int!,
  $description: String,
  $category_ids: [ID!],
  $images: [Upload!],
  $analysis_file: Upload
) {
  createProductCBDWithFiles(
    name: $name,
    price: $price,
    stock: $stock,
    description: $description,
    category_ids: $category_ids,
    images: $images,
    analysis_file: $analysis_file
  ) {
    id
    name
    price
    images
    analysis_file
  }
}
```

### Modifier un produit
```graphql
mutation UpdateProduct($id: ID!, $input: UpdateProductCBDInput!) {
  updateProductCBD(id: $id, input: $input) {
    id
    name
    description
    price
    stock
    updated_at
  }
}
```

### Supprimer un produit
```graphql
mutation DeleteProduct($id: ID!) {
  deleteProduct(id: $id) {
    success
    message
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
    product {
      id
      name
      price
      stock
      images
    }
    created_at
  }
}
```

### Résumé du panier
```graphql
query CartSummary {
  cartSummary {
    totalItems
    totalPrice
    estimatedShipping
    totalWithShipping
    items {
      id
      quantity
      subtotal
      product {
        name
        price
      }
    }
  }
}
```

### Suggestions pour le panier
```graphql
query CartSuggestions($limit: Int, $type: SuggestionType) {
  cartSuggestions(limit: $limit, type: $type) {
    id
    name
    price
    images
    reason
    confidence
  }
}
```

### Ajouter au panier
```graphql
mutation AddToCart($input: AddToCartInput!) {
  addToCart(input: $input) {
    id
    quantity
    product {
      id
      name
      price
    }
    created_at
  }
}
```

### Modifier quantité dans le panier
```graphql
mutation UpdateCartItem($id: ID!, $input: UpdateCartItemInput!) {
  updateCartItem(id: $id, input: $input) {
    id
    quantity
    product {
      name
      price
    }
  }
}
```

### Retirer du panier
```graphql
mutation RemoveFromCart($id: ID!) {
  removeFromCart(id: $id) {
    success
    message
  }
}
```

### Vider le panier
```graphql
mutation ClearCart {
  clearCart {
    success
    message
  }
}
```

---

## 📦 Commandes

### Mes commandes
```graphql
query MyOrders($first: Int, $page: Int) {
  myOrders(first: $first, page: $page) {
    id
    total
    status
    created_at
    updated_at
    products {
      id
      name
      price
      images
      pivot {
        quantity
        unit_price
      }
    }
  }
}
```

### Toutes les commandes (Admin)
```graphql
query AllOrders($first: Int, $page: Int) {
  orders(first: $first, page: $page) {
    id
    total
    status
    created_at
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
  }
}
```

### Détail d'une commande
```graphql
query OrderDetails($id: ID!) {
  order(id: $id) {
    id
    total
    status
    created_at
    updated_at
    user {
      id
      name
      email
      phone
      address
    }
    products {
      id
      name
      price
      images
      pivot {
        quantity
        unit_price
      }
    }
  }
}
```

### Statistiques de commandes
```graphql
query OrderStats($first: Int) {
  orderStats(first: $first) {
    order_id
    total_items
    product_count
    total_amount
    average_item_price
    created_at
    status
    formatted_status
  }
}
```

### Résumé des commandes
```graphql
query OrderSummaries($first: Int) {
  orderSummaries(first: $first) {
    id
    total
    status
    user_name
    product_count
    total_items
    created_at
  }
}
```

### Finaliser une commande
```graphql
mutation Checkout {
  checkout {
    id
    total
    status
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

### Annuler une commande
```graphql
mutation CancelOrder($id: ID!) {
  cancelOrder(id: $id)
}
```

### Changer le statut d'une commande (Admin)
```graphql
mutation UpdateOrderStatus($input: UpdateOrderStatusInput!) {
  updateOrderStatus(input: $input) {
    id
    status
    updated_at
  }
}
```

---

## 📂 Catégories

### Liste des catégories
```graphql
query Categories($first: Int, $page: Int) {
  categories(first: $first, page: $page) {
    id
    name
    description
    created_at
    products {
      id
      name
      price
    }
  }
}
```

### Détail d'une catégorie
```graphql
query Category($id: ID!) {
  category(id: $id) {
    id
    name
    description
    created_at
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

### Catégories avec compteurs
```graphql
query CategoriesWithCounts {
  categoriesWithCounts {
    id
    name
    slug
    description
    productCount
    parentId
    level
    children {
      id
      name
      productCount
    }
    isActive
    displayOrder
    imageUrl
  }
}
```

### Produits populaires par catégorie
```graphql
query PopularProductsByCategory(
  $categoryId: ID!,
  $limit: Int,
  $period: PopularityPeriod
) {
  popularProductsByCategory(
    categoryId: $categoryId,
    limit: $limit,
    period: $period
  ) {
    id
    name
    price
    stock
    orderCount
    revenue
  }
}
```

### Créer une catégorie
```graphql
mutation CreateCategory($input: CreateCategoryInput!) {
  createCategory(input: $input) {
    id
    name
    description
    created_at
  }
}
```

### Modifier une catégorie
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

### Supprimer une catégorie
```graphql
mutation DeleteCategory($id: ID!) {
  deleteCategory(id: $id) {
    success
    message
  }
}
```

---

## 👥 Utilisateurs

### Liste des utilisateurs (Admin)
```graphql
query Users($first: Int, $page: Int) {
  users(first: $first, page: $page) {
    id
    name
    email
    phone
    address
    birth_date
    avatar
    is_admin
    is_active
    created_at
    updated_at
  }
}
```

### Détail d'un utilisateur
```graphql
query User($id: ID!) {
  user(id: $id) {
    id
    name
    email
    phone
    address
    birth_date
    avatar
    is_admin
    is_active
    created_at
    updated_at
  }
}
```

### Recherche d'utilisateurs
```graphql
query SearchUsers(
  $search: String,
  $role: String,
  $status: String,
  $first: Int,
  $page: Int
) {
  usersSearch(
    search: $search,
    role: $role,
    status: $status,
    first: $first,
    page: $page
  ) {
    id
    name
    email
    is_admin
    is_active
    created_at
  }
}
```

### Profil complet
```graphql
query MyProfileComplete {
  myProfileComplete {
    id
    name
    email
    phone
    address
    birth_date
    avatar
    created_at
    updated_at
    email_verified_at
    last_login
    orders_count
    total_spent
    preferences {
      theme
      language
      notifications
    }
  }
}
```

### Modifier un utilisateur (Admin)
```graphql
mutation UpdateUser(
  $id: ID!,
  $name: String,
  $email: String,
  $phone: String,
  $address: String,
  $birth_date: Date,
  $is_active: Boolean,
  $is_admin: Boolean
) {
  updateUser(
    id: $id,
    name: $name,
    email: $email,
    phone: $phone,
    address: $address,
    birth_date: $birth_date,
    is_active: $is_active,
    is_admin: $is_admin
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

### Modifier son profil
```graphql
mutation UpdateProfile(
  $id: ID!,
  $name: String,
  $phone: String,
  $address: String,
  $birth_date: Date,
  $avatar: String
) {
  updateProfile(
    id: $id,
    name: $name,
    phone: $phone,
    address: $address,
    birth_date: $birth_date,
    avatar: $avatar
  ) {
    id
    name
    phone
    address
    birth_date
    avatar
    updated_at
  }
}
```

### Changer mot de passe
```graphql
mutation ChangePassword(
  $current_password: String!,
  $new_password: String!
) {
  changePassword(
    current_password: $current_password,
    new_password: $new_password
  ) {
    success
    message
  }
}
```

### Supprimer un utilisateur (Admin)
```graphql
mutation DeleteUser($id: ID!) {
  deleteUser(id: $id) {
    success
    message
  }
}
```

---

## 📊 Statistiques

### Statistiques des commandes
```graphql
query OrderStatistics($startDate: Date!, $endDate: Date!) {
  orderStatistics(startDate: $startDate, endDate: $endDate) {
    totalRevenue
    totalOrders
    averageOrderValue
    uniqueCustomers
    topProducts
    topCustomers
  }
}
```

### Timeline des revenus
```graphql
query RevenueTimeline(
  $startDate: Date!,
  $endDate: Date!,
  $groupBy: TimeGrouping
) {
  revenueTimeline(
    startDate: $startDate,
    endDate: $endDate,
    groupBy: $groupBy
  ) {
    periods
    totalRevenue
    totalOrders
  }
}
```

### Croissance des clients
```graphql
query CustomerGrowth($startDate: Date!, $endDate: Date!) {
  customerGrowthTimeline(startDate: $startDate, endDate: $endDate) {
    periods
    summary
    trends
  }
}
```

### Statistiques basiques
```graphql
query BasicOrderStats($startDate: Date!, $endDate: Date!) {
  basicOrderStats(startDate: $startDate, endDate: $endDate) {
    totalOrders
    totalRevenue
    averageOrderValue
    period
  }
}
```

### Revenus mensuels
```graphql
query MonthlyRevenue($months: Int) {
  monthlyRevenue(months: $months) {
    months {
      month
      year
      revenue
      orders
      growth
    }
    summary {
      totalRevenue
      averageMonthly
      bestMonth
      worstMonth
    }
  }
}
```

### Statistiques utilisateur
```graphql
query UserOrderStatistics(
  $userId: ID,
  $startDate: Date,
  $endDate: Date
) {
  userOrderStatistics(
    userId: $userId,
    startDate: $startDate,
    endDate: $endDate
  ) {
    userId
    userName
    email
    totalOrders
    totalAmount
    averageOrderValue
    orderFrequency
    daysSinceFirstOrder
    favoriteProducts
    orderHistory
  }
}
```

### Dashboard global
```graphql
query DashboardStats($period: TimeGrouping) {
  dashboardStats(period: $period)
}
```

---

## 🏭 Fournisseurs

### Liste des fournisseurs
```graphql
query Suppliers($first: Int, $page: Int) {
  suppliers(first: $first, page: $page) {
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
    }
  }
}
```

### Détail d'un fournisseur
```graphql
query Supplier($id: ID!) {
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
      price
      stock
    }
  }
}
```

### Créer un fournisseur
```graphql
mutation CreateSupplier(
  $name: String!,
  $email: String,
  $phone: String,
  $address: String,
  $website: String,
  $contact_person: String,
  $description: String
) {
  createSupplier(
    name: $name,
    email: $email,
    phone: $phone,
    address: $address,
    website: $website,
    contact_person: $contact_person,
    description: $description
  ) {
    id
    name
    email
    contact_person
  }
}
```

### Modifier un fournisseur
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

### Supprimer un fournisseur
```graphql
mutation DeleteSupplier($id: ID!) {
  deleteSupplier(id: $id) {
    success
    message
  }
}
```

---

## 📦 Arrivages

### Liste des arrivages
```graphql
query Arrivals($first: Int, $page: Int) {
  arrivals(first: $first, page: $page) {
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
      cost_price
      product {
        name
        price
      }
    }
  }
}
```

### Détail d'un arrivage
```graphql
query Arrival($arrival_id: ID!) {
  arrival(arrival_id: $arrival_id) {
    id
    amount
    status
    created_at
    updated_at
    products {
      id
      product_id
      quantity
      cost_price
      product {
        id
        name
        price
        stock
      }
    }
  }
}
```

### Créer un arrivage
```graphql
mutation CreateArrival($input: CreateArrivalInput!) {
  createArrival(input: $input) {
    id
    amount
    status
    created_at
  }
}
```

### Modifier un arrivage
```graphql
mutation UpdateArrival($arrival_id: ID!, $input: UpdateArrivalInput!) {
  updateArrival(arrival_id: $arrival_id, input: $input) {
    id
    amount
    status
    updated_at
  }
}
```

### Valider un arrivage
```graphql
mutation ValidateArrival($arrival_id: ID!) {
  validateArrival(arrival_id: $arrival_id) {
    id
    status
    updated_at
  }
}
```

### Supprimer un arrivage
```graphql
mutation DeleteArrival($arrival_id: ID!) {
  deleteArrival(arrival_id: $arrival_id) {
    id
    status
  }
}
```

---

## 🔧 APIs d'optimisation

### Résumé des commandes
```graphql
query OrdersSummary {
  ordersSummary {
    totalOrders
    pendingOrders
    validatedOrders
    cancelledOrders
    totalRevenue
  }
}
```

### Résumé des utilisateurs
```graphql
query UsersSummary {
  usersSummary {
    totalUsers
    activeUsers
    inactiveUsers
    adminUsers
    recentRegistrations
  }
}
```

### Résumé e-commerce
```graphql
query EcommerceSummary {
  ecommerceSummary {
    totalProducts
    totalCategories
    lowStockProducts
    outOfStockProducts
    totalValue
    averagePrice
  }
}
```

### Dashboard optimisé
```graphql
query DashboardStatsOptimized {
  dashboardStatsOptimized {
    orders {
      total
      thisMonth
      lastMonth
      growth
    }
    revenue {
      total
      thisMonth
      lastMonth
      growth
    }
    users {
      total
      active
      newThisMonth
    }
    products {
      total
      lowStock
      outOfStock
    }
  }
}
```

### Liste optimisée des catégories
```graphql
query CategoriesList {
  categoriesList {
    id
    name
    slug
    description
    products_count
  }
}
```

---

## 🔍 Recherches et filtres

### Recherche globale de produits
```graphql
query GlobalProductSearch(
  $search: String,
  $category: ID,
  $minPrice: Float,
  $maxPrice: Float,
  $inStock: Boolean,
  $first: Int,
  $page: Int
) {
  productsSearch(
    search: $search,
    category: $category,
    minPrice: $minPrice,
    maxPrice: $maxPrice,
    inStock: $inStock,
    first: $first,
    page: $page
  ) {
    id
    name
    price
    stock
    images
    categories {
      name
    }
  }
}
```

### Validation de token
```graphql
query ValidateToken {
  validateToken {
    valid
    expires_at
    user {
      id
      name
      email
      is_admin
    }
  }
}
```

---

## 📋 Variables d'exemple

### Pour CreateProductCBDInput
```json
{
  "input": {
    "name": "Huile CBD Premium 20%",
    "description": "Huile de CBD full spectrum de haute qualité",
    "price": 89.99,
    "stock": 25,
    "category_ids": ["1", "3"]
  }
}
```

### Pour UpdateProductCBDInput
```json
{
  "id": "1",
  "input": {
    "price": 79.99,
    "stock": 30,
    "description": "Description mise à jour"
  }
}
```

### Pour AddToCartInput
```json
{
  "input": {
    "product_id": "1",
    "quantity": 2
  }
}
```

### Pour recherche de produits
```json
{
  "query": "huile cbd",
  "first": 10,
  "category_id": "1",
  "min_price": 20.0,
  "max_price": 100.0,
  "in_stock": true
}
```

### Pour statistiques
```json
{
  "startDate": "2024-01-01",
  "endDate": "2024-12-31",
  "groupBy": "MONTH"
}
```
