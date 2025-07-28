# 🎉 Mutation UPDATE ORDER STATUS ajoutée avec succès !

## ✅ **Nouvelles fonctionnalités disponibles :**

### **Mutation pour changer le statut d'une commande (Admin uniquement) :**
```graphql
mutation UpdateOrderStatus($input: UpdateOrderStatusInput!) {
  updateOrderStatus(input: $input) {
    id
    total
    status
    created_at
    updated_at
    products {
      id
      name
      price
      pivot {
        quantity
        unit_price
      }
    }
    user {
      id
      name
      email
    }
  }
}
```

### **Variables :**
```json
{
  "input": {
    "id": "1",
    "status": "validated"
  }
}
```

### **Statuts disponibles :**
- `pending` - En attente
- `validated` - Validée
- `cancelled` - Annulée

## 🎯 **Exemples d'utilisation :**

### **1. Valider une commande :**
```json
{
  "input": {
    "id": "5",
    "status": "validated"
  }
}
```

### **2. Annuler une commande :**
```json
{
  "input": {
    "id": "12",
    "status": "cancelled"
  }
}
```

### **3. Remettre en attente :**
```json
{
  "input": {
    "id": "8",
    "status": "pending"
  }
}
```

## 🛡️ **Sécurité :**
- ✅ **Admin uniquement** peut utiliser cette mutation
- ❌ **Utilisateurs normaux** : Accès refusé
- ✅ Validation des statuts autorisés
- ✅ Vérification de l'existence de la commande

## 📋 **Headers requis :**
```http
POST /graphql
Content-Type: application/json
Authorization: Bearer <JWT_ADMIN_TOKEN>
```

## 🚀 **Prêt à utiliser !**
Vous pouvez maintenant gérer complètement le cycle de vie des commandes depuis votre API GraphQL.
