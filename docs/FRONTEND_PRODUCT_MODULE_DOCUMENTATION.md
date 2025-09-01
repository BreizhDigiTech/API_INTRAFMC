# 🛍️ Documentation Frontend - Module Produits CBD

## 📋 Vue d'ensemble

Le **module Product_CBD** est le système central de gestion des produits dans l'API INTRAFMC. Il propose une suite complète de fonctionnalités pour la gestion, recherche, analyse et insights des produits CBD avec un **tri automatique par date de création décroissante** (plus récent en premier).

---

## 🎯 Fonctionnalités clés

### ✅ **Gestion complète CRUD**
- ✨ Créer, lire, modifier, supprimer des produits
- 🖼️ Gestion d'images multiples avec métadonnées
- 📎 Upload de fichiers d'analyse (PDF, DOC, images)
- 🏷️ Association aux catégories (simple et multiple)
- **📅 Tri automatique par date de création (plus récent en premier)**

### ✅ **Recherche intelligente**
- 🔍 Recherche textuelle avec autocomplétion
- 🎛️ Filtres avancés : catégorie, prix, stock
- 💡 Suggestions de recherche en temps réel
- ⚡ Tri par pertinence + date de création

### ✅ **Analytics avancées**
- 📊 Performance des produits par période
- 📈 Tendances par catégorie
- 🏆 Analyses compétitives
- 🔮 Prédictions et recommandations

---

## 🏗️ Structure des données

### **Type ProductCBD**
```typescript
interface ProductCBD {
  id: string
  name: string
  description?: string
  price: number
  images: string[]                    // Chemins relatifs
  image_urls: string[]                // URLs complètes prêtes à l'affichage
  image_metadata?: Record<string, any>
  stock: number
  
  // Fichiers d'analyse
  analysis_file?: string
  analysis_file_url?: string
  analysis_file_original_name?: string
  analysis_file_size?: number
  analysis_file_mime_type?: string
  
  // Relations
  category_id?: string
  category?: Category
  categories: Category[]              // Relations multiples
  
  // Métadonnées
  created_at: string                  // ISO 8601
  updated_at: string                  // ISO 8601
}
```

### **Type Category**
```typescript
interface Category {
  id: string
  name: string
  description?: string
  created_at: string
  updated_at: string
}
```

---

## 🔍 Requêtes GraphQL

### **1. Lister tous les produits (avec pagination)**

