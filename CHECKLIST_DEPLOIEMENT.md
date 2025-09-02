# ✅ CHECKLIST DÉPLOIEMENT PRODUCTION
## API INTRAFMC - O2SWITCH

---

## 🔧 Préparation (Terminé)

- [x] **Code nettoyé** - Fichiers de test supprimés
- [x] **Branche production** - Code isolé de develop
- [x] **Configuration optimisée** - .env.example adapté O2SWITCH
- [x] **Sécurité renforcée** - Headers, HTTPS, debug off
- [x] **Documentation complète** - Guides et références
- [x] **Modules GraphQL** - Tous fonctionnels et testés
- [x] **.htaccess optimisé** - Compression, cache, sécurité
- [x] **Scripts utilitaires** - Déploiement et vérification

---

## 🚀 Déploiement O2SWITCH

### Étape 1: Récupération du code
- [ ] Se connecter au panel O2SWITCH
- [ ] Télécharger la branche `production` depuis GitHub
- [ ] Extraire dans un dossier temporaire local

### Étape 2: Configuration base de données
- [ ] Créer une base MySQL dans le panel O2SWITCH
  - [ ] Nom: `intrafmc_prod`
  - [ ] Utilisateur: `intrafmc_user`
  - [ ] Mot de passe sécurisé noté
- [ ] Noter les informations de connexion (host, port)

### Étape 3: Configuration domaine
- [ ] Configurer le domaine pour pointer vers le bon dossier
- [ ] Activer HTTPS/SSL (Let's Encrypt)
- [ ] Vérifier la version PHP (8.1+ requis)

### Étape 4: Upload des fichiers
- [ ] Créer le dossier `/www/api/` sur O2SWITCH
- [ ] Uploader tous les fichiers via FTP/SFTP
- [ ] Configurer les permissions :
  - [ ] `chmod 755 storage/`
  - [ ] `chmod 755 bootstrap/cache/`
  - [ ] `chmod 755 public/product_images/`
  - [ ] `chmod 755 public/product_analysis/`

### Étape 5: Configuration environnement
- [ ] Copier `.env.example` vers `.env`
- [ ] Configurer `.env` avec vos paramètres :
  - [ ] `APP_URL=https://votre-domaine.com`
  - [ ] `DB_HOST=votre-host.mysql.db`
  - [ ] `DB_DATABASE=intrafmc_prod`
  - [ ] `DB_USERNAME=intrafmc_user`
  - [ ] `DB_PASSWORD=votre_password`
  - [ ] `MAIL_*` avec vos paramètres SMTP
  - [ ] `CORS_ALLOWED_ORIGINS=https://votre-frontend.com`

### Étape 6: Initialisation
- [ ] Accéder à `https://votre-domaine.com/check-production.php`
- [ ] Vérifier que toutes les checks sont ✅
- [ ] Si SSH disponible, exécuter :
  ```bash
  php artisan key:generate
  php artisan jwt:secret
  php artisan migrate --force
  php artisan optimize
  ```
- [ ] Sinon, créer et exécuter le script `install.php` temporaire
- [ ] **Supprimer** `check-production.php` et `install.php` après usage

---

## 🧪 Tests de validation

### Tests techniques
- [ ] Accéder à `https://votre-domaine.com` → Page d'accueil Laravel
- [ ] Tester `https://votre-domaine.com/graphql` → Endpoint GraphQL actif
- [ ] Vérifier HTTPS → Certificat SSL valide
- [ ] Tester les redirections → Pas d'erreurs 404/500

### Tests API GraphQL
- [ ] **Test de base** :
  ```graphql
  query { __schema { types { name } } }
  ```
- [ ] **Test authentification** :
  ```graphql
  mutation { login(email: "admin@example.com", password: "password") { access_token } }
  ```
- [ ] **Test produits** :
  ```graphql
  query { productsCBD(first: 5) { data { id name price } } }
  ```

### Tests fonctionnels
- [ ] Créer un compte administrateur dans la BDD
- [ ] Se connecter via GraphQL
- [ ] Créer un produit de test
- [ ] Tester l'upload d'images
- [ ] Vérifier les permissions utilisateur/admin

---

## 🔒 Sécurisation finale

### Fichiers sensibles
- [ ] Vérifier que `.env` n'est pas accessible via web
- [ ] Supprimer tous les fichiers temporaires (`*.php` utilitaires)
- [ ] Vérifier que `.git` n'est pas uploadé

### Configuration sécurité
- [ ] Vérifier les headers de sécurité (F12 → Network)
- [ ] Tester la protection CSRF
- [ ] Valider les CORS avec votre frontend
- [ ] Vérifier les logs d'erreur dans `storage/logs/`

### Monitoring
- [ ] Configurer la surveillance uptime (UptimeRobot, Pingdom)
- [ ] Mettre en place les sauvegardes automatiques O2SWITCH
- [ ] Tester la restauration de sauvegarde
- [ ] Noter les accès admin et mots de passe dans un gestionnaire sécurisé

---

## 📊 Performance et optimisation

### Cache
- [ ] Vérifier que le cache de config est généré
- [ ] Tester la vitesse de réponse des API (< 200ms)
- [ ] Optimiser les requêtes GraphQL si nécessaire

### Base de données
- [ ] Vérifier les index sur les colonnes critiques
- [ ] Tester les performances avec des données réelles
- [ ] Planifier la maintenance BDD

---

## 📚 Documentation et formation

### Documentation équipe
- [ ] Partager l'URL de l'API de production
- [ ] Distribuer la documentation API
- [ ] Former l'équipe frontend aux nouveaux endpoints

### Maintenance
- [ ] Planifier les mises à jour de sécurité
- [ ] Documenter la procédure de mise à jour
- [ ] Prévoir les sauvegardes avant modifications

---

## 🆘 Plan de repli

### En cas de problème
- [ ] **Accès logs** : `storage/logs/laravel.log`
- [ ] **Support O2SWITCH** : Panel → Tickets
- [ ] **Rollback** : Restaurer depuis sauvegarde
- [ ] **Contacts urgence** : Noter les contacts techniques

### Numéros utiles
- [ ] Support O2SWITCH : [Numéro support]
- [ ] Développeur principal : [Contact]
- [ ] Admin système : [Contact]

---

## 🎉 Mise en production réussie !

### Actions post-déploiement
- [ ] Annoncer la mise en production à l'équipe
- [ ] Mettre à jour la documentation interne
- [ ] Planifier le monitoring des 48 premières heures
- [ ] Programmer la première maintenance

### Métriques à surveiller
- [ ] Temps de réponse des API
- [ ] Taux d'erreur 500
- [ ] Connexions simultanées
- [ ] Utilisation de la base de données

---

**Date de déploiement** : _______________  
**Responsable déploiement** : _______________  
**Version déployée** : production (commit: 6b76a73)  
**URL production** : _______________

**✅ Application déployée avec succès !**
