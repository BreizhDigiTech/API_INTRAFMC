# 🎭 **GÉNÉRATION DE DONNÉES FACTICES - Guide de développement**

## **🎯 Vue d'ensemble**

Ce système génère des données factices réalistes pour faciliter le développement frontend de l'API CBD. Il inclut des utilisateurs, produits, commandes, et toutes les données nécessaires pour tester l'interface.

---

## **🚀 Utilisation rapide**

### **Commandes disponibles :**

```bash
# Génération standard (recommandée)
php artisan dev:fake-data

# Génération avec remise à zéro complète
php artisan dev:fake-data --fresh

# Jeu de données minimal (développement rapide)
php artisan dev:fake-data --minimal

# Jeu de données complet (tests approfondis)
php artisan dev:fake-data --full
```

---

## **📊 Données générées**

### **👥 Utilisateurs (10 créés)**

#### **Comptes administrateurs :**
- **Email :** `admin@admin.com` / **Mot de passe :** `L15fddef!`
- **Email :** `manager@cbdstore.com` / **Mot de passe :** `manager123`

#### **Comptes clients :**
- **Email :** `marie.dubois@email.com` / **Mot de passe :** `client123`
- **Email :** `pierre.martin@email.com` / **Mot de passe :** `client123`
- **Email :** `sophie.bernard@email.com` / **Mot de passe :** `client123`
- **Email :** `lucas.petit@email.com` / **Mot de passe :** `client123`
- **Email :** `emma.moreau@email.com` / **Mot de passe :** `client123`

### **📂 Catégories (7 créées)**
- **Huiles CBD** - Huiles de CBD full spectrum et isolat
- **Fleurs CBD** - Fleurs premium cultivées biologiquement
- **E-liquides CBD** - Pour cigarettes électroniques
- **Cosmétiques CBD** - Crèmes et baumes
- **Comestibles CBD** - Bonbons et chocolats
- **Résines CBD** - Hash artisanal
- **Accessoires** - Vaporisateurs et équipements

### **🌿 Produits CBD (20-200 selon le mode)**
- **Noms réalistes :** "Huile CBD 10%", "OG Kush CBD", "E-liquide Menthe 300mg"
- **Prix cohérents :** De 5€ à 150€ selon le type
- **Stocks variables :** 0 à 500 unités
- **Descriptions détaillées** avec informations techniques
- **Images simulées** et fichiers d'analyse

### **🏭 Fournisseurs (3-8 selon le mode)**
- **Green Valley Farms** - Producteur bio français
- **CBD Premium Labs** - Laboratoire d'extraction
- **Swiss CBD Import** - Importateur suisse
- **Organic CBD Solutions** - Fabricant cosmétiques
- **Mediterranean Hemp Co.** - Coopérative méditerranéenne

### **🛒 Données transactionnelles**
- **Paniers** avec 1-5 articles par utilisateur actif
- **Commandes** avec historique sur 6 mois
- **Statuts variés :** pending, processing, shipped, delivered, cancelled
- **Arrivages** de marchandises avec validation

---

## **🎨 Variations des données**

### **Produits spéciaux disponibles :**
- **🔥 Produits populaires** - Stock élevé, noms marqués
- **💎 Gamme premium** - Prix majorés de 50%
- **🏷️ Produits en promotion** - Prix réduits de 20%
- **📦 Stock faible** - 0-10 unités (pour tester les alertes)

### **Types d'utilisateurs :**
- **👑 Admins** - Accès complet à toutes les fonctions
- **👤 Clients actifs** - Avec historique de commandes
- **😴 Comptes inactifs** - Pour tester la modération

### **Fournisseurs diversifiés :**
- **🇫🇷 Locaux** - Délais courts, spécialités françaises
- **🌍 Internationaux** - Suisse, Pays-Bas, Italie
- **⭐ Premium** - Conditions avantageuses

---

## **🛠️ Personnalisation avancée**

### **Factories disponibles :**

