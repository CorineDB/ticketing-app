# Guide de Déploiement Backend sur Railway.app

Ce guide explique comment déployer le **backend Laravel** sur Railway.app avec **$5 de crédit gratuit par mois**.

## ✅ Pourquoi Railway pour le Backend ?

- ✅ **$5 de crédit GRATUIT par mois** (permanent)
- ✅ **PostgreSQL inclus** sans frais supplémentaires
- ✅ **Pas de mise en veille** - toujours actif
- ✅ **Queue worker inclus** sans frais
- ✅ **Déploiement simple** - détection automatique
- ✅ **Variables d'environnement** auto-configurées
- ✅ **Logs en temps réel**
- ✅ **Pas de limite de 30 jours** comme Render

## 💰 Estimation de Coût

Avec le crédit gratuit de $5/mois :
- Backend API : ~$2/mois
- PostgreSQL : ~$1/mois
- Queue Worker : ~$1/mois
- **Total : ~$4/mois = DANS LE CRÉDIT GRATUIT** ✅

Vous restez donc **100% gratuit** ! 🎉

---

## 🚀 Méthode 1 : Déploiement via Dashboard (Recommandé)

### Étape 1 : Créer un Compte Railway

1. Aller sur https://railway.app/
2. Cliquer "Start a New Project"
3. Se connecter avec GitHub
4. Railway vous donne **$5 de crédit gratuit par mois**

### Étape 2 : Créer un Nouveau Projet

1. Cliquer "New Project"
2. Sélectionner "Deploy from GitHub repo"
3. Connecter votre compte GitHub
4. Sélectionner le repository `ticketing-app`
5. Railway détecte automatiquement le Dockerfile

### Étape 3 : Configurer le Service Backend

Railway va automatiquement :
- ✅ Détecter le `Dockerfile` dans `ticketing-api-rest-app/`
- ✅ Builder l'image Docker
- ✅ Déployer le service

**Configuration à vérifier** :

1. Cliquer sur le service déployé
2. Aller dans "Settings"
3. Vérifier :
   ```
   Root Directory: ticketing-api-rest-app
   Dockerfile Path: Dockerfile
   ```

### Étape 4 : Ajouter PostgreSQL

1. Dans votre projet, cliquer "New"
2. Sélectionner "Database" → "Add PostgreSQL"
3. Railway crée automatiquement la base de données
4. Les variables d'environnement sont **automatiquement configurées** :
   - `DATABASE_URL`
   - `PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`

### Étape 5 : Connecter la Base de Données au Backend

1. Cliquer sur le service backend
2. Aller dans "Variables"
3. Ajouter une "Reference" vers la base de données PostgreSQL

Railway configure automatiquement :
```bash
DATABASE_URL=postgresql://user:password@host:port/database
```

Ou vous pouvez utiliser les variables individuelles :
```bash
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}
```

### Étape 6 : Configurer les Variables d'Environnement

Dans "Variables", ajouter toutes les variables de votre `.env` :

**Variables Requises** :

```bash
# Application
APP_NAME=Plateforme de gestion des tickets
APP_ENV=production
APP_KEY=base64:votre-clé-générée
APP_DEBUG=false
APP_URL=https://votre-backend.up.railway.app

# Base de données (auto-configurées par Railway)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=votre-email@exemple.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@exemple.com
MAIL_FROM_NAME=Plateforme de gestion des tickets

# SMS Provider
SMS_PROVIDER=custom_api
SMS_PROVIDER_URL=https://api.e-mc.co/v3
SMS_PROVIDER_API_ACCOUNT_ID=votre-account-id
SMS_PROVIDER_API_ACCOUNT_PASSWORD=votre-password
SMS_PROVIDER_API_KEY=votre-api-key

# Alerts
SMS_ALERT_THRESHOLD=1000
ALERT_EMAIL=email1@exemple.com,email2@exemple.com
ALERT_SMS=phone1,phone2

# Laravel Passport
PASSPORT_GRANT_ACCESS_CLIENT_ID=votre-client-id
PASSPORT_GRANT_ACCESS_CLIENT_SECRET=votre-client-secret
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=votre-personal-id
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=votre-personal-secret

# CinetPay
CINETPAY_API_KEY=votre-api-key
CINETPAY_SITE_ID=votre-site-id
CINETPAY_SECRET_KEY=votre-secret-key
CINETPAY_API_URL=https://api-checkout.cinetpay.com/v2/payment
CINETPAY_MODE=production
CINETPAY_API_CHECK=https://api-checkout.cinetpay.com/v2/payment/check

# FedaPay
FEDAPAY_PUBLIC_KEY=pk_live_xxxxx
FEDAPAY_SECRET_KEY=sk_live_xxxxx
FEDAPAY_WEBHOOK_SECRET=wh_live_xxxxx
FEDAPAY_ENVIRONMENT=live
FEDAPAY_CURRENCY=XOF

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Other
SUBSCRIPTION_PRICE_PER_YEAR=50000
SUBSCRIPTION_CURRENCY=XOF
```

