# 📋 Référence complète du schéma GraphQL - API INTRAFMC

## 🔧 Scalaires

```graphql
scalar DateTime  # Format: Y-m-d H:i:s (ex: 2024-08-28 14:30:00)
scalar Date      # Format: Y-m-d (ex: 2024-08-28)
scalar Upload    # Fichier multipart/form-data
scalar JSON      # Données JSON arbitraires
```

## 🔑 Types d'authentification

```graphql
type AuthPayload {
  access_token: String!
  token_type: String!
  expires_in: Int
  user: User!
}

type LogoutResponse {
  message: String!
}
```

## 👤 Types utilisateur

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
  is_admin: Boolean!
  is_active: Boolean!
  email_verified_at: String
  created_at: DateTime
  updated_at: DateTime
}

type UserProfile {
  id: ID!
  name: String!
  email: String!
  phone: String
  address: String
  birth_date: String
  avatar: String
  created_at: DateTime!
  updated_at: DateTime!
  email_verified_at: DateTime
  last_login: DateTime
  orders_count: Int!
  total_spent: Float!
  preferences: UserPreferences!
}

type UserPreferences {
  theme: String!
  language: String!
  notifications: Boolean!
}

type TokenValidation {
  valid: Boolean!
  expires_at: DateTime
  user: TokenUser
}

type TokenUser {
  id: ID!
  name: String!
  email: String!
  is_admin: Boolean!
}
```

## 🛍️ Types produits

```graphql
type ProductCBD {
  id: ID!
  name: String!
  description: String
  price: Float!
  stock: Int!
  images: [String!]!
  analysis_file: String
  analysis_image: String
  categories: [Category!]!
  suppliers: [Supplier!]!
  created_at: DateTime
  updated_at: DateTime
}

type ProductInsights {
  productId: ID
  productName: String
  currentPrice: Float
  currentStock: Int
  baseMetrics: ProductBaseMetrics
  timelinePerformance: ProductTimelinePerformance
  competitiveAnalysis: ProductCompetitiveAnalysis
  predictions: ProductPredictions
  recommendations: [ProductRecommendation!]
  lastUpdated: DateTime
}

type ProductBaseMetrics {
  totalSales: Int!
  totalRevenue: Float!
  averageRating: Float
  conversionRate: Float
  returnRate: Float
  viewCount: Int
  wishlistCount: Int
}

type ProductTimelinePerformance {
  salesTrend: JSON
  revenueTrend: JSON
  stockMovements: JSON
  priceHistory: JSON
  seasonalPatterns: JSON
}

type ProductRecommendation {
  type: String!
  priority: String!
  description: String!
  estimatedImpact: Float
  actionItems: [String!]
}

type ProductSuggestion {
  id: ID!
  name: String!
  price: Float!
  images: [String!]
  reason: String!
  confidence: Float!
}
```

## 📂 Types catégories

```graphql
type Category {
  id: ID!
  name: String!
  description: String
  created_at: String
  products: [ProductCBD!]!
}

type CategoryWithCounts {
  id: ID!
  name: String!
  slug: String!
  description: String
  productCount: Int!
  parentId: ID
  level: Int!
  children: [CategoryWithCounts!]!
  isActive: Boolean!
  displayOrder: Int!
  imageUrl: String
}

type CategoryList {
  id: ID!
  name: String!
  slug: String
  description: String
  products_count: Int!
}

type PopularProduct {
  id: ID!
  name: String!
  price: Float!
  stock: Int!
  orderCount: Int!
  revenue: Float!
}

type CategoryTrend {
  categoryId: ID!
  categoryName: String!
  period: String!
  salesGrowth: Float!
  revenueGrowth: Float!
  productCount: Int!
  topProducts: [ProductCBD!]!
}
```

## 🛒 Types panier

```graphql
type Cart {
  id: ID!
  user_id: ID!
  product_id: ID!
  quantity: Int!
  product: ProductCBD!
  created_at: DateTime
}

type CartSummary {
  totalItems: Int!
  totalPrice: Float!
  estimatedShipping: Float
  totalWithShipping: Float!
  items: [CartItem!]!
}

type CartItem {
  id: ID!
  quantity: Int!
  subtotal: Float!
  product: ProductCBD!
}
```

## 📦 Types commandes

```graphql
type Order {
  id: ID!
  user_id: ID!
  user: User!
  total: Float!
  status: String!  # pending, validated, shipped, delivered, cancelled
  created_at: DateTime
  updated_at: DateTime
  products: [ProductCBD!]!
}

type OrderProductPivot {
  quantity: Int!
  unit_price: Float!
}

type OrderProduct {
  id: ID!
  name: String!
  price: Float!
  stock: Int!
  images: [String!]
  pivot: OrderProductPivot
}

