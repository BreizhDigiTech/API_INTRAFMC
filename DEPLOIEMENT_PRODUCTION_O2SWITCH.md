# 🚀 GUIDE DE DÉPLOIEMENT EN PRODUCTION - O2SWITCH
## API INTRAFMC - Version Production

---

## 📋 PRÉREQUIS

### ✅ Vérifications avant déploiement
- [ ] Compte O2SWITCH actif avec hébergement web
- [ ] Base de données MySQL disponible
- [ ] Nom de domaine configuré
- [ ] Accès FTP/SFTP ou panel de gestion
- [ ] PHP 8.1+ activé sur l'hébergement

### 🛠️ Outils nécessaires
- **FTP Client** : FileZilla, WinSCP, ou panel O2SWITCH
- **Terminal SSH** : Si disponible sur votre forfait O2SWITCH
- **Navigateur** : Pour tester les API GraphQL

---

## 🗂️ ÉTAPE 1 : PRÉPARATION DES FICHIERS

### 1.1 Récupération du code source
```bash
# Cloner la branche production
git clone -b production https://github.com/BreizhDigiTech/API_INTRAFMC.git
cd API_INTRAFMC
```

### 1.2 Optimisation locale (optionnel)
```bash
# Installer les dépendances pour la production
composer install --no-dev --optimize-autoloader

# Supprimer les fichiers inutiles
rm -rf tests/ .git/ .gitignore README.md
```

---

## 🌐 ÉTAPE 2 : CONFIGURATION O2SWITCH

### 2.1 Connexion au panel O2SWITCH
1. Connectez-vous à votre **panel de gestion O2SWITCH**
2. Allez dans **"Bases de données MySQL"**
3. Créez une nouvelle base de données :
   - **Nom** : `intrafmc_prod`
   - **Utilisateur** : `intrafmc_user`
   - **Mot de passe** : Générez un mot de passe sécurisé