### Étape 7 : Générer APP_KEY

Railway permet d'exécuter des commandes :

1. Aller dans "Settings" → "Deploy"
2. Ou utiliser Railway CLI :

```bash
railway run php artisan key:generate --show
# Copier la clé générée dans APP_KEY
```

### Étape 8 : Ajouter un Queue Worker (Optionnel mais Recommandé)

Pour traiter les jobs en arrière-plan :

1. Cliquer "New" dans votre projet
2. Sélectionner "Empty Service"
3. Nommer : `ticketing-queue`
4. Connecter au même repository
5. Dans "Settings" :
   ```
   Root Directory: ticketing-api-rest-app
   Dockerfile Path: Dockerfile.worker
   ```

6. Ajouter les mêmes variables d'environnement que le backend

### Étape 9 : Déployer

1. Railway déploie automatiquement
2. Suivre les logs dans "Deployments"
3. Attendre 3-5 minutes

### Étape 10 : Obtenir l'URL du Backend

1. Aller dans le service backend
2. Onglet "Settings" → "Networking"
3. Cliquer "Generate Domain"
4. Vous obtenez : `https://votre-backend.up.railway.app`

### Étape 11 : Tester l'API

```bash
curl https://votre-backend.up.railway.app/api/health

# Devrait retourner :
# {
#   "status": "healthy",
#   "database": "connected",
#   "timestamp": "..."
# }
```

---

## 🚀 Méthode 2 : Déploiement via Railway CLI (Plus Rapide)

### Étape 1 : Installer Railway CLI

```bash
# macOS / Linux
curl -fsSL https://railway.app/install.sh | sh

# Windows (PowerShell)
iwr https://railway.app/install.ps1 | iex

# Ou via npm
npm install -g @railway/cli
```

### Étape 2 : Se Connecter

```bash
railway login
# Ouvre le navigateur pour authentification
```

### Étape 3 : Initialiser le Projet

```bash
cd ticketing-api-rest-app
railway init

# Sélectionner :
# - Create a new project
# - Nom : ticketing-app
```

### Étape 4 : Ajouter PostgreSQL

```bash
railway add --database postgres
```

### Étape 5 : Configurer les Variables

Créer un fichier `.railway.env` dans `ticketing-api-rest-app/` :

```bash
# Application
APP_NAME=Plateforme de gestion des tickets
APP_ENV=production
APP_DEBUG=false

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=votre-email
MAIL_PASSWORD=votre-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email
MAIL_FROM_NAME=Plateforme de gestion des tickets

# SMS
SMS_PROVIDER=custom_api
SMS_PROVIDER_URL=https://api.e-mc.co/v3
SMS_PROVIDER_API_ACCOUNT_ID=votre-id
SMS_PROVIDER_API_ACCOUNT_PASSWORD=votre-password
SMS_PROVIDER_API_KEY=votre-key

# Alerts
ALERT_EMAIL=email1,email2
ALERT_SMS=phone1,phone2

# Payment Gateways
CINETPAY_API_KEY=votre-key
CINETPAY_SITE_ID=votre-site-id
CINETPAY_SECRET_KEY=votre-secret
FEDAPAY_PUBLIC_KEY=pk_live_xxx
FEDAPAY_SECRET_KEY=sk_live_xxx
FEDAPAY_WEBHOOK_SECRET=wh_live_xxx

# Session & Queue
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Puis :

```bash
railway variables --set-from-file .railway.env
```

### Étape 6 : Générer APP_KEY

```bash
railway run php artisan key:generate
```

### Étape 7 : Déployer

```bash
railway up

# Railway :
# - Détecte le Dockerfile
# - Build l'image
# - Déploie le service
```

### Étape 8 : Obtenir l'URL

```bash
railway domain

# Génère une URL : https://votre-backend.up.railway.app
```

### Étape 9 : Voir les Logs

```bash
railway logs
```

---

## 🔧 Configuration du Frontend

Une fois le backend déployé sur Railway, mettre à jour le frontend :

### Option 1 : Frontend sur Render

Dans Render Dashboard → `ticketing-frontend` → Environment :

```bash
VITE_API_URL=https://votre-backend.up.railway.app/api
```

### Option 2 : Frontend sur Vercel

```bash
vercel env add VITE_API_URL production
# Entrer : https://votre-backend.up.railway.app/api

vercel --prod
```

---

## 🔒 Configuration CORS

Le backend Railway doit accepter les requêtes du frontend.

**Dans `ticketing-api-rest-app/config/cors.php`** :

```php
'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:4173',
    'https://ticketing-frontend.onrender.com',  // Frontend Render
    'https://ticketing-app.vercel.app',          // Frontend Vercel
    'https://*.vercel.app',                      // Tous les domaines Vercel
],