type OrderStats {
  order_id: ID!
  total_items: Int!
  product_count: Int!
  total_amount: Float!
  average_item_price: Float!
  created_at: DateTime!
  status: String!
  formatted_status: String!
}

type OrderSummary {
  id: ID!
  total: Float!
  status: String!
  user_name: String!
  product_count: Int!
  total_items: Int!
  created_at: DateTime!
}
```

## 🏭 Types fournisseurs

```graphql
type Supplier {
  id: ID!
  name: String!
  email: String
  phone: String
  address: String
  website: String
  contact_person: String
  description: String
  products: [ProductCBD!]!
}
```

## 📦 Types arrivages

```graphql
type CbdArrival {
  id: ID!
  amount: Float!
  status: String!  # pending, validated
  products: [ArrivalProductCbd!]!
  created_at: DateTime
  updated_at: DateTime
}

type ArrivalProductCbd {
  id: ID!
  arrival_id: ID!
  product_id: ID!
  quantity: Int!
  cost_price: Float!
  product: ProductCBD
}
```

## 📊 Types statistiques

```graphql
type OrderStatistics {
  totalRevenue: Float!
  totalOrders: Int!
  averageOrderValue: Float!
  uniqueCustomers: Int!
  topProducts: JSON!
  topCustomers: JSON!
}

type RevenueTimeline {
  periods: JSON!
  totalRevenue: Float!
  totalOrders: Int!
}

type CustomerGrowthTimeline {
  periods: JSON!
  summary: JSON!
  trends: JSON!
}

type UserStatistics {
  userId: ID!
  userName: String!
  email: String!
  totalOrders: Int!
  totalAmount: Float!
  averageOrderValue: Float!
  orderFrequency: Float!
  daysSinceFirstOrder: Int!
  favoriteProducts: JSON!
  orderHistory: JSON!
}

type BasicOrderStats {
  totalOrders: Int!
  totalRevenue: Float!
  averageOrderValue: Float!
  period: String!
}

type MonthlyRevenueResponse {
  months: [MonthlyRevenue!]!
  summary: RevenueSummary!
}

type MonthlyRevenue {
  month: Int!
  year: Int!
  revenue: Float!
  orders: Int!
  growth: Float!
}

type RevenueSummary {
  totalRevenue: Float!
  averageMonthly: Float!
  bestMonth: MonthlyRevenue!
  worstMonth: MonthlyRevenue!
}

# Dashboard
type DashboardStats {
  orders: OrdersStats!
  revenue: RevenueStats!
  users: UsersStats!
  products: ProductsStats!
}

type OrdersStats {
  total: Int!
  thisMonth: Int!
  lastMonth: Int!
  growth: Float!
}

type RevenueStats {
  total: Float!
  thisMonth: Float!
  lastMonth: Float!
  growth: Float!
}

type UsersStats {
  total: Int!
  active: Int!
  newThisMonth: Int!
}

type ProductsStats {
  total: Int!
  lowStock: Int!
  outOfStock: Int!
}

# Résumés optimisés
type OrdersSummary {
  totalOrders: Int!
  pendingOrders: Int!
  validatedOrders: Int!
  cancelledOrders: Int!
  totalRevenue: Float!
}

type UsersSummary {
  totalUsers: Int!
  activeUsers: Int!
  inactiveUsers: Int!
  adminUsers: Int!
  recentRegistrations: Int!
}

type EcommerceSummary {
  totalProducts: Int!
  totalCategories: Int!
  lowStockProducts: Int!
  outOfStockProducts: Int!
  totalValue: Float!
  averagePrice: Float!
}
```

## 🔄 Types de réponse

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

type ChangePasswordResponse {
  success: Boolean
  message: String
}

type StatisticsResponse {
  success: Boolean!
  message: String
  data: JSON
}
```

## 📥 Types d'entrée (Input)

```graphql
# Produits
input CreateProductCBDInput {
  name: String!
  description: String
  price: Float!
  stock: Int = 0
  category_id: ID
  category_ids: [ID!]
  images: [Upload!]
  analysis_file: Upload
}

input UpdateProductCBDInput {
  name: String
  description: String
  price: Float
  stock: Int
  category_id: ID
  category_ids: [ID!]
  images: [Upload]
  analysis_file: Upload
}

# Catégories
input CreateCategoryInput {
  name: String!
  description: String
}

input UpdateCategoryInput {
  name: String
  description: String
}

# Fournisseurs
input UpdateSupplierInput {
  name: String
  email: String
  phone: String
  address: String
  website: String
  contact_person: String
  description: String
}

# Panier
input AddToCartInput {
  product_id: ID!
  quantity: Int!
}

input UpdateCartItemInput {
  quantity: Int!
}

# Commandes
input UpdateOrderStatusInput {
  id: ID!
  status: String!
}

# Arrivages
input CreateArrivalInput {
  amount: Float!
  products: [ArrivalProductInput!]!
}

input UpdateArrivalInput {
  amount: Float
  products: [ArrivalProductInput!]
}

input ArrivalProductInput {
  product_id: ID!
  quantity: Int!
  cost_price: Float!
}

# Filtres
input UserFilterInput {
  is_active: Boolean
  is_admin: Boolean
}
```