### 2.2 Configuration du domaine
1. Dans **"Domaines et sous-domaines"**
2. Pointez votre domaine vers le dossier `/public` de l'application
3. Activez **HTTPS/SSL** (Let's Encrypt disponible)

### 2.3 Configuration PHP
1. Allez dans **"Configuration PHP"**
2. Sélectionnez **PHP 8.1** ou supérieur
3. Activez les extensions :
   - `mbstring`
   - `openssl`
   - `PDO`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `json`
   - `bcmath`
   - `gd`

---

## 📁 ÉTAPE 3 : UPLOAD DES FICHIERS

### 3.1 Structure des dossiers sur O2SWITCH
```
votre-domaine.com/
├── www/                    # Dossier racine web
│   ├── api/               # Votre application Laravel
│   │   ├── app/
│   │   ├── bootstrap/
│   │   ├── config/
│   │   ├── database/
│   │   ├── public/        # Point d'entrée web
│   │   ├── resources/
│   │   ├── routes/
│   │   ├── storage/
│   │   └── vendor/
│   └── public_html -> api/public  # Lien symbolique
```

### 3.2 Upload via FTP
1. **Connectez-vous en FTP** avec les identifiants O2SWITCH
2. **Créez le dossier** `api` dans `www/`
3. **Uploadez tous les fichiers** dans `www/api/`
4. **Configurez le point d'entrée** :
   - Supprimez le contenu de `public_html/`
   - Créez un lien ou copiez le contenu de `api/public/` vers `public_html/`

---

## ⚙️ ÉTAPE 4 : CONFIGURATION ENVIRONNEMENT

### 4.1 Fichier .env
Créez le fichier `.env` dans le dossier racine avec le contenu suivant :

```env
# ===========================================
# CONFIGURATION PRODUCTION API INTRAFMC
# ===========================================

APP_NAME="API INTRAFMC"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Europe/Paris
APP_URL=https://votre-domaine.com

# Base de données MySQL (O2SWITCH)
DB_CONNECTION=mysql
DB_HOST=votre-host.mysql.db
DB_PORT=3306
DB_DATABASE=intrafmc_prod
DB_USERNAME=intrafmc_user
DB_PASSWORD=votre_mot_de_passe_bdd

# Cache et Session
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (O2SWITCH SMTP)
MAIL_MAILER=smtp
MAIL_HOST=pro1.mail.ovh.net
MAIL_PORT=587
MAIL_USERNAME=votre-email@votre-domaine.com
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@votre-domaine.com
MAIL_FROM_NAME="${APP_NAME}"

# JWT Configuration
JWT_SECRET=
JWT_TTL=60
JWT_REFRESH_TTL=20160

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://votre-frontend.com
CORS_ALLOWED_METHODS="GET,POST,PUT,DELETE,OPTIONS"
CORS_ALLOWED_HEADERS="Content-Type,Authorization,X-Requested-With"

# GraphQL Configuration
GRAPHQL_PLAYGROUND_ENABLED=false
LIGHTHOUSE_CACHE_ENABLED=true

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Asset URL
ASSET_URL=https://votre-domaine.com

# Sécurité
SECURE_HEADERS=true
HTTPS_ONLY=true
```

### 4.2 Permissions des dossiers
```bash
# Via SSH ou panel de fichiers
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/product_images/
chmod -R 755 public/product_analysis/
```

---

## 🚀 ÉTAPE 5 : INITIALISATION DE L'APPLICATION

### 5.1 Via SSH (si disponible)
```bash
# Aller dans le dossier de l'application
cd /home/votre-compte/www/api

# Générer la clé d'application
php artisan key:generate

# Générer la clé JWT
php artisan jwt:secret

# Lancer les migrations
php artisan migrate --force

# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan lighthouse:cache
```

### 5.2 Via panel web (alternative)
Si SSH n'est pas disponible, créez un fichier `install.php` temporaire :

```php
<?php
// install.php - À supprimer après utilisation

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "🔑 Génération de la clé d'application...\n";
$kernel->call('key:generate', ['--force' => true]);

echo "🗄️ Migration de la base de données...\n";
$kernel->call('migrate', ['--force' => true]);

echo "⚡ Cache de configuration...\n";
$kernel->call('config:cache');

echo "✅ Installation terminée !\n";
echo "🗑️ N'oubliez pas de supprimer ce fichier install.php\n";
?>
```

Accédez à `https://votre-domaine.com/install.php` puis supprimez le fichier.

---

## 🗄️ ÉTAPE 6 : CONFIGURATION BASE DE DONNÉES

### 6.1 Import des données initiales
Si vous avez des données à importer :

```sql
-- Connexion à votre BDD via phpMyAdmin O2SWITCH
-- Import du fichier SQL de sauvegarde si nécessaire

-- Création d'un utilisateur admin
INSERT INTO users (name, email, email_verified_at, password, is_admin, is_active, created_at, updated_at) 
VALUES (
    'Administrateur',
    'admin@votre-domaine.com',
    NOW(),
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    1,
    1,
    NOW(),
    NOW()
);
```

### 6.2 Configuration des catégories par défaut
```sql
-- Insérer des catégories de base
INSERT INTO categories (name, description, created_at, updated_at) VALUES
('Fleurs CBD', 'Fleurs de cannabis légal', NOW(), NOW()),
('Huiles CBD', 'Huiles et extraits', NOW(), NOW()),
('Cosmétiques', 'Produits cosmétiques au CBD', NOW(), NOW());
```

---

## 🔧 ÉTAPE 7 : OPTIMISATIONS PRODUCTION

### 7.1 Configuration serveur web

**Fichier .htaccess dans public/** (généré automatiquement par Laravel)
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

### 7.2 Optimisation PHP (php.ini ou .user.ini)
```ini
# Configuration recommandée
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 32M
post_max_size = 32M
max_input_vars = 3000

# OPcache (si disponible)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

---

## 🧪 ÉTAPE 8 : TESTS DE VALIDATION

### 8.1 Test de l'API GraphQL
Accédez à `https://votre-domaine.com/graphql` et testez :

```graphql
# Test de base
query {
  __schema {
    types {
      name
    }
  }
}
```

### 8.2 Test d'authentification
```graphql
mutation {
  login(email: "admin@votre-domaine.com", password: "password") {
    access_token
    token_type
    expires_in
  }
}
```

### 8.3 Test des produits
```graphql
query {
  productsCBD(first: 5) {
    data {
      id
      name
      price
      stock
    }
    paginatorInfo {
      count
      currentPage
      total
    }
  }
}
```

---

## 🔒 ÉTAPE 9 : SÉCURISATION

### 9.1 Sécurité des fichiers
- **Supprimez** les fichiers `install.php`, `.env.example`
- **Vérifiez** que `.env` n'est pas accessible via le web
- **Masquez** les erreurs PHP en production

### 9.2 Sauvegarde régulière
Configurez des sauvegardes automatiques via O2SWITCH :
- **Base de données** : Export quotidien
- **Fichiers** : Sauvegarde hebdomadaire
- **Images** : Sauvegarde du dossier `public/product_images/`

### 9.3 Monitoring
```bash
# Vérification des logs d'erreur
tail -f storage/logs/laravel.log

# Monitoring des performances
# Via les outils O2SWITCH ou services externes
```

---

## 📊 ÉTAPE 10 : MAINTENANCE

### 10.1 Commandes utiles
```bash
# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser après mise à jour
php artisan optimize
```

### 10.2 Mise à jour de l'application
```bash
# 1. Sauvegarde complète
# 2. Upload des nouveaux fichiers
# 3. Migration si nécessaire
php artisan migrate --force
# 4. Recache
php artisan optimize
```

---

## 🆘 DÉPANNAGE

### Problèmes courants

**1. Erreur 500 - Internal Server Error**
- Vérifiez les permissions (755 pour storage/)
- Vérifiez le fichier .env
- Consultez les logs dans `storage/logs/`

**2. Base de données inaccessible**
- Vérifiez les paramètres DB_ dans .env
- Testez la connexion MySQL via phpMyAdmin

**3. Images non affichées**
- Vérifiez les permissions du dossier `public/product_images/`
- Vérifiez l'URL de base dans .env

**4. API GraphQL non fonctionnelle**
- Vérifiez que le module mod_rewrite est activé
- Testez l'endpoint `/graphql`

### Support O2SWITCH
- **Ticket support** : Via le panel de gestion
- **Documentation** : https://faq.o2switch.fr/
- **Téléphone** : Support technique disponible

---

## 📞 CONTACTS & RESSOURCES

### Documentation technique
- **Laravel** : https://laravel.com/docs
- **Lighthouse GraphQL** : https://lighthouse-php.com/
- **JWT Auth** : https://github.com/tymondesigns/jwt-auth

### Maintenance et support
- **Repository GitHub** : BreizhDigiTech/API_INTRAFMC
- **Branche production** : `production`
- **Logs d'erreur** : `storage/logs/laravel.log`

---

## ✅ CHECKLIST DE DÉPLOIEMENT

- [ ] Code source uploadé dans `/www/api/`
- [ ] Base de données MySQL créée et configurée
- [ ] Fichier `.env` configuré avec les bonnes valeurs
- [ ] Permissions des dossiers configurées (755)
- [ ] Migrations de la base de données exécutées
- [ ] Cache de configuration généré
- [ ] HTTPS activé et certificat SSL installé
- [ ] Test des endpoints GraphQL réussi
- [ ] Utilisateur administrateur créé
- [ ] Sauvegardes configurées
- [ ] Monitoring en place

---

**🎉 Votre API INTRAFMC est maintenant en production !**

L'application est accessible via votre domaine et prête à recevoir les requêtes de vos applications frontend.