```graphql
query GetProducts($first: Int, $page: Int, $name: String, $category_id: ID) {
  productsCBD(
    first: $first
    page: $page 
    name: $name
    category_id: $category_id
  ) {
    id
    name
    description
    price
    images
    image_urls
    stock
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

**🎯 Variables d'exemple :**
```json
{
  "first": 50,
  "page": 1,
  "name": "CBD",
  "category_id": "3"
}
```

**📅 Tri automatique :** Les produits sont automatiquement triés du **plus récent au plus ancien** par date de création.

**📊 Pagination :** Plus de limite ! `allProductsCBD` récupère **TOUS les produits**. Les requêtes paginées `productsCBD` utilisent `first` sans limitation max.

### **2. Récupérer TOUS les produits (sans pagination)**

```graphql
query GetAllProducts($name: String, $category_id: ID, $limit: Int) {
  allProductsCBD(
    name: $name
    category_id: $category_id
    limit: $limit
  ) {
    id
    name
    description
    price
    images
    image_urls
    stock
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

**🚀 Variables d'exemple pour TOUS les produits :**
```json
{
  "name": "",
  "category_id": ""
}
```

**⚡ Performance :** Cette requête récupère **TOUS les produits** en une seule fois (illimité). Idéale pour :
- Export de données
- Sélecteurs complets  
- Analyses globales

**🎯 Recommandation :** Utilisez cette requête pour récupérer tous vos **191 produits** d'un coup !

### **3. Obtenir un produit spécifique**

```graphql
query GetProduct($id: ID!) {
  productCBD(id: $id) {
    id
    name
    description
    price
    images
    image_urls
    image_metadata
    stock
    
    # Fichier d'analyse
    analysis_file
    analysis_file_url
    analysis_file_original_name
    analysis_file_size
    analysis_file_mime_type
    
    # Relations
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

### **4. Recherche avancée**

```graphql
query SearchProducts(
  $query: String
  $first: Int
  $category_id: ID
  $min_price: Float
  $max_price: Float
  $in_stock: Boolean
) {
  searchProducts(
    query: $query
    first: $first
    category_id: $category_id
    min_price: $min_price
    max_price: $max_price
    in_stock: $in_stock
  ) {
    id
    name
    description
    price
    stock
    image_urls
    category {
      id
      name
    }
    created_at
  }
}
```

**🔍 Tri intelligent :**
- **Avec recherche textuelle** : Tri par pertinence (correspondance exacte, puis partielle), puis par date de création décroissante
- **Sans recherche** : Tri par date de création décroissante (plus récent en premier)

### **5. Autocomplétion**

```graphql
query GetProductSuggestions($query: String!) {
  productSuggestions(query: $query)
}
```

**💡 Suggestions intelligentes :** Basées sur les noms des produits les plus récents correspondant à votre recherche.

### **6. Recherche rapide par nom**

```graphql
query SearchByName($name: String!, $limit: Int) {
  searchProductsByName(name: $name, limit: $limit) {
    id
    name
    price
    image_urls
    stock
    created_at
  }
}
```

**⚡ Recherche rapide :** Tri par pertinence (correspondance exacte en premier), puis par date de création décroissante.

---

## ✏️ Mutations GraphQL

### **1. Créer un produit simple**

```graphql
mutation CreateProduct($input: CreateProductCBDInput!) {
  createProductCBD(input: $input) {
    id
    name
    description
    price
    stock
    images
    image_urls
    analysis_file_url
    category {
      id
      name
    }
    categories {
      id
      name
    }
    created_at
  }
}
```

**📝 Variables d'exemple :**
```json
{
  "input": {
    "name": "CBD Premium Oil 10%",
    "description": "Huile CBD premium à 10% de concentration",
    "price": 49.99,
    "stock": 100,
    "category_id": "1",
    "category_ids": ["1", "3"]
  }
}
```

### **2. Créer un produit avec fichiers**

```graphql
mutation CreateProductWithFiles(
  $name: String!
  $description: String
  $price: Float!
  $stock: Int!
  $category_id: ID
  $category_ids: [ID!]
  $images: [Upload!]
  $analysis_file: Upload
) {
  createProductCBDWithFiles(
    name: $name
    description: $description
    price: $price
    stock: $stock
    category_id: $category_id
    category_ids: $category_ids
    images: $images
    analysis_file: $analysis_file
  ) {
    id
    name
    price
    stock
    image_urls
    analysis_file_url
    created_at
  }
}
```

### **3. Mettre à jour un produit**

```graphql
mutation UpdateProduct($id: ID!, $input: UpdateProductCBDInput!) {
  updateProductCBD(id: $id, input: $input) {
    id
    name
    description
    price
    stock
    image_urls
    analysis_file_url
    updated_at
  }
}
```

### **4. Supprimer un produit**

```graphql
mutation DeleteProduct($id: ID!) {
  deleteProductCBD(id: $id) {
    success
    message
  }
}
```

### **5. Gestion des images**

#### **Upload d'images**
```graphql
mutation UploadProductImages($productId: ID!, $images: [Upload!]!) {
  uploadProductImages(productId: $productId, images: $images) {
    path
    url
    original_name
    size
    mime_type
  }
}
```

#### **Supprimer des images spécifiques**
```graphql
mutation RemoveProductImages($product_id: ID!, $image_paths: [String!]!) {
  removeProductImages(product_id: $product_id, image_paths: $image_paths) {
    id
    images
    image_urls
  }
}
```

#### **Supprimer toutes les images**
```graphql
mutation ClearProductImages($product_id: ID!) {
  clearProductImages(product_id: $product_id) {
    id
    images
    image_urls
  }
}
```

### **6. Gestion des fichiers d'analyse**

```graphql
mutation UploadAnalysisFile($product_id: ID!, $file: Upload!) {
  uploadProductAnalysisFile(product_id: $product_id, file: $file) {
    id
    analysis_file
    analysis_file_url
    analysis_file_original_name
    analysis_file_size
    analysis_file_mime_type
  }
}
```

---

## 📊 Analytics et Insights

### **Performance d'un produit**

```graphql
query GetProductInsights(
  $productId: ID
  $startDate: Date
  $endDate: Date
  $limit: Int
) {
  productPerformanceInsights(
    productId: $productId
    startDate: $startDate
    endDate: $endDate
    limit: $limit
  ) {
    productId
    productName
    currentPrice
    currentStock
    
    baseMetrics {
      totalOrders
      totalQuantitySold
      totalRevenue
      averageUnitPrice
      uniqueCustomers
      conversionRate
      returnRate
      reorderRate
      profitMargin
    }
    
    timelinePerformance {
      periods {
        period
        orders
        quantitySold
        revenue
        averageOrderValue
      }
      trend
      seasonality
      volatility
      growthRate
    }
    
    recommendations {
      type
      priority
      title
      description
      expectedImpact
    }
    
    topPerformers {
      productId
      productName
      revenue
      growthRate
      trendDirection
      score
    }
    
    lastUpdated
  }
}
```

### **Tendances par catégorie**

```graphql
query GetCategoryTrends(
  $startDate: Date
  $endDate: Date
  $groupBy: TrendGrouping
) {
  categoryTrends(
    startDate: $startDate
    endDate: $endDate
    groupBy: $groupBy
  ) {
    categoryId
    categoryName
    periods {
      period
      revenue
      orders
      quantity
    }
    totalRevenue
    totalOrders
    trend
    growthRate
    marketShare
  }
}
```

---

## 🎨 Intégration Frontend

### **Configuration Apollo Client**

```typescript
import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client'
import { setContext } from '@apollo/client/link/context'

const httpLink = createHttpLink({
  uri: 'http://localhost:8000/graphql',
})

const authLink = setContext((_, { headers }) => {
  const token = localStorage.getItem('auth-token')
  return {
    headers: {
      ...headers,
      authorization: token ? `Bearer ${token}` : "",
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  }
})

const client = new ApolloClient({
  link: authLink.concat(httpLink),
  cache: new InMemoryCache(),
  defaultOptions: {
    watchQuery: {
      errorPolicy: 'all'
    }
  }
})
```

### **Hook de produits (Vue 3 + Composition API)**

```typescript
// composables/useProducts.ts
import { ref, reactive, computed } from 'vue'
import { useQuery, useMutation } from '@vue/apollo-composable'
import { 
  GET_PRODUCTS_QUERY,
  SEARCH_PRODUCTS_QUERY,
  CREATE_PRODUCT_MUTATION,
  UPDATE_PRODUCT_MUTATION,
  DELETE_PRODUCT_MUTATION
} from '@/graphql/products'

export function useProducts() {
  const products = ref([])
  const loading = ref(false)
  const currentPage = ref(1)
  const searchQuery = ref('')
  
  const filters = reactive({
    category_id: '',
    min_price: null,
    max_price: null,
    in_stock: false
  })

  // Requête principale avec tri automatique
  const { result: productsResult, refetch, fetchMore } = useQuery(
    GET_PRODUCTS_QUERY,
    () => ({
      first: 50, // Augmenté de 20 à 50 pour récupérer plus de produits
      page: currentPage.value,
      name: searchQuery.value || undefined,
      ...filters
    }),
    {
      notifyOnNetworkStatusChange: true
    }
  )

  // 🚀 NOUVELLE: Requête pour récupérer TOUS les produits
  const { result: allProductsResult, refetch: refetchAll } = useQuery(
    GET_ALL_PRODUCTS_QUERY,
    () => ({
      name: searchQuery.value || undefined,
      category_id: filters.category_id || undefined
    }),
    {
      enabled: false // Désactivée par défaut, à appeler manuellement
    }
  )

  // Mutations
  const { mutate: createProduct } = useMutation(CREATE_PRODUCT_MUTATION)
  const { mutate: updateProduct } = useMutation(UPDATE_PRODUCT_MUTATION)
  const { mutate: deleteProduct } = useMutation(DELETE_PRODUCT_MUTATION)

  // Computed
  const hasProducts = computed(() => products.value.length > 0)
  const isFiltered = computed(() => 
    searchQuery.value || 
    filters.category_id || 
    filters.min_price || 
    filters.max_price || 
    filters.in_stock
  )

  // Méthodes
  const searchProducts = async (query: string) => {
    searchQuery.value = query
    currentPage.value = 1
    await refetch()
  }

  const applyFilters = async (newFilters: any) => {
    Object.assign(filters, newFilters)
    currentPage.value = 1
    await refetch()
  }

  const loadMoreProducts = async () => {
    if (!loading.value) {
      currentPage.value++
      await fetchMore({
        variables: {
          page: currentPage.value
        }
      })
    }
  }

  const createNewProduct = async (productData: any) => {
    try {
      const result = await createProduct({ input: productData })
      await refetch() // Recharger pour voir le nouveau produit en premier
      return result.data.createProductCBD
    } catch (error) {
      console.error('Erreur création produit:', error)
      throw error
    }
  }

  const loadAllProducts = async () => {
    try {
      loading.value = true
      await refetchAll()
      if (allProductsResult.value?.allProductsCBD) {
        products.value = allProductsResult.value.allProductsCBD
      }
    } catch (error) {
      console.error('Erreur chargement tous produits:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  // Watchers
  watch(productsResult, (newResult) => {
    if (newResult?.productsCBD) {
      products.value = newResult.productsCBD
    }
  })

  return {
    // State
    products,
    loading,
    currentPage,
    searchQuery,
    filters,
    
    // Computed
    hasProducts,
    isFiltered,
    
    // Methods
    searchProducts,
    applyFilters,
    loadMoreProducts,
    loadAllProducts, // 🚀 NOUVELLE méthode pour charger tous les produits
    createNewProduct,
    updateProduct,
    deleteProduct,
    refetch
  }
}
```

### **Composant Liste de Produits**

```vue
<template>
  <div class="products-container">
    <!-- En-tête avec recherche -->
    <div class="products-header">
      <h1>Gestion des Produits</h1>
      <div class="search-section">
        <input
          v-model="searchQuery"
          @input="handleSearch"
          placeholder="Rechercher des produits..."
          class="search-input"
        />
        <button @click="showCreateModal = true" class="btn-primary">
          ➕ Nouveau Produit
        </button>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters-section">
      <select v-model="filters.category_id" @change="applyFilters">
        <option value="">Toutes les catégories</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>
      
      <input
        v-model="filters.min_price"
        type="number"
        placeholder="Prix min"
        @blur="applyFilters"
      />
      
      <input
        v-model="filters.max_price"
        type="number"
        placeholder="Prix max"
        @blur="applyFilters"
      />
      
      <label class="checkbox-label">
        <input
          v-model="filters.in_stock"
          type="checkbox"
          @change="applyFilters"
        />
        En stock seulement
      </label>
      
      <button v-if="isFiltered" @click="clearFilters" class="btn-secondary">
        🗑️ Effacer filtres
      </button>
      
      <button @click="loadAllProducts" class="btn-primary" :disabled="loading">
        📦 Charger tous les produits ({{ loading ? 'Chargement...' : '191 produits' }})
      </button>
    </div>

    <!-- Indicateur de tri -->
    <div class="sort-indicator">
      📅 Triés par date de création (plus récent en premier)
      <span v-if="isFiltered" class="filter-count">
        • {{ products.length }} résultat(s)
      </span>
    </div>

    <!-- Grille de produits -->
    <div class="products-grid">
      <div
        v-for="product in products"
        :key="product.id"
        class="product-card"
        @click="selectProduct(product)"
      >
        <!-- Image principale -->
        <div class="product-image">
          <img
            v-if="product.image_urls?.length"
            :src="product.image_urls[0]"
            :alt="product.name"
            @error="handleImageError"
          />
          <div v-else class="no-image">
            🖼️ Pas d'image
          </div>
          
          <!-- Badge stock -->
          <div class="stock-badge" :class="stockBadgeClass(product.stock)">
            {{ product.stock }} en stock
          </div>
        </div>

        <!-- Informations produit -->
        <div class="product-info">
          <h3 class="product-name">{{ product.name }}</h3>
          <p class="product-description">{{ truncate(product.description, 100) }}</p>
          
          <div class="product-metrics">
            <span class="price">{{ formatPrice(product.price) }}</span>
            <span class="created-date">{{ formatDate(product.created_at) }}</span>
          </div>

          <!-- Catégories -->
          <div class="categories">
            <span
              v-for="category in product.categories"
              :key="category.id"
              class="category-tag"
            >
              {{ category.name }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="product-actions">
          <button @click.stop="editProduct(product)" class="btn-edit">
            ✏️
          </button>
          <button @click.stop="confirmDelete(product)" class="btn-delete">
            🗑️
          </button>
        </div>
      </div>
    </div>

    <!-- Chargement de plus de produits -->
    <div v-if="hasMore" class="load-more">
      <button @click="loadMoreProducts" :disabled="loading" class="btn-secondary">
        {{ loading ? 'Chargement...' : 'Charger plus' }}
      </button>
    </div>

    <!-- État vide -->
    <div v-if="!hasProducts && !loading" class="empty-state">
      <h3>{{ isFiltered ? 'Aucun produit trouvé' : 'Aucun produit' }}</h3>
      <p>
        {{ isFiltered 
          ? 'Essayez de modifier vos critères de recherche' 
          : 'Commencez par créer votre premier produit'
        }}
      </p>
      <button v-if="!isFiltered" @click="showCreateModal = true" class="btn-primary">
        Créer le premier produit
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProducts } from '@/composables/useProducts'
import { useRouter } from 'vue-router'

const router = useRouter()
const {
  products,
  loading,
  searchQuery,
  filters,
  hasProducts,
  isFiltered,
  searchProducts,
  applyFilters,
  loadMoreProducts,
  loadAllProducts, // 🚀 Nouvelle méthode
  deleteProduct
} = useProducts()

const categories = ref([])
const showCreateModal = ref(false)
const hasMore = ref(true)

// Méthodes
const handleSearch = debounce((event) => {
  searchProducts(event.target.value)
}, 300)

const clearFilters = () => {
  searchQuery.value = ''
  Object.assign(filters, {
    category_id: '',
    min_price: null,
    max_price: null,
    in_stock: false
  })
  applyFilters(filters)
}

const selectProduct = (product) => {
  router.push(`/products/${product.id}`)
}

const editProduct = (product) => {
  router.push(`/products/${product.id}/edit`)
}

const confirmDelete = async (product) => {
  if (confirm(`Supprimer "${product.name}" ?`)) {
    try {
      await deleteProduct({ id: product.id })
      // Les produits seront automatiquement rechargés
    } catch (error) {
      alert('Erreur lors de la suppression')
    }
  }
}

const stockBadgeClass = (stock) => {
  if (stock === 0) return 'out-of-stock'
  if (stock < 10) return 'low-stock'
  return 'in-stock'
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(date))
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

// Utilitaire debounce
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

onMounted(() => {
  // Charger les catégories pour les filtres
  // loadCategories()
})
</script>

<style scoped>
.products-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.search-section {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  width: 300px;
}

.filters-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.sort-indicator {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: #e3f2fd;
  border-radius: 4px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.product-card {
  border: 1px solid #ddd;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
  background: white;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  color: #999;
}

.stock-badge {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: bold;
}

.stock-badge.in-stock {
  background: #4caf50;
  color: white;
}

.stock-badge.low-stock {
  background: #ff9800;
  color: white;
}

.stock-badge.out-of-stock {
  background: #f44336;
  color: white;
}

.product-info {
  padding: 1rem;
}

.product-name {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.product-description {
  color: #666;
  margin: 0 0 1rem 0;
  line-height: 1.4;
}

.product-metrics {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #2196f3;
}

.created-date {
  font-size: 0.8rem;
  color: #999;
}

.categories {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.category-tag {
  background: #e3f2fd;
  color: #1976d2;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
}

.product-actions {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 1rem;
  background: #f8f9fa;
}

.btn-edit, .btn-delete {
  padding: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-edit {
  background: #fff3e0;
  color: #f57c00;
}

.btn-delete {
  background: #ffebee;
  color: #d32f2f;
}

.load-more {
  text-align: center;
  margin-top: 2rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.btn-primary {
  background: #2196f3;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
}

.btn-secondary {
  background: #f5f5f5;
  color: #333;
  border: 1px solid #ddd;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
```

---

## 🔐 Authentification et Sécurité

### **Headers requis**
```javascript
{
  'Authorization': 'Bearer YOUR_JWT_TOKEN',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}
```

### **Permissions par action**
- **👀 Lecture** : `@guard(with: ["api"])` + permissions view
- **➕ Création** : `@guard(with: ["api"])`
- **✏️ Modification** : `@guard(with: ["api"])` + `@can(ability: "update")`
- **🗑️ Suppression** : `@guard(with: ["api"])` + `@can(ability: "delete")`

---

## 📁 Gestion des Fichiers

### **Images de produits**
- **📏 Formats** : JPG, JPEG, PNG, WebP
- **📦 Taille max** : 5MB par image
- **💾 Stockage** : `storage/app/public/cbd_products/`
- **🌐 URLs** : `/storage/cbd_products/`

### **Fichiers d'analyse**
- **📄 Formats** : PDF, DOC, DOCX, JPG, JPEG, PNG
- **📦 Taille max** : 10MB
- **💾 Stockage** : `storage/app/public/product_analysis/`

### **Upload multipart avec Fetch**
```javascript
const uploadImages = async (productId, files) => {
  const formData = new FormData()
  
  formData.append('operations', JSON.stringify({
    query: `
      mutation UploadProductImages($productId: ID!, $images: [Upload!]!) {
        uploadProductImages(productId: $productId, images: $images) {
          path
          url
          original_name
        }
      }
    `,
    variables: { productId, images: null }
  }))
  
  const map = {}
  files.forEach((file, index) => {
    map[index] = [`variables.images.${index}`]
  })
  formData.append('map', JSON.stringify(map))
  
  files.forEach((file, index) => {
    formData.append(index, file)
  })
  
  const response = await fetch('/graphql', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  })
  
  return response.json()
}
```

---

## 🎯 Bonnes Pratiques

### **⚡ Performance et récupération de données**
- ✅ **Pagination** : 20 produits par défaut, jusqu'à 500 maximum
- ✅ **Récupération complète** : Nouvelle requête `allProductsCBD` pour tous les produits
- ✅ **Cache intelligent** : Apollo cache pour optimiser les requêtes
- ✅ **Tri automatique** : Plus récent en premier
- 🎯 **Solution 192+ produits** : Utilisez `allProductsCBD` pour récupérer TOUS vos produits sans limitation

### **🎨 UX/UI**
- ✅ Prévisualisez les images avant upload
- ✅ Affichez les erreurs de validation clairement
- ✅ Implémentez la recherche en temps réel avec debounce (300ms)
- ✅ Utilisez des loaders pendant les opérations
- ✅ Indiquez visuellement l'ordre de tri

### **🔒 Sécurité**
- ✅ Validez les fichiers côté client ET serveur
- ✅ Limitez les tailles de fichiers
- ✅ Vérifiez les types MIME
- ✅ Sanitisez les noms de fichiers

---

## 🚨 Gestion d'Erreurs et Résolution de Problèmes

### **❌ Problème : "Je ne récupère que 50 produits"**

**🔧 Solutions :**

#### **Option 1 : Utiliser `allProductsCBD` (Recommandée)**
```graphql
query GetAllProducts {
  allProductsCBD {
    id
    name
    price
    image_urls
    created_at
  }
}
```

#### **Option 2 : Augmenter `first` dans `productsCBD`**
```graphql
query GetManyProducts {
  productsCBD(first: 200) {
    id
    name
    price
    image_urls
    created_at
  }
}
```

#### **Option 3 : Pagination multiple**
```javascript
const loadAllProductsPaginated = async () => {
  let allProducts = []
  let page = 1
  let hasMorePages = true
  
  while (hasMorePages) {
    const result = await apolloClient.query({
      query: GET_PRODUCTS_QUERY,
      variables: { first: 50, page }
    })
    
    const products = result.data.productsCBD
    allProducts = [...allProducts, ...products]
    
    hasMorePages = products.length === 50 // S'il y a moins de 50, c'est la dernière page
    page++
  }
  
  return allProducts
}
```

### **Codes d'erreur courants**

```typescript
const handleGraphQLError = (error) => {
  if (error.graphQLErrors?.length) {
    error.graphQLErrors.forEach(err => {
      switch (err.extensions?.category) {
        case 'validation':
          showValidationErrors(err.extensions.validation)
          break
        case 'authorization':
          showAuthError('Permissions insuffisantes')
          break
        case 'authentication':
          redirectToLogin()
          break
        default:
          showError(err.message)
      }
    })
  } else if (error.networkError) {
    showError('Erreur de connexion au serveur')
  } else {
    showError('Une erreur inattendue s\'est produite')
  }
}

// Validation côté client
const validateProduct = (product) => {
  const errors = {}
  
  if (!product.name?.trim()) {
    errors.name = 'Le nom est requis'
  }
  
  if (!product.price || product.price <= 0) {
    errors.price = 'Le prix doit être supérieur à 0'
  }
  
  if (!product.stock || product.stock < 0) {
    errors.stock = 'Le stock ne peut pas être négatif'
  }
  
  return errors
}
```

---

## 📚 Ressources et Tests

### **🧪 Test d'une requête avec cURL**
```bash
curl -X POST http://localhost:8000/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "query": "query { productsCBD(first: 5) { id name price image_urls created_at } }"
  }'
```

### **📖 Documentation associée**
- 📄 [Collection de requêtes GraphQL](./GRAPHQL_QUERIES_COLLECTION.md)
- 📄 [Documentation API complète](./API_DOCUMENTATION.md)
- 📄 [Guide de démarrage rapide](./QUICK_START_GUIDE.md)

---

## ✅ Checklist d'Intégration

### **🏗️ Configuration de base**
- [ ] Configuration Apollo Client avec authentification
- [ ] Gestion des tokens JWT
- [ ] Configuration des uploads multipart

### **🧩 Composants essentiels**
- [ ] Liste des produits avec tri automatique
- [ ] Détail d'un produit
- [ ] Formulaire de création/édition
- [ ] Composant de recherche avec suggestions
- [ ] Galerie d'images avec upload

### **⚙️ Fonctionnalités avancées**
- [ ] Filtres et recherche avancée
- [ ] Pagination et chargement infini
- [ ] Analytics et graphiques
- [ ] Gestion d'erreurs robuste
- [ ] Cache et optimisations

### **🧪 Tests**
- [ ] Tests unitaires des composants
- [ ] Tests d'intégration des mutations
- [ ] Tests des uploads de fichiers
- [ ] Tests de la recherche et des filtres

---

## 🎉 Conclusion

Cette documentation vous fournit tout le nécessaire pour intégrer efficacement le module produit dans votre application frontend. Le **tri automatique par date de création décroissante** garantit que vos utilisateurs voient toujours les produits les plus récents en premier.

**Caractéristiques clés à retenir :**
- 📅 **Tri automatique** : Plus récent en premier
- 🔍 **Recherche intelligente** : Pertinence + chronologie
- 🖼️ **Gestion complète des fichiers** : Images + analyses
- 📊 **Analytics avancées** : Performance et insights
- ⚡ **Performance optimisée** : Pagination et cache

**Prêt à construire une expérience produit exceptionnelle !** 🚀
