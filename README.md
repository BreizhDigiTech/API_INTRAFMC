# API GraphQL - Intra FMC

Une API GraphQL construite avec Laravel et Lighthouse pour la gestion interne des tâches quotidiennes des équipes de la SARL FMC.

## 🚀 Technologies

- **Laravel 12.x** - Framework PHP
- **Lighthouse GraphQL** - Serveur GraphQL pour Laravel
- **JWT Auth** - Authentification par tokens JWT
- **PHP 8.2+** - Version PHP requise

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- Base de données (MySQL/PostgreSQL/SQLite)

## 🛠️ Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/BreizhDigiTech/API_INTRAFMC.git
   cd API_INTRAFMC
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration de l'environnement**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Configurer la base de données**
   - Modifier le fichier `.env` avec vos paramètres de base de données
   ```env
   APP_NAME="Intra FMC API"
   APP_URL=http://localhost:8000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=intra_fmc
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   # Configuration JWT
   JWT_SECRET=your_jwt_secret_key
   JWT_TTL=60
   ```

5. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

6. **Peupler la base de données (optionnel)**
   ```bash
   php artisan db:seed
   ```

7. **Générer la clé JWT**
   ```bash
   php artisan jwt:secret
   ```

## 🏃‍♂️ Démarrage

### Développement

1. **Démarrer le serveur de développement**
   ```bash
   php artisan serve
   ```

### Production

1. **Optimiser l'application**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 🔍 GraphQL Playground

L'API GraphQL est accessible via GraphQL Playground à l'adresse :
```
http://localhost:8000/graphql-playground
```

**Endpoints disponibles :**
- GraphQL API : `http://localhost:8000/graphql`
- GraphQL Playground : `http://localhost:8000/graphql-playground`

## 📚 Modules disponibles

L'API est organisée en modules :

- **Auth** - Authentification des utilisateurs
- **Register** - Inscription des utilisateurs
- **User** - Gestion des utilisateurs
- **Product_CBD** - Gestion des produits CBD
- **Arrival** - Gestion des arrivages
- **Category** - Gestion des catégories
- **Supplier** - Gestion des fournisseurs
- **Cart** - Gestion du panier
- **Order** - Gestion des commandes

## 🔐 Authentification

L'API utilise JWT (JSON Web Tokens) pour l'authentification. Incluez le token dans l'en-tête Authorization :

```
Authorization: Bearer your-jwt-token
```

## 🌐 Configuration CORS

L'API est configurée pour accepter les requêtes cross-origin des domaines autorisés. 

**Domaines autorisés par défaut :**
- `http://localhost:3000` (React, Next.js)
- `http://localhost:3001` 
- `http://localhost:8080` (Vue.js)

**Pour ajouter un nouveau domaine :**
1. Modifiez le fichier `config/cors.php`
2. Ajoutez votre domaine dans le tableau `allowed_origins`
3. Ou configurez les variables d'environnement dans `.env`

```env
CORS_ALLOWED_ORIGINS="http://localhost:3000,https://votre-frontend.com"
```

## 📝 Exemples de requêtes

### Connexion
```graphql
mutation {
  login(email: "user@example.com", password: "password") {
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

### Récupérer les produits
```graphql
query {
  products {
    id
    name
    description
    price
    category {
      id
      name
    }
  }
}
```

## 🧪 Tests

Exécuter les tests :
```bash
php artisan test
```

Exécuter les tests avec couverture :
```bash
php artisan test --coverage
```

## 🔧 Dépannage

### Problèmes courants

1. **Erreur JWT Secret**
   ```bash
   php artisan jwt:secret --force
   ```

2. **Problème de permissions (Linux/Mac)**
   ```bash
   sudo chmod -R 775 storage bootstrap/cache
   ```

3. **Problème CORS avec le frontend**
   ```bash
   # Vérifier la configuration CORS dans config/cors.php
   # Ajouter votre domaine frontend aux origines autorisées
   ```

4. **Cache problématique**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

5. **Base de données non accessible**
   - Vérifiez que votre serveur de base de données est démarré
   - Vérifiez les paramètres de connexion dans `.env`

## ⚠️ Points d'amélioration recommandés

1. **Sécurité**
   - [x] **CORS configuré** - Configuration CORS ajoutée pour les requêtes cross-origin
   - [x] **Normalisation des fins de ligne** - Configuration `.gitattributes` et `.editorconfig` ajoutées
   - [ ] Ajouter une validation des entrées plus stricte
   - [ ] Implémenter la limitation de taux (rate limiting)
   - [ ] Ajouter la validation des tokens JWT

2. **Performance**
   - [ ] Implémenter la pagination pour les grandes listes
   - [ ] Ajouter la mise en cache Redis
   - [ ] Optimiser les requêtes N+1 avec DataLoader

3. **Documentation**
   - [ ] Compléter les commentaires dans le schema GraphQL
   - [ ] Ajouter des exemples de requêtes complexes
   - [ ] Documenter les erreurs possibles

4. **Tests**
   - [ ] Ajouter des tests unitaires pour chaque module
   - [ ] Implémenter des tests d'intégration GraphQL
   - [ ] Ajouter des tests de performance

5. **DevOps**
   - [ ] Configurer les environnements de staging/production
   - [ ] Ajouter des scripts de déploiement
   - [ ] Configurer la surveillance et les logs

## 📖 Documentation

- **Schema GraphQL** : Accessible via GraphQL Playground
- **Laravel Documentation** : https://laravel.com/docs
- **Lighthouse Documentation** : https://lighthouse-php.com/

## 🔧 Scripts disponibles

- `composer install` - Installer les dépendances PHP
- `php artisan serve` - Démarrer le serveur de développement
- `php artisan migrate` - Exécuter les migrations
- `php artisan test` - Exécuter les tests

## 📁 Structure du projet

```
app/
├── Exceptions/         # Exceptions personnalisées
├── Helpers/           # Classes d'aide
├── Models/            # Modèles Eloquent
├── Modules/           # Modules GraphQL
│   ├── Auth/
│   ├── User/
│   ├── Product_CBD/
│   └── ...
└── Policies/          # Politiques d'autorisation

graphql/
└── schema.graphql     # Schema GraphQL principal

database/
├── migrations/        # Migrations de base de données
└── seeders/          # Seeders
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commiter vos changements (`git commit -am 'Ajouter une nouvelle fonctionnalité'`)
4. Pusher vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT.

## 📝 Fichiers recommandés à créer

Pour améliorer la qualité du projet, considérez l'ajout de ces fichiers :

1. **CHANGELOG.md** - Historique des versions
2. **CONTRIBUTING.md** - Guide de contribution
3. **docker-compose.yml** - Configuration Docker
4. **Dockerfile** - Image Docker
5. **.env.testing** - Configuration pour les tests
6. **docs/** - Documentation détaillée de l'API

## 📁 Fichiers de configuration présents

Le projet inclut déjà plusieurs fichiers de configuration importants :

- `.gitattributes` - Normalisation des fins de ligne et configuration Git
- `.editorconfig` - Configuration de l'éditeur pour la cohérence du code
- `.env.example` - Exemple de configuration d'environnement
- `phpunit.xml` - Configuration des tests PHPUnit

## �📞 Support

Pour toute question ou support, contactez l'équipe de développement de la SARL FMC.