## 🏷️ Enums

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

enum TrendGrouping {
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

enum SuggestionType {
  ALL
  FREQUENTLY_BOUGHT_TOGETHER
  SIMILAR_PRODUCTS
  RECOMMENDATIONS
  RECENTLY_VIEWED
}
```

## 🔍 Requêtes principales (Query)

```graphql
type Query {
  # === AUTHENTIFICATION ===
  me: User
  validateToken: TokenValidation!

  # === PRODUITS ===
  productsCBD(
    first: Int = 20
    page: Int
    name: String
    category_id: ID
  ): [ProductCBD!]!
  
  productCBD(id: ID!): ProductCBD
  
  searchProducts(
    query: String
    first: Int = 20
    page: Int
    category_id: ID
    min_price: Float
    max_price: Float
    in_stock: Boolean
  ): [ProductCBD!]!
  
  searchProductsByName(
    name: String!
    limit: Int = 10
  ): [ProductCBD!]!
  
  productInsights(productId: ID!): ProductInsights
  
  productPerformanceInsights(
    startDate: Date
    endDate: Date
    limit: Int = 20
  ): ProductInsights
  
  categoryTrends(
    startDate: Date
    endDate: Date
    groupBy: TrendGrouping = MONTH
  ): [CategoryTrend!]!

  # === CATÉGORIES ===
  categories: [Category!]!
  category(id: ID!): Category
  categoriesWithCounts: [CategoryWithCounts!]!
  categoriesList: [CategoryList!]!
  
  popularProductsByCategory(
    categoryId: ID!
    limit: Int = 5
    period: PopularityPeriod = MONTH
  ): [PopularProduct!]!

  # === PANIER ===
  myCart: [Cart!]!
  cartSummary: CartSummary!
  
  cartSuggestions(
    limit: Int = 5
    type: SuggestionType = ALL
  ): [ProductSuggestion!]!

  # === COMMANDES ===
  orders: [Order!]!
  myOrders: [Order!]!
  order(id: ID!): Order
  orderStats(first: Int): [OrderStats!]!
  orderSummaries(first: Int): [OrderSummary!]!

  # === UTILISATEURS ===
  users(first: Int, page: Int): [User!]!
  user(id: ID!): User
  myProfileComplete: UserProfile!
  
  usersSearch(
    search: String
    role: String
    status: String
    first: Int = 15
    page: Int = 1
  ): [User!]!

  # === FOURNISSEURS ===
  suppliers(first: Int, page: Int): [Supplier!]!
  supplier(id: ID!): Supplier

  # === ARRIVAGES ===
  arrivals(first: Int, page: Int): [CbdArrival!]!
  arrival(arrival_id: ID!): CbdArrival

  # === STATISTIQUES ===
  orderStatistics(
    startDate: Date!
    endDate: Date!
  ): OrderStatistics!
  
  revenueTimeline(
    startDate: Date!
    endDate: Date!
    groupBy: TimeGrouping = MONTH
  ): RevenueTimeline!
  
  customerGrowthTimeline(
    startDate: Date!
    endDate: Date!
  ): CustomerGrowthTimeline!
  
  basicOrderStats(
    startDate: Date!
    endDate: Date!
  ): BasicOrderStats!
  
  monthlyRevenue(months: Int = 12): MonthlyRevenueResponse!
  
  userOrderStatistics(
    userId: ID
    startDate: Date
    endDate: Date
  ): UserStatistics!
  
  dashboardStats(period: TimeGrouping = MONTH): JSON!

  # === RÉSUMÉS OPTIMISÉS ===
  ordersSummary: OrdersSummary!
  usersSummary: UsersSummary!
  ecommerceSummary: EcommerceSummary!
  dashboardStatsOptimized: DashboardStats!
  
  # === RECHERCHES AVANCÉES ===
  productsSearch(
    search: String
    category: ID
    minPrice: Float
    maxPrice: Float
    inStock: Boolean
    first: Int = 20
    page: Int = 1
  ): [ProductCBD!]!
}
```

## ✏️ Mutations principales

```graphql
type Mutation {
  # === AUTHENTIFICATION ===
  login(email: String!, password: String!): AuthPayload
  logout: LogoutResponse

  # === PRODUITS ===
  createProductCBD(input: CreateProductCBDInput!): ProductCBD!
  
  createProductCBDWithFiles(
    name: String!
    description: String
    price: Float!
    stock: Int! = 0
    category_id: ID
    category_ids: [ID!]
    images: [Upload!]
    analysis_file: Upload
  ): ProductCBD!
  
  updateProductCBD(id: ID!, input: UpdateProductCBDInput!): ProductCBD!
  deleteProduct(id: ID!): DeleteResponse!
  
  uploadProductImages(
    product_id: ID!
    images: [Upload!]!
  ): FileUploadResponse!
  
  uploadProductAnalysisFile(
    product_id: ID!
    analysis_file: Upload!
  ): FileUploadResponse!

  # === CATÉGORIES ===
  createCategory(input: CreateCategoryInput!): Category
  updateCategory(id: ID!, input: UpdateCategoryInput!): Category
  deleteCategory(id: ID!): DeleteResponse
  attachCategoryToProduct(category_id: ID!, product_id: ID!): Category
  detachCategoryFromProduct(category_id: ID!, product_id: ID!): Category

  # === PANIER ===
  addToCart(input: AddToCartInput!): Cart
  updateCartItem(id: ID!, input: UpdateCartItemInput!): Cart
  removeFromCart(id: ID!): DeleteResponse
  clearCart: DeleteResponse

  # === COMMANDES ===
  checkout: Order
  cancelOrder(id: ID!): Boolean
  updateOrderStatus(input: UpdateOrderStatusInput!): Order

  # === UTILISATEURS ===
  updateUser(
    id: ID!
    name: String
    email: String
    phone: String
    address: String
    birth_date: Date
    is_active: Boolean
    is_admin: Boolean
    password: String
    password_confirmation: String
  ): User
  
  deleteUser(id: ID!): DeleteResponse
  
  updateProfile(
    id: ID!
    name: String
    email: String
    phone: String
    address: String
    birth_date: Date
    avatar: String
  ): User
  
  changePassword(
    current_password: String!
    new_password: String!
  ): ChangePasswordResponse

  # === FOURNISSEURS ===
  createSupplier(
    name: String!
    email: String
    phone: String
    address: String
    website: String
    contact_person: String
    description: String
  ): Supplier
  
  updateSupplier(id: ID!, input: UpdateSupplierInput!): Supplier
  deleteSupplier(id: ID!): DeleteResponse
  attachSupplierToProduct(supplier_id: ID!, product_id: ID!): Supplier
  detachSupplierFromProduct(supplier_id: ID!, product_id: ID!): Supplier

  # === ARRIVAGES ===
  createArrival(input: CreateArrivalInput!): CbdArrival
  updateArrival(arrival_id: ID!, input: UpdateArrivalInput!): CbdArrival
  validateArrival(arrival_id: ID!): CbdArrival
  deleteArrival(arrival_id: ID!): CbdArrival
}
```

## 🛡️ Directives de sécurité

```graphql
# Authentification requise
@guard(with: ["api"])

# Vérification des permissions
@can(ability: "view", model: "App\\Models\\ProductCBD")
@can(ability: "update", model: "App\\Models\\ProductCBD", find: "id")
@can(ability: "delete", model: "App\\Models\\ProductCBD", find: "id")
@can(ability: "admin")  # Admin uniquement

# Validation des données
@rules(apply: ["required", "string", "max:255"])
@rules(apply: ["required", "numeric", "min:0"])
@rules(apply: ["required", "exists:products,id"])

# Pagination
@paginate(defaultCount: 20, maxCount: 100)

# Relations
@find(model: "App\\Models\\ProductCBD")
@create(model: "App\\Models\\ProductCBD")
@update(model: "App\\Models\\ProductCBD")

# Filtres
@where(operator: "like")
@eq
@spread  # Étale les propriétés d'un input
```

## 🔧 Resolver patterns

```graphql
# Resolvers personnalisés
@field(resolver: "App\\Modules\\Product_CBD\\GraphQL\\Queries\\ProductSearchQuery@search")
@field(resolver: "App\\Modules\\Product_CBD\\GraphQL\\Mutations\\ProductCBDMutator@create")
@field(resolver: "App\\Modules\\Statistics\\GraphQL\\Queries\\OrderStatisticsQuery@getOrderStatistics")

# Builders pour pagination
@paginate(builder: "App\\Modules\\Category\\GraphQL\\Queries\\CategoryQuery@categories")
```

---

**📋 Cette référence couvre l'intégralité du schéma GraphQL de l'API INTRAFMC**  
**📅 Dernière mise à jour** : 28 août 2025  
**🔗 Version** : 1.0.0
