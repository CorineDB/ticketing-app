# 🚀 Déploiement Backend Railway - Guide Ultra-Rapide

Déployez votre backend Laravel sur Railway en **5 minutes** avec $5 de crédit gratuit/mois.

## ⚡ Déploiement Express

### Méthode 1 : Via Dashboard (Plus Simple) - 5 minutes

```
1. Aller sur https://railway.app/
2. Sign up avec GitHub → Crédit $5/mois gratuit ✅
3. "New Project" → "Deploy from GitHub repo"
4. Sélectionner: ticketing-app
5. Railway détecte automatiquement le Dockerfile
6. "Add PostgreSQL" dans le projet
7. Configurer les variables d'environnement (voir ci-dessous)
8. Déployer ✅
```

### Méthode 2 : Via CLI (Plus Rapide) - 2 minutes

```bash
# 1. Installer Railway CLI
npm install -g @railway/cli

# 2. Se connecter
railway login

# 3. Initialiser
cd ticketing-api-rest-app
railway init

# 4. Ajouter PostgreSQL
railway add --database postgres

# 5. Configurer variables (voir section ci-dessous)
# Créer .railway.env puis:
railway variables --set-from-file .railway.env

# 6. Générer APP_KEY
railway run php artisan key:generate

# 7. Déployer
railway up

# 8. Obtenir l'URL
railway domain
```

**C'est tout !** 🎉

---

## 📝 Variables d'Environnement REQUISES

Créer `.railway.env` dans `ticketing-api-rest-app/` :

```bash
# Application
APP_NAME="Plateforme de gestion des tickets"
APP_ENV=production
APP_DEBUG=false

# Database (Auto-configuré par Railway)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Mail (Utiliser vos vraies valeurs)
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=votre-email@example.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre-email@example.com

# SMS (Utiliser vos vraies valeurs)
SMS_PROVIDER=custom_api
SMS_PROVIDER_URL=https://api.e-mc.co/v3
SMS_PROVIDER_API_ACCOUNT_ID=votre-account-id
SMS_PROVIDER_API_ACCOUNT_PASSWORD=votre-password
SMS_PROVIDER_API_KEY=votre-api-key

# Alerts
ALERT_EMAIL=email1@example.com,email2@example.com
ALERT_SMS=phone1,phone2

# CinetPay
CINETPAY_API_KEY=votre-api-key
CINETPAY_SITE_ID=votre-site-id
CINETPAY_SECRET_KEY=votre-secret-key
CINETPAY_MODE=production

# FedaPay
FEDAPAY_PUBLIC_KEY=pk_live_xxxxx
FEDAPAY_SECRET_KEY=sk_live_xxxxx
FEDAPAY_WEBHOOK_SECRET=wh_live_xxxxx
FEDAPAY_ENVIRONMENT=live
FEDAPAY_CURRENCY=XOF

# Session & Queue
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Uploader les variables** :
```bash
railway variables --set-from-file .railway.env
```

---

## 🔧 Après le Déploiement

### 1. Obtenir l'URL du Backend

```bash
railway domain
# Ou dans Dashboard → Settings → Networking → Generate Domain
```

Vous obtenez : `https://votre-backend.up.railway.app`

### 2. Tester l'API

```bash
curl https://votre-backend.up.railway.app/api/health

# Devrait retourner :
# {"status":"healthy","database":"connected"}
```

### 3. Mettre à Jour le Frontend

**Si frontend sur Render** :
- Dashboard → ticketing-frontend → Environment
- `VITE_API_URL = https://votre-backend.up.railway.app/api`
- Save Changes

**Si frontend sur Vercel** :
```bash
vercel env add VITE_API_URL production
# Entrer: https://votre-backend.up.railway.app/api
vercel --prod
```

### 4. Configurer les Webhooks

**FedaPay** :
- Dashboard FedaPay → Settings → Webhooks
- URL : `https://votre-backend.up.railway.app/api/webhooks/fedapay`

---

## 💰 Coût

- Backend : ~$2/mois
- PostgreSQL : ~$1/mois
- Queue Worker (optionnel) : ~$1/mois
- **Total : ~$4/mois**

Avec le crédit de $5/mois → **GRATUIT** ✅

---

## 🐛 Problèmes Courants

**Déploiement échoue** :
```bash
railway logs
# Vérifier les erreurs
```

**Base de données non accessible** :
```bash
# Vérifier que PostgreSQL est ajouté
railway ps
```

**Variables non prises en compte** :
```bash
# Vérifier les variables
railway variables

# Redéployer
railway up --detach
```

---

## 📊 Commandes Utiles

```bash
# Logs en temps réel
railway logs --follow

# Exécuter une commande
railway run php artisan migrate

# Shell interactif
railway shell

# Redémarrer
railway restart

# Voir l'utilisation
railway usage
```

---

## ✅ Checklist

- [ ] Compte Railway créé ($5 crédit)
- [ ] Projet créé depuis GitHub
- [ ] PostgreSQL ajouté
- [ ] Variables configurées
- [ ] APP_KEY généré
- [ ] Déployé avec succès
- [ ] URL générée
- [ ] Health check OK
- [ ] Frontend mis à jour
- [ ] Webhooks configurés

---

## 🎯 Architecture Finale

```
Frontend (Render)          Backend (Railway)         Database (Railway)
https://frontend.render → https://api.railway → PostgreSQL
      GRATUIT                  ~$4/mois               Inclus
                           (crédit $5 gratuit)
```

**Coût Total : $0/mois** ✅

---

## 📚 Documentation Complète

Pour plus de détails : **DEPLOYMENT_RAILWAY_BACKEND.md**

---

**Railway = La meilleure option pour un backend Laravel gratuit !** 🚀
