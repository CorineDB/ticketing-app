# Guide de Déploiement - Application de Ticketing

Guide complet pour déployer votre application de gestion de tickets.

---

## 🎯 Recommandation : Frontend (Render) + Backend (Railway)

**Architecture recommandée** :
```
Frontend (Render) → Backend (Railway) → PostgreSQL (Railway)
   GRATUIT ∞          ~$4/mois ($5 crédit gratuit)
```

**Coût total : $0/mois** ✅

---

## 📋 Table des Matières

1. [Déploiement Frontend (Render)](#1-déploiement-frontend-render)
2. [Déploiement Backend (Railway)](#2-déploiement-backend-railway)
3. [Configuration et Connexion](#3-configuration-et-connexion)
4. [Alternatives](#4-alternatives)

---

## 1. Déploiement Frontend (Render)

### Étape 1 : Créer un compte Render
1. Aller sur https://render.com/
2. Sign up avec GitHub

### Étape 2 : Déployer avec Blueprint
1. "New +" → "Blueprint"
2. Sélectionner votre repository
3. Branche : `claude/deploy-ren-environment-0139xhC4fcY4J1SJuqfrXYyK`
4. Render détecte automatiquement `render-frontend.yaml`
5. "Apply"

### Étape 3 : Configurer les variables
Dans "Environment" :
```bash
VITE_API_URL=http://localhost:8000/api  # Temporaire, à changer après backend
VITE_CINETPAY_API_KEY=votre-clé
VITE_CINETPAY_SITE_ID=votre-site-id
```

**Résultat** : Frontend accessible à `https://ticketing-frontend.onrender.com`

---

## 2. Déploiement Backend (Railway)

### Option A : Via Dashboard (Simple)

**Étape 1 : Créer un compte**
1. https://railway.app/ → Sign up avec GitHub
2. Vous recevez **$5 de crédit gratuit/mois**

**Étape 2 : Nouveau projet**
1. "New Project" → "Deploy from GitHub repo"
2. Sélectionner `ticketing-app`
3. Railway détecte le Dockerfile

**Étape 3 : Ajouter PostgreSQL**
1. Dans le projet, "New" → "Database" → "Add PostgreSQL"
2. Railway configure automatiquement les variables DB_*

**Étape 4 : Configurer les variables**

Copier `.railway.env.example` vers `.railway.env` et remplir avec vos valeurs.

Variables requises :
```bash
# Application
APP_NAME="Plateforme de gestion des tickets"
APP_ENV=production
APP_DEBUG=false

# Database (auto-configuré)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=votre-email
MAIL_PASSWORD=votre-password
MAIL_FROM_ADDRESS=votre-email

# SMS
SMS_PROVIDER_URL=https://api.e-mc.co/v3
SMS_PROVIDER_API_ACCOUNT_ID=votre-id
SMS_PROVIDER_API_KEY=votre-key

# Paiements
CINETPAY_API_KEY=votre-key
CINETPAY_SITE_ID=votre-site-id
CINETPAY_SECRET_KEY=votre-secret
FEDAPAY_PUBLIC_KEY=pk_live_xxx
FEDAPAY_SECRET_KEY=sk_live_xxx
FEDAPAY_WEBHOOK_SECRET=wh_live_xxx
FEDAPAY_ENVIRONMENT=live

# Session
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Étape 5 : Générer APP_KEY**
```bash
railway run php artisan key:generate --show
# Copier la clé dans APP_KEY
```

**Étape 6 : Déployer**
Railway déploie automatiquement. Attendre 3-5 minutes.

**Étape 7 : Obtenir l'URL**
Settings → Networking → "Generate Domain"
Résultat : `https://votre-backend.up.railway.app`

### Option B : Via CLI (Rapide)

```bash
# Installer
npm install -g @railway/cli

# Se connecter
railway login

# Déployer
cd ticketing-api-rest-app
railway init
railway add --database postgres
railway variables --set-from-file .railway.env
railway run php artisan key:generate
railway up
railway domain
```

---

## 3. Configuration et Connexion

### Connecter Frontend et Backend

**Dans Render Dashboard** :
1. Aller dans `ticketing-frontend`
2. Environment → Modifier `VITE_API_URL`
3. `VITE_API_URL = https://votre-backend.up.railway.app/api`
4. Save Changes

### Configurer CORS

**Dans `ticketing-api-rest-app/config/cors.php`** :
```php
'allowed_origins' => [
    'http://localhost:5173',
    'https://ticketing-frontend.onrender.com',
    'https://*.vercel.app',
],
```

Commit et push → Railway redéploie automatiquement.

### Configurer Webhooks

**FedaPay** :
1. Dashboard FedaPay → Webhooks
2. URL : `https://votre-backend.up.railway.app/api/webhooks/fedapay`

### Tester

```bash
# Health check
curl https://votre-backend.up.railway.app/api/health

# Frontend
# Ouvrir https://ticketing-frontend.onrender.com
```

---

## 4. Alternatives

### Alternative 1 : Frontend sur Vercel

**Plus rapide et meilleur CDN** :
```bash
cd ticketing-app
npm install -g vercel
vercel login
vercel --prod
```

Puis configurer `VITE_API_URL` dans Vercel Dashboard.

### Alternative 2 : Backend Local (Développement)

**Avec ngrok** :
```bash
# Terminal 1
cd ticketing-api-rest-app
php artisan serve

# Terminal 2
ngrok http 8000

# Mettre VITE_API_URL = https://abc123.ngrok.io/api dans Render
```

---

## 💰 Coûts

| Service | Coût |
|---------|------|
| Frontend (Render Static) | ✅ $0 (gratuit illimité) |
| Backend (Railway) | ✅ $0 (~$4 dans crédit $5) |
| PostgreSQL (Railway) | ✅ Inclus |
| **TOTAL** | **$0/mois** |

---

## ✅ Checklist

### Frontend
- [ ] Compte Render créé
- [ ] Blueprint déployé
- [ ] Variables configurées
- [ ] Site accessible

### Backend
- [ ] Compte Railway créé
- [ ] PostgreSQL ajouté
- [ ] Variables configurées
- [ ] API accessible (`/api/health`)

### Connexion
- [ ] Frontend → Backend URL configurée
- [ ] CORS configuré
- [ ] Webhooks configurés
- [ ] Tests effectués

---

## 🆘 Problèmes Courants

**Frontend ne se connecte pas au backend** :
- Vérifier `VITE_API_URL` (avec `/api` à la fin)
- Vérifier CORS dans Laravel
- Vider cache navigateur

**Backend ne démarre pas** :
```bash
railway logs
# Vérifier les erreurs
```

**Base de données non accessible** :
- Vérifier que PostgreSQL est ajouté au projet Railway
- Vérifier les variables `DB_*`

---

## 📚 Fichiers de Configuration

- **`render-frontend.yaml`** - Configuration frontend Render
- **`railway.json`** - Configuration backend Railway
- **`ticketing-api-rest-app/.railway.env.example`** - Template variables

---

## 🚀 Déploiement Rapide

**Temps total : ~10 minutes**

1. Frontend Render : 2 min
2. Backend Railway : 5 min
3. Connexion : 1 min
4. Webhooks : 2 min

**C'est tout !** 🎉
