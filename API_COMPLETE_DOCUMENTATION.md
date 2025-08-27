# 📚 Documentation API Complète - Frontend

> Guide exhaustif de toutes les APIs GraphQL disponibles pour l'application INTRAFMC

## 🌟 Vue d'Ensemble

Cette API utilise **GraphQL** avec authentification **JWT** et suit une architecture modulaire. Chaque module gère un domaine métier spécifique.

**Endpoint GraphQL** : `http://localhost:8000/graphql`  
**Playground** : `http://localhost:8000/graphql-playground`  
**Authentification** : JWT Bearer Token

## 🗂️ Modules Disponibles

1. **[Auth](#-1-authentification)** - Connexion, déconnexion, utilisateur connecté
2. **[Register](#-2-inscription)** - Inscription, vérification email
3. **[User](#-3-gestion-utilisateurs)** - CRUD utilisateurs, profils
4. **[Product CBD](#-4-produits-cbd)** - Catalogue produits, analytics, insights
5. **[Category](#-5-catégories)** - Classification produits, statistiques
6. **[Supplier](#-6-fournisseurs)** - Gestion fournisseurs
7. **[Cart](#-7-panier)** - Panier utilisateur, suggestions
8. **[Order](#-8-commandes)** - Processus commande, historique
9. **[Arrival](#-9-arrivages)** - Gestion stock arrivages (Admin)
10. **[Statistics](#-10-statistiques)** - Analytics business, KPIs

---

## 🔐 1. Authentification

### Connexion
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
      is_admin
      is_active
    }
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

### Utilisateur Connecté
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

**Headers requis** :
```javascript
{
  "Authorization": "Bearer YOUR_JWT_TOKEN",
  "Content-Type": "application/json"
}
```

---

## 📝 2. Inscription

### Créer un Compte
```graphql
mutation Register($name: String!, $email: String!, $password: String!, $password_confirmation: String!) {
  register(
    name: $name
    email: $email
    password: $password
    password_confirmation: $password_confirmation
  ) {
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

### Vérifier Email
```graphql
mutation VerifyEmail($token: String!) {
  verifyEmail(token: $token) {
    success
    message
  }
}
```

### Renvoyer Email de Vérification
```graphql
mutation ResendVerificationEmail($email: String!) {
  resendVerificationEmail(email: $email) {
    success
    message
  }
}
```

---

## 👥 3. Gestion Utilisateurs

### Lister Utilisateurs (Admin)
```graphql
query Users($first: Int, $page: Int) {
  users(first: $first, page: $page) {
    id
    name
    email
    phone
    address
    is_admin
    is_active
    created_at
  }
}
```

### Détails Utilisateur
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
    email_verified_at
    created_at
    updated_at
  }
}
```

### Modifier Utilisateur (Admin)
```graphql
mutation UpdateUser($id: ID!, $name: String, $email: String, $is_active: Boolean) {
  updateUser(id: $id, name: $name, email: $email, is_active: $is_active) {
    id
    name
    email
    is_active
  }
}
```

### Modifier Profil
```graphql
mutation UpdateProfile(
  $id: ID!
  $name: String
  $email: String
  $phone: String
  $address: String
  $birth_date: Date
  $avatar: String
) {
  updateProfile(
    id: $id
    name: $name
    email: $email
    phone: $phone
    address: $address
    birth_date: $birth_date
    avatar: $avatar
  ) {
    id
    name
    email
    phone
    address
    birth_date
    avatar
    updated_at
  }
}
```

**Variables d'exemple** :
```json
{
  "id": "1",
  "name": "Jean Dupont",
  "email": "jean.dupont@email.com",
  "phone": "+33 6 12 34 56 78",
  "address": "123 Rue de la Paix, 75001 Paris",
  "birth_date": "1990-05-15",
  "avatar": "avatars/profile.jpg"
}
```

**Notes importantes** :
- ✅ L'utilisateur ne peut modifier que son propre profil
- ✅ Tous les champs sont optionnels 
- ✅ L'email est vérifié pour éviter les doublons
- ✅ Les champs non fournis conservent leur valeur actuelle

### Changer Mot de Passe
```graphql
mutation ChangePassword($current_password: String!, $new_password: String!) {
  changePassword(current_password: $current_password, new_password: $new_password) {
    success
    message
  }
}
```

### Supprimer Utilisateur (Admin)
```graphql
mutation DeleteUser($id: ID!) {
  deleteUser(id: $id) {
    success
    message
  }
}
```

---

## 🌿 4. Produits CBD

### Lister Produits
```graphql
query Products($first: Int, $page: Int, $search: String, $category_id: ID) {
  products(first: $first, page: $page, search: $search, category_id: $category_id) {
    id
    name
    description
    price
    stock
    image_urls
    categories {
      id
      name
    }
    suppliers {
      id
      name
    }
    created_at
  }
}
```

### Détails Produit
```graphql
query Product($id: ID!) {
  product(id: $id) {
    id
    name
    description
    price
    stock
    image_urls
    image_metadata
    analysis_image_url
    analysis_data
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

### Créer Produit (Admin)
```graphql
mutation CreateProduct($input: CreateProductCBDInput!) {
  createProductCBD(input: $input) {
    id
    name
    price
    stock
    image_urls
  }
}
```

**Variables** :
```json
{
  "input": {
    "name": "Huile CBD 20%",
    "description": "Huile CBD premium",
    "price": 49.99,
    "stock": 100,
    "category_ids": ["1", "2"],
    "images": ["file1", "file2"]
  }
}
```

### Modifier Produit (Admin)
```graphql
mutation UpdateProduct($id: ID!, $input: UpdateProductCBDInput!) {
  updateProductCBD(id: $id, input: $input) {
    id
    name
    price
    stock
  }
}
```

### Supprimer Produit (Admin)
```graphql
mutation DeleteProduct($id: ID!) {
  deleteProduct(id: $id) {
    success
    message
  }
}
```

### Analytics Produit (Admin)
```graphql
query ProductInsights($productId: ID, $startDate: Date, $endDate: Date) {
  productPerformanceInsights(
    productId: $productId
    startDate: $startDate
    endDate: $endDate
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
    }
    timelinePerformance {
      periods {
        period
        orders
        quantitySold
        revenue
      }
      trend
      growthRate
    }
    recommendations {
      type
      priority
      description
      impact
    }
  }
}
```

### Tendances Catégories (Admin)
```graphql
query CategoryTrends($startDate: Date, $endDate: Date, $groupBy: TrendGrouping) {
  categoryTrends(startDate: $startDate, endDate: $endDate, groupBy: $groupBy) {
    categoryId
    categoryName
    periods {
      period
      orders
      revenue
      growth
    }
    totalRevenue
    totalOrders
    averageGrowth
  }
}
```

---

## 📁 5. Catégories

### Lister Catégories
```graphql
query Categories {
  categories {
    id
    name
    description
    products {
      id
      name
      price
    }
  }
}
```

### Catégories Enrichies (Avec Stats)
```graphql
query CategoriesWithStats {
  categoriesWithStats {
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

### Créer Catégorie (Admin)
```graphql
mutation CreateCategory($input: CreateCategoryInput!) {
  createCategory(input: $input) {
    id
    name
    description
  }
}
```

### Modifier Catégorie (Admin)
```graphql
mutation UpdateCategory($id: ID!, $input: UpdateCategoryInput!) {
  updateCategory(id: $id, input: $input) {
    id
    name
    description
  }
}
```

### Supprimer Catégorie (Admin)
```graphql
mutation DeleteCategory($id: ID!) {
  deleteCategory(id: $id) {
    success
    message
  }
}
```

### Produits Populaires par Catégorie
```graphql
query PopularProductsByCategory($categoryId: ID!, $period: PopularityPeriod, $limit: Int) {
  popularProductsByCategory(
    categoryId: $categoryId
    period: $period
    limit: $limit
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

---

## 🏭 6. Fournisseurs

### Lister Fournisseurs (Admin)
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
    }
  }
}
```

### Détails Fournisseur (Admin)
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

### Créer Fournisseur (Admin)
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
  }
}
```

### Associer Fournisseur à Produit (Admin)
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

---

## 🛒 7. Panier

### Mon Panier
```graphql
query MyCart {
  myCart {
    id
    quantity
    product {
      id
      name
      price
      image_urls
      stock
    }
    created_at
  }
}
```

### Total Panier
```graphql
query CartTotal {
  cartTotal {
    total
    itemCount
  }
}
```

### Ajouter au Panier
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
  }
}
```

**Variables** :
```json
{
  "input": {
    "product_id": "1",
    "quantity": 2
  }
}
```

### Modifier Quantité
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

### Supprimer du Panier
```graphql
mutation RemoveFromCart($id: ID!) {
  removeFromCart(id: $id) {
    success
    message
  }
}
```

### Vider le Panier
```graphql
mutation ClearCart {
  clearCart {
    success
    message
  }
}
```

### Suggestions Panier
```graphql
query CartSuggestions($limit: Int, $type: SuggestionType) {
  cartSuggestions(limit: $limit, type: $type) {
    productId
    productName
    price
    suggestionType
    reason
    score
    frequency
  }
}
```

---

## 📦 8. Commandes

### Mes Commandes
```graphql
query MyOrders($first: Int, $page: Int) {
  myOrders(first: $first, page: $page) {
    id
    total
    status
    formatted_status
    total_items
    product_count
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

### Toutes les Commandes (Admin)
```graphql
query Orders($first: Int, $page: Int) {
  orders(first: $first, page: $page) {
    id
    total
    status
    user {
      id
      name
      email
    }
    total_items
    product_count
    created_at
  }
}
```

### Détails Commande
```graphql
query Order($id: ID!) {
  order(id: $id) {
    id
    total
    status
    formatted_status
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
      image_urls
      pivot {
        quantity
        unit_price
      }
    }
    orderProducts {
      id
      quantity
      unit_price
      product {
        name
      }
    }
    created_at
    updated_at
  }
}
```

### Passer Commande
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

### Annuler Commande
```graphql
mutation CancelOrder($id: ID!) {
  cancelOrder(id: $id)
}
```

### Modifier Statut (Admin)
```graphql
mutation UpdateOrderStatus($input: UpdateOrderStatusInput!) {
  updateOrderStatus(input: $input) {
    id
    status
    formatted_status
  }
}
```

**Variables** :
```json
{
  "input": {
    "id": "1",
    "status": "shipped"
  }
}
```

---

## 📈 9. Arrivages (Admin)

### Lister Arrivages
```graphql
query Arrivals($first: Int, $page: Int) {
  arrivals(first: $first, page: $page) {
    id
    amount
    status
    products {
      id
      arrival_id
      product_id
      quantity
      unit_price
    }
    created_at
    updated_at
  }
}
```

### Détails Arrivage
```graphql
query Arrival($arrival_id: ID!) {
  arrival(arrival_id: $arrival_id) {
    id
    amount
    status
    products {
      id
      arrival_id
      product_id
      quantity
      unit_price
      total_price
    }
    created_at
  }
}
```

### Créer Arrivage
```graphql
mutation CreateArrival($input: CreateArrivalInput!) {
  createArrival(input: $input) {
    id
    amount
    status
  }
}
```

### Valider Arrivage
```graphql
mutation ValidateArrival($arrival_id: ID!) {
  validateArrival(arrival_id: $arrival_id) {
    id
    status
  }
}
```

---

## 📊 10. Statistiques (Admin)

### Dashboard Principal
```graphql
query DashboardStats($period: TimeGrouping) {
  dashboardStats(period: $period) {
    currentMonth {
      orders
      revenue
      users
      products
    }
    summary {
      totalOrders
      totalRevenue
      totalUsers
      totalProducts
    }
  }
}
```

### Statistiques Commandes
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

### Timeline Revenus
```graphql
query RevenueTimeline($startDate: Date!, $endDate: Date!, $groupBy: TimeGrouping) {
  revenueTimeline(startDate: $startDate, endDate: $endDate, groupBy: $groupBy) {
    periods
    totalRevenue
    totalOrders
  }
}
```

**Note importante** : `periods` est retourné en JSON string :
```javascript
const data = await client.query({ query: REVENUE_TIMELINE });
const periods = JSON.parse(data.revenueTimeline.periods);
```

### Statistiques Utilisateur
```graphql
query UserOrderStatistics($userId: ID, $startDate: Date, $endDate: Date) {
  userOrderStatistics(userId: $userId, startDate: $startDate, endDate: $endDate) {
    userId
    userName
    email
    totalOrders
    totalAmount
    averageOrderValue
    orderFrequency
    customerSegment
    favoriteProducts
    favoriteCategories
    behaviorAnalysis
    recommendations
    lastOrderDate
    memberSince
  }
}
```

### Revenus Mensuels
```graphql
query MonthlyRevenue($months: Int) {
  monthlyRevenue(months: $months) {
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

### Croissance Clients
```graphql
query CustomerGrowthTimeline($startDate: Date, $endDate: Date, $groupBy: TimeGrouping) {
  customerGrowthTimeline(startDate: $startDate, endDate: $endDate, groupBy: $groupBy) {
    periods
    summary
    trends
  }
}
```

---

## 🔧 Configuration Frontend

### Installation Apollo Client
```bash
npm install @apollo/client graphql
```

### Configuration de Base
```javascript
import { ApolloClient, InMemoryCache, createHttpLink } from '@apollo/client';
import { setContext } from '@apollo/client/link/context';

const httpLink = createHttpLink({
  uri: 'http://localhost:8000/graphql',
});

const authLink = setContext((_, { headers }) => {
  const token = localStorage.getItem('jwt_token');
  return {
    headers: {
      ...headers,
      authorization: token ? `Bearer ${token}` : "",
    }
  }
});

export const client = new ApolloClient({
  link: authLink.concat(httpLink),
  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          // Pagination pour les produits
          products: {
            keyArgs: ['search', 'category_id'],
            merge(existing = [], incoming) {
              return [...existing, ...incoming];
            },
          },
          // Pagination pour les commandes
          myOrders: {
            merge(existing = [], incoming) {
              return [...existing, ...incoming];
            },
          },
        },
      },
    },
  }),
});
```

### Gestion d'Erreur
```javascript
import { onError } from '@apollo/client/link/error';

const errorLink = onError(({ graphQLErrors, networkError }) => {
  if (graphQLErrors) {
    graphQLErrors.forEach(({ message, locations, path }) => {
      console.error(`GraphQL error: ${message}`);
      
      // Redirection si non authentifié
      if (message.includes('Unauthenticated')) {
        localStorage.removeItem('jwt_token');
        window.location.href = '/login';
      }
    });
  }
  
  if (networkError) {
    console.error(`Network error: ${networkError}`);
  }
});

// Combiner avec authLink
const link = from([errorLink, authLink, httpLink]);
```

### Hook Personnalisé Auth
```javascript
import { useQuery, useMutation } from '@apollo/client';
import { ME_QUERY, LOGIN_MUTATION, LOGOUT_MUTATION } from './queries';

export const useAuth = () => {
  const { data, loading } = useQuery(ME_QUERY, {
    errorPolicy: 'ignore'
  });
  
  const [login] = useMutation(LOGIN_MUTATION, {
    onCompleted: (data) => {
      localStorage.setItem('jwt_token', data.login.access_token);
      client.resetStore(); // Rafraîchir le cache
    }
  });
  
  const [logout] = useMutation(LOGOUT_MUTATION, {
    onCompleted: () => {
      localStorage.removeItem('jwt_token');
      client.clearStore();
    }
  });
  
  return {
    user: data?.me,
    isAuthenticated: !!data?.me,
    isAdmin: data?.me?.is_admin,
    loading,
    login,
    logout
  };
};
```

---

## 📋 Types de Données

### Énumérations Importantes
```typescript
enum TimeGrouping {
  HOUR = "HOUR"
  DAY = "DAY"
  WEEK = "WEEK"
  MONTH = "MONTH"
  QUARTER = "QUARTER"
  YEAR = "YEAR"
}

enum CustomerSegment {
  VIP = "VIP"
  PREMIUM = "PREMIUM"
  STANDARD = "STANDARD"
  NEW = "NEW"
  PROSPECT = "PROSPECT"
  AT_RISK = "AT_RISK"
}

enum SuggestionType {
  ALL = "ALL"
  CROSS_SELL = "CROSS_SELL"
  UP_SELL = "UP_SELL"
  FREQUENTLY_BOUGHT = "FREQUENTLY_BOUGHT"
}
```

### Interfaces TypeScript
```typescript
interface User {
  id: string;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  is_admin: boolean;
  is_active: boolean;
  created_at: string;
}

interface ProductCBD {
  id: string;
  name: string;
  description?: string;
  price: number;
  stock: number;
  image_urls: string[];
  categories: Category[];
  suppliers: Supplier[];
}

interface Order {
  id: string;
  total: number;
  status: string;
  formatted_status: string;
  total_items: number;
  product_count: number;
  user: User;
  products: ProductCBD[];
  created_at: string;
}

interface CartItem {
  id: string;
  quantity: number;
  product: ProductCBD;
  created_at: string;
}
```

---

## 🚀 Exemples d'Utilisation

### Authentification Complète
```javascript
// Connexion
const loginUser = async (email, password) => {
  try {
    const { data } = await client.mutate({
      mutation: LOGIN_MUTATION,
      variables: { email, password }
    });
    
    localStorage.setItem('jwt_token', data.login.access_token);
    return data.login.user;
  } catch (error) {
    throw new Error(error.message);
  }
};

// Vérifier si connecté
const getCurrentUser = async () => {
  try {
    const { data } = await client.query({
      query: ME_QUERY,
      fetchPolicy: 'network-only'
    });
    return data.me;
  } catch (error) {
    return null;
  }
};
```

### Gestion Profil Utilisateur
```javascript
// Hook personnalisé pour le profil
const useUserProfile = () => {
  const { data, loading, refetch } = useQuery(ME_QUERY);
  const [updateProfile] = useMutation(UPDATE_PROFILE_MUTATION);
  
  const handleUpdateProfile = async (profileData) => {
    try {
      const { data: result } = await updateProfile({
        variables: {
          id: data.me.id,
          ...profileData
        },
        refetchQueries: ['Me']
      });
      return result.updateProfile;
    } catch (error) {
      throw new Error(error.message);
    }
  };
  
  return {
    user: data?.me,
    loading,
    updateProfile: handleUpdateProfile,
    refetch
  };
};

// Composant Profil
const ProfilePage = () => {
  const { user, updateProfile } = useUserProfile();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    birth_date: '',
    avatar: ''
  });
  
  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        address: user.address || '',
        birth_date: user.birth_date || '',
        avatar: user.avatar || ''
      });
    }
  }, [user]);
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      // Envoyer uniquement les champs modifiés
      const changedFields = {};
      Object.keys(formData).forEach(key => {
        if (formData[key] !== (user[key] || '')) {
          changedFields[key] = formData[key];
        }
      });
      
      if (Object.keys(changedFields).length > 0) {
        await updateProfile(changedFields);
        alert('Profil mis à jour avec succès !');
      }
    } catch (error) {
      alert(`Erreur : ${error.message}`);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label>Nom complet</label>
        <input 
          type="text"
          value={formData.name}
          onChange={(e) => setFormData({...formData, name: e.target.value})}
        />
      </div>
      
      <div>
        <label>Email</label>
        <input 
          type="email"
          value={formData.email}
          onChange={(e) => setFormData({...formData, email: e.target.value})}
        />
      </div>
      
      <div>
        <label>Téléphone</label>
        <input 
          type="tel"
          value={formData.phone}
          onChange={(e) => setFormData({...formData, phone: e.target.value})}
          placeholder="+33 6 12 34 56 78"
        />
      </div>
      
      <div>
        <label>Adresse</label>
        <textarea 
          value={formData.address}
          onChange={(e) => setFormData({...formData, address: e.target.value})}
          placeholder="123 Rue de la Paix, 75001 Paris"
          rows="3"
        />
      </div>
      
      <div>
        <label>Date de naissance</label>
        <input 
          type="date"
          value={formData.birth_date}
          onChange={(e) => setFormData({...formData, birth_date: e.target.value})}
        />
      </div>
      
      <div>
        <label>Avatar (URL)</label>
        <input 
          type="url"
          value={formData.avatar}
          onChange={(e) => setFormData({...formData, avatar: e.target.value})}
          placeholder="https://example.com/avatar.jpg"
        />
      </div>
      
      <button type="submit">Mettre à jour le profil</button>
    </form>
  );
};

// Requêtes GraphQL
const UPDATE_PROFILE_MUTATION = gql`
  mutation UpdateProfile(
    $id: ID!
    $name: String
    $email: String
    $phone: String
    $address: String
    $birth_date: Date
    $avatar: String
  ) {
    updateProfile(
      id: $id
      name: $name
      email: $email
      phone: $phone
      address: $address
      birth_date: $birth_date
      avatar: $avatar
    ) {
      id
      name
      email
      phone
      address
      birth_date
      avatar
      updated_at
    }
  }
`;
```

### Gestion Panier
```javascript
// Composant Panier
const CartPage = () => {
  const { data, loading, refetch } = useQuery(MY_CART_QUERY);
  const [addToCart] = useMutation(ADD_TO_CART_MUTATION);
  const [updateQuantity] = useMutation(UPDATE_CART_ITEM_MUTATION);
  
  const handleAddToCart = async (productId, quantity) => {
    await addToCart({
      variables: { input: { product_id: productId, quantity } },
      refetchQueries: ['MyCart', 'CartTotal']
    });
  };
  
  const handleUpdateQuantity = async (cartItemId, quantity) => {
    await updateQuantity({
      variables: { id: cartItemId, input: { quantity } },
      refetchQueries: ['MyCart', 'CartTotal']
    });
  };
  
  if (loading) return <Spinner />;
  
  return (
    <div>
      {data.myCart.map(item => (
        <CartItem 
          key={item.id}
          item={item}
          onUpdateQuantity={handleUpdateQuantity}
        />
      ))}
    </div>
  );
};
```
```

### Dashboard Admin
```javascript
const AdminDashboard = () => {
  const { data: dashboardData } = useQuery(DASHBOARD_STATS_QUERY);
  const { data: revenueData } = useQuery(REVENUE_TIMELINE_QUERY, {
    variables: {
      startDate: '2025-01-01',
      endDate: '2025-08-26',
      groupBy: 'DAY'
    }
  });
  
  const periods = useMemo(() => {
    if (!revenueData?.revenueTimeline?.periods) return [];
    try {
      return JSON.parse(revenueData.revenueTimeline.periods);
    } catch (error) {
      console.error('Erreur parsing periods:', error);
      return [];
    }
  }, [revenueData]);
  
  return (
    <div className="dashboard">
      <StatsCards data={dashboardData?.dashboardStats} />
      <RevenueChart periods={periods} />
      <TopProducts />
      <RecentOrders />
    </div>
  );
};
```

---

## 🔍 Dépannage

### Erreurs Communes

1. **Token expiré** :
```javascript
// Vérifier expiration token
const isTokenExpired = (token) => {
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    return Date.now() >= payload.exp * 1000;
  } catch {
    return true;
  }
};
```

2. **Permissions insuffisantes** :
```javascript
// Vérifier permissions
if (error.message.includes('permissions')) {
  // Rediriger ou afficher message
  console.error('Permissions insuffisantes');
}
```

3. **Parsing JSON fields** :
```javascript
// Pour les champs JSON comme 'periods'
const safeJSONParse = (jsonString, fallback = []) => {
  try {
    return JSON.parse(jsonString || '[]');
  } catch (error) {
    console.error('Erreur parsing JSON:', error);
    return fallback;
  }
};
```

### Performance Tips

- Utiliser `fetchPolicy: 'cache-first'` pour les données statiques
- Implémenter la pagination avec `first` et `page`
- Utiliser `refetchQueries` pour synchroniser le cache
- Optimiser les requêtes avec des fragments GraphQL

---

**Documentation mise à jour** : 26 août 2025  
**Version API** : v1.0  
**Modules couverts** : 10/10  
**Endpoints documentés** : 50+ requêtes et mutations