```php
// Créer des utilisateurs spécifiques
User::factory()->admin()->create();
User::factory()->premium()->create();
User::factory()->inactive()->create();

// Créer des produits spéciaux
ProductCBD::factory()->popular()->create();
ProductCBD::factory()->premium()->create();
ProductCBD::factory()->discounted()->create();
ProductCBD::factory()->lowStock()->create();

// Créer des commandes spécifiques
Order::factory()->delivered()->create();
Order::factory()->cancelled()->create();
Order::factory()->large()->create();

// Créer des fournisseurs spéciaux
Supplier::factory()->premium()->create();
Supplier::factory()->local()->create();
Supplier::factory()->international()->create();
```

### **Commandes Tinker utiles :**

```php
// Ajouter plus de produits
ProductCBD::factory()->count(50)->create();

// Créer des commandes récentes
Order::factory()->count(20)->recent()->create();

// Générer des paniers pleins
foreach(User::where('is_admin', false)->get() as $user) {
    Cart::factory()->count(rand(3,8))->create(['user_id' => $user->id]);
}

// Créer des arrivages en cours
CbdArrival::factory()->count(5)->pending()->create();
```

---

## **📱 Test des endpoints GraphQL**

### **Requêtes de base pour le frontend :**

#### **Authentification :**
```graphql
mutation {
  login(email: "admin@admin.com", password: "L15fddef!") {
    access_token
    token_type
    user {
      id
      name
      email
      is_admin
    }
  }
}
```

#### **Liste des produits :**
```graphql
query {
  products(per_page: 12) {
    data {
      id
      name
      price
      stock
      images
      description
    }
    paginatorInfo {
      total
      hasMorePages
    }
  }
}
```

#### **Panier utilisateur :**
```graphql
query {
  cartItems {
    id
    quantity
    product {
      id
      name
      price
      stock
    }
  }
  cartTotal
}
```

#### **Historique des commandes :**
```graphql
query {
  myOrders {
    id
    total
    status
    created_at
    items {
      quantity
      product {
        name
        price
      }
    }
  }
}
```

---

## **🔧 Maintenance et nettoyage**

### **Réinitialisation complète :**
```bash
php artisan dev:fake-data --fresh
```

### **Nettoyage sélectif :**
```bash
# Supprimer seulement les commandes de test
php artisan tinker --execute="Order::truncate();"

# Vider les paniers
php artisan tinker --execute="Cart::truncate();"

# Réinitialiser les stocks
php artisan tinker --execute="ProductCBD::query()->update(['stock' => 100]);"
```

### **Vérification des données :**
```bash
php artisan tinker --execute="
echo 'Users: ' . User::count() . PHP_EOL;
echo 'Products: ' . ProductCBD::count() . PHP_EOL;
echo 'Orders: ' . Order::count() . PHP_EOL;
echo 'Cart items: ' . Cart::count() . PHP_EOL;
"
```

---

## **💡 Conseils pour le développement**

### **🎯 Tests de performance :**
- Utilisez `--full` pour tester avec beaucoup de données
- Testez la pagination avec 200+ produits
- Vérifiez les temps de réponse avec des paniers pleins

### **🎨 Tests d'interface :**
- Testez avec des noms de produits longs/courts
- Vérifiez l'affichage des stocks faibles
- Testez les différents statuts de commandes

### **🔐 Tests de sécurité :**
- Connectez-vous avec différents types d'utilisateurs
- Testez les permissions admin vs client
- Vérifiez l'accès aux données sensibles

### **📱 Tests responsive :**
- Utilisez les images simulées pour tester l'affichage
- Vérifiez les listes longues sur mobile
- Testez les formulaires avec vraies données

---

## **🚨 Limitations et notes**

### **⚠️ Données factices :**
- Les emails de fournisseurs sont fictifs
- Les numéros de téléphone sont générés
- Les adresses sont aléatoires
- Les fichiers d'analyse n'existent pas physiquement

### **🔄 Régénération :**
- Utilisez `--fresh` uniquement si nécessaire
- Le mode `--minimal` est plus rapide pour les tests
- Les IDs peuvent changer entre les générations

### **📊 Performance :**
- Le mode `--full` peut prendre 1-2 minutes
- Évitez de régénérer en production
- Préférez les ajouts ponctuels avec Tinker

---

**✅ Système prêt pour le développement frontend !**

Toutes les données sont cohérentes et permettent de tester tous les aspects de l'interface utilisateur sans limitation.
