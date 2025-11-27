# Guide de Déploiement GRATUIT - Options Sans Paiement

Ce guide vous présente plusieurs options **100% gratuites** pour déployer votre application de gestion de tickets.

## 📊 Comparaison des Options Gratuites

| Service | Backend | Base de données | Frontend | Queue Worker | Limitations |
|---------|---------|-----------------|----------|--------------|-------------|
| **Render.com** | ✅ Gratuit | ✅ 90 jours gratuits | ✅ Gratuit illimité | ❌ Payant | Mise en veille après 15min |
| **Railway.app** | ✅ $5/mois crédit | ✅ Inclus | ✅ Inclus | ✅ Inclus | Limité par crédit |
| **Fly.io** | ✅ Gratuit | ✅ 3GB gratuit | ✅ Gratuit | ✅ Gratuit | Limité à 3 machines |
| **Vercel + Neon** | ❌ Serverless seulement | ✅ Gratuit | ✅ Gratuit | ❌ Non supporté | Laravel non compatible |

## 🎯 Option Recommandée: Railway.app (Meilleure option gratuite)

**Railway.app** offre $5 de crédit gratuit par mois, ce qui suffit largement pour une petite application.

### Avantages de Railway
- ✅ **$5 de crédit gratuit** par mois (suffisant pour ~500h)
- ✅ **Pas de mise en veille** (toujours actif)
- ✅ **Base de données PostgreSQL incluse** (pas de limite de 90 jours)
- ✅ **Queue worker inclus** sans frais supplémentaires
- ✅ **Déploiement automatique** depuis GitHub
- ✅ **Domaine gratuit** fourni (.up.railway.app)
- ✅ **SSL/HTTPS gratuit**

### Inconvénients
- ⚠️ Crédit limité à $5/mois (mais suffisant pour un usage modéré)
- ⚠️ Après le crédit, facturation à l'usage

### Comment déployer sur Railway

1. **Créer un compte Railway**: https://railway.app/
2. **Installer Railway CLI** (optionnel):
   ```bash
   npm install -g @railway/cli
   railway login
   ```

3. **Déployer via Dashboard**:
   - Aller sur https://railway.app/dashboard
   - Cliquer "New Project" → "Deploy from GitHub repo"
   - Sélectionner votre repository
   - Railway détecte automatiquement le Dockerfile

4. **Configurer les services**:
   - Ajouter PostgreSQL: Cliquer "New" → "Database" → "PostgreSQL"
   - Les variables DB_* sont automatiquement configurées

5. **Configurer les variables d'environnement**:
   - Dans votre service backend → "Variables"
   - Ajouter toutes les variables (MAIL_*, SMS_*, FEDAPAY_*, etc.)

### Estimation de consommation Railway (plan gratuit)

Avec $5 de crédit:
- Backend API: ~$2/mois
- Base de données PostgreSQL: ~$1/mois
- Queue Worker: ~$1/mois
- Frontend (statique): Gratuit sur Vercel/Netlify
- **Total: ~$4/mois = DANS LE CRÉDIT GRATUIT** ✅

## 🚀 Option 2: Render.com (Plan Gratuit) ⚠️ **NON RECOMMANDÉ**

**Fichier de configuration**: Utilisez `render-free.yaml`

### ❌ LIMITATIONS CRITIQUES de Render Gratuit

**Base de données PostgreSQL**:
- ⏰ **Expire après 30 jours** (+ 14 jours de grâce = 44 jours max)
- 💾 Limite de **1 GB seulement**
- 🗑️ **Suppression automatique** si non upgradé
- 🚫 **Une seule base de données gratuite** par compte
- ❌ Pas de backup

**Web Services**:
- ✅ 750h/mois (suffisant pour 1 service 24/7)
- 😴 Mise en veille après 15 min d'inactivité
- ⏱️ Réveil très lent: **jusqu'à 1 minute**
- 🚫 **SMTP bloqué** (ports 25, 465, 587) = **Impossible d'envoyer des emails**
- ❌ Pas de queue worker gratuit
- ❌ Pas de scaling, disque persistant, SSH

**Frontend**:
- ✅ Gratuit illimité

### ⚠️ POURQUOI RENDER GRATUIT N'EST PAS VIABLE