'allowed_origins_patterns' => [
    '/^https:\/\/.*\.vercel\.app$/',
],
```

Commit et push :

```bash
git add config/cors.php
git commit -m "Update CORS for Railway backend"
git push
```

Railway redéploie automatiquement.

---

## 🔄 Déploiement Automatique

Railway déploie automatiquement à chaque `git push` sur la branche configurée.

**Désactiver l'auto-déploiement** (optionnel) :

1. Settings → "Deployments"
2. Désactiver "Automatic Deployments"
3. Déployer manuellement avec `railway up`

---

## 📊 Monitoring

### Voir les Logs en Temps Réel

```bash
# Via CLI
railway logs --follow

# Via Dashboard
# Aller dans "Observability" → "Logs"
```

### Métriques

Railway Dashboard → "Metrics" :
- CPU usage
- Memory usage
- Network traffic
- Coût estimé

### Alertes de Crédit

Railway envoie un email quand :
- 50% du crédit utilisé
- 80% du crédit utilisé
- 100% du crédit utilisé

---

## 🐛 Dépannage

### Le déploiement échoue

**Erreur** : Build failed

**Solutions** :
```bash
# Tester le build Docker localement
cd ticketing-api-rest-app
docker build -t test .

# Vérifier les logs Railway
railway logs
```

### Base de données non accessible

**Erreur** : `SQLSTATE[08006] Connection refused`

**Solutions** :
1. Vérifier que PostgreSQL est ajouté au projet
2. Vérifier les variables `DB_*`
3. Utiliser les références Railway :
   ```bash
   DB_HOST=${{Postgres.PGHOST}}
   ```

### Migrations ne s'exécutent pas

**Solution** :
```bash
# Exécuter manuellement
railway run php artisan migrate --force

# Ou se connecter au shell
railway shell
php artisan migrate --force
```

### Variables d'environnement non prises en compte

**Solutions** :
1. Vérifier dans Dashboard → Variables
2. Redéployer manuellement :
   ```bash
   railway up --detach
   ```

### Le site ne répond pas

**Vérifier** :
```bash
# Status du service
railway status

# Logs d'erreur
railway logs --error
```

---

## 💡 Commandes Utiles

```bash
# Se connecter au shell du container
railway shell

# Exécuter une commande
railway run php artisan tinker

# Voir les variables
railway variables

# Redémarrer le service
railway restart

# Voir l'utilisation
railway usage

# Ouvrir le dashboard
railway open
```

---

## 🎯 Architecture Finale Recommandée

```
Frontend (Render/Vercel)  →  Backend (Railway)  →  Database (Railway)
https://frontend.render.com   https://api.railway.app  PostgreSQL Railway
     GRATUIT                    $4/mois (~80% crédit)   Inclus
```

**Coût Total** : $0/mois (dans le crédit gratuit de $5) ✅

---

## 📝 Checklist de Déploiement Backend Railway

- [ ] Compte Railway créé
- [ ] Projet créé depuis GitHub
- [ ] PostgreSQL ajouté
- [ ] Variables d'environnement configurées
- [ ] APP_KEY généré
- [ ] Backend déployé et accessible
- [ ] Health check retourne "healthy"
- [ ] Queue worker déployé (optionnel)
- [ ] URL backend notée
- [ ] Frontend mis à jour avec l'URL backend
- [ ] CORS configuré
- [ ] Webhooks FedaPay configurés
- [ ] Tests de paiement effectués

---

## 🚀 Prochaines Étapes

1. ✅ Backend déployé sur Railway
2. ✅ Frontend déployé sur Render/Vercel
3. 🔜 Connecter frontend et backend
4. 🔜 Configurer webhooks FedaPay
5. 🔜 Tester le flux complet de ticketing
6. 🔜 Configurer domaine personnalisé (optionnel)

---

## 📚 Ressources

- [Documentation Railway](https://docs.railway.app/)
- [Railway CLI Reference](https://docs.railway.app/develop/cli)
- [Railway Templates](https://railway.app/templates)
- [Laravel on Railway Guide](https://docs.railway.app/guides/laravel)

---

## 💰 Gestion du Crédit

### Optimiser les Coûts

1. **Utiliser un seul environnement** (production)
2. **Surveiller l'usage** dans Dashboard → Usage
3. **Désactiver les services non utilisés**
4. **Utiliser queue:work au lieu de queue:listen**

### Si vous dépassez $5/mois

Railway vous facturera uniquement le dépassement :
- $5/mois de dépassement = ~$5 de facture
- Carte bancaire requise pour continuer

### Alternative Gratuite 100%

Si vous voulez rester 100% gratuit :
- Frontend : Render/Vercel (gratuit)
- Backend : Fly.io (plan gratuit)
- Database : Neon.tech (PostgreSQL gratuit)

---

**Railway est la meilleure option pour votre backend Laravel** avec un excellent équilibre entre facilité, performance et coût ! 🚀