1. **Base de données supprimée après 30 jours** - Vous perdrez toutes vos données !
2. **Impossible d'envoyer des emails** - Les notifications par email ne fonctionneront pas
3. **Pas de queue worker** - Les jobs en arrière-plan ne peuvent pas s'exécuter
4. **Réveil trop lent** - Mauvaise expérience utilisateur (1 minute d'attente)
5. **Limite 1 GB** - Trop petit pour une application de ticketing

### ❌ NE PAS UTILISER Render gratuit pour cette application

### Déploiement Render Gratuit

```bash
# Utiliser le fichier de configuration gratuite
# Dans Render Dashboard:
# 1. New → Blueprint
# 2. Sélectionner le repo GitHub
# 3. Utiliser render-free.yaml au lieu de render.yaml
```

## 🌐 Option 3: Fly.io (Généreux Plan Gratuit)

Fly.io offre un excellent plan gratuit pour les petites applications.

### Plan Gratuit Fly.io
- ✅ 3 machines partagées (256MB RAM)
- ✅ 3GB stockage PostgreSQL
- ✅ 160GB bandwidth sortant
- ✅ Pas de mise en veille
- ✅ SSL automatique

### Déploiement sur Fly.io

1. **Installer Fly CLI**:
   ```bash
   curl -L https://fly.io/install.sh | sh
   fly auth signup
   ```

2. **Créer l'application**:
   ```bash
   cd ticketing-api-rest-app
   fly launch --no-deploy
   ```

3. **Créer la base de données**:
   ```bash
   fly postgres create --name ticketing-db
   fly postgres attach ticketing-db
   ```

4. **Configurer les variables**:
   ```bash
   fly secrets set MAIL_HOST=smtp.titan.email
   fly secrets set MAIL_USERNAME=your-email
   # ... etc
   ```

5. **Déployer**:
   ```bash
   fly deploy
   ```

## 💡 Option 4: Configuration Hybride (100% Gratuit Permanent)

Combiner plusieurs services gratuits pour un déploiement permanent:

### Architecture Hybride Gratuite

```
Frontend (Vercel)     →  Backend API (Railway)  →  Database (Neon.tech)
  Gratuit illimité       $5 crédit/mois             Gratuit illimité
```

#### Services utilisés:
1. **Frontend**: Vercel ou Netlify (gratuit illimité)
2. **Backend API**: Railway.app ($5 crédit gratuit/mois)
3. **Base de données**: Neon.tech (PostgreSQL gratuit permanent)
4. **Queue**: Pas de worker séparé (traitement synchrone)

### Configuration Neon.tech (PostgreSQL Gratuit)

1. **Créer compte**: https://neon.tech/
2. **Créer base de données**: Gratuit avec limitations
   - ✅ 3GB de stockage
   - ✅ Branches illimitées
   - ✅ Pas de limite de temps (contrairement à Render)

3. **Obtenir connection string**:
   ```
   postgres://user:password@ep-xxx.neon.tech/dbname
   ```

4. **Configurer dans Railway**:
   - Ajouter `DATABASE_URL` avec la connection string Neon

### Déployer Frontend sur Vercel (Gratuit)

```bash
cd ticketing-app

# Installer Vercel CLI
npm install -g vercel

# Déployer
vercel

# Configurer les variables d'environnement
vercel env add VITE_API_URL
# Entrer: https://votre-api.railway.app/api
```

## 📋 Recommandation Finale

### Pour usage immédiat et simple:
👉 **Railway.app** - Le plus facile, tout-en-un, $5 crédit suffit

### Pour économiser à long terme:
👉 **Configuration Hybride** - Frontend (Vercel) + Backend (Railway) + DB (Neon)

### Pour tester rapidement:
👉 **Render.com gratuit** - Bon pour 90 jours, ensuite migrer

## ⚙️ Configuration Sans Queue Worker

Si vous utilisez un plan gratuit sans queue worker, modifiez la configuration Laravel:

**Dans `.env`**:
```bash
QUEUE_CONNECTION=sync  # Au lieu de 'database'
```

Cela exécutera les jobs immédiatement au lieu de les mettre en file d'attente.

**Inconvénient**: Les emails/notifications bloqueront la requête HTTP
**Avantage**: Pas besoin de queue worker séparé (économie de $7/mois)

## 🎓 Tableau de Décision

| Critère | Railway | Render Free | Fly.io | Hybride |
|---------|---------|-------------|---------|---------|
| **Coût** | $0/mois | $0/mois | $0/mois | $0/mois |
| **Durée gratuite** | Permanent* | 90 jours DB | Permanent | Permanent |
| **Facilité** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **Performance** | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Queue Worker** | ✅ | ❌ | ✅ | ❌ |
| **Pas de sleep** | ✅ | ❌ | ✅ | Mixte |

*Tant que vous restez sous $5/mois de consommation

## 🔧 Prochaines Étapes

Choisissez une option ci-dessus et je vous fournirai:
1. La configuration détaillée
2. Les commandes exactes à exécuter
3. Les variables d'environnement à configurer
4. Un guide de déploiement pas à pas

**Quelle option préférez-vous ?**
- Option 1: Railway.app (recommandé)
- Option 2: Render.com gratuit (90 jours)
- Option 3: Fly.io
- Option 4: Configuration hybride (Vercel + Railway + Neon)
