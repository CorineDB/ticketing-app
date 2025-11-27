# Plan de Déploiement - Frontend Uniquement (100% Gratuit)

Ce guide vous montre comment déployer **uniquement le frontend Vue.js** sur des plateformes gratuites.

## 🎯 Pourquoi Déployer le Frontend Séparément ?

✅ **Avantages** :
- Frontend statique = **GRATUIT ILLIMITÉ** sur la plupart des plateformes
- Déploiement rapide (2-5 minutes)
- CDN mondial automatique = très rapide partout
- SSL/HTTPS gratuit
- Nom de domaine gratuit fourni
- Tester l'interface utilisateur indépendamment

⚠️ **Note** : Le backend peut tourner en local pour le développement, ou être déployé plus tard.

---

## 🏆 Option 1 : Vercel (RECOMMANDÉ)

**Vercel** est la plateforme la plus populaire pour les applications Vue.js/React/Next.js.

### Avantages Vercel
- ✅ **100% gratuit** pour les sites statiques
- ✅ **Déploiement automatique** depuis GitHub
- ✅ **CDN mondial** ultra-rapide
- ✅ **SSL automatique** (HTTPS)
- ✅ **Domaine gratuit** (.vercel.app)
- ✅ **Illimité** - pas de limite de bande passante pour hobby
- ✅ **Preview deployments** pour chaque PR
- ✅ Interface très simple

### Configuration requise
```bash
# Dans ticketing-app/.env.production
VITE_API_URL=http://localhost:8000/api  # Pour développement local
# OU
VITE_API_URL=https://votre-backend.com/api  # Quand backend déployé
```

### Déploiement Vercel - Méthode 1 : Dashboard (Plus Simple)

**Étape 1** : Créer un compte
1. Aller sur https://vercel.com/
2. Cliquer "Sign Up"
3. Se connecter avec GitHub

**Étape 2** : Importer le projet
1. Cliquer "Add New..." → "Project"
2. Sélectionner votre repository `ticketing-app`
3. Vercel détecte automatiquement Vue.js

**Étape 3** : Configurer le build
```
Framework Preset: Vite
Root Directory: ticketing-app
Build Command: npm run build
Output Directory: dist
Install Command: npm install
```

**Étape 4** : Configurer les variables d'environnement
Dans "Environment Variables" :
```
VITE_API_URL = http://localhost:8000/api
VITE_CINETPAY_API_KEY = votre-clé
VITE_CINETPAY_SITE_ID = votre-site-id
VITE_ENABLE_MULTI_ORG = true
VITE_ENABLE_CASH_PAYMENTS = true
VITE_BETA_FEATURES = false
NODE_ENV = production
```

**Étape 5** : Déployer
1. Cliquer "Deploy"
2. Attendre 2-3 minutes
3. Votre site est en ligne ! 🎉

**URL** : `https://votre-projet.vercel.app`

### Déploiement Vercel - Méthode 2 : CLI (Plus Rapide)

```bash
# Installer Vercel CLI
npm install -g vercel

# Se connecter
vercel login

# Aller dans le dossier frontend
cd ticketing-app

# Déployer
vercel

# Suivre les instructions interactives
# Répondre aux questions :
# - Setup and deploy? Yes
# - Which scope? Votre compte
# - Link to existing project? No
# - Project name? ticketing-app
# - Directory? ./
# - Override settings? No

# Le déploiement commence automatiquement !
```

### Configurer les variables d'environnement avec CLI

```bash
# Ajouter les variables une par une
vercel env add VITE_API_URL
# Entrer: http://localhost:8000/api (ou votre backend URL)

vercel env add VITE_CINETPAY_API_KEY
# Entrer: votre-clé-cinetpay

vercel env add VITE_CINETPAY_SITE_ID
# Entrer: votre-site-id

vercel env add VITE_ENABLE_MULTI_ORG
# Entrer: true

vercel env add VITE_ENABLE_CASH_PAYMENTS
# Entrer: true

vercel env add VITE_BETA_FEATURES
# Entrer: false

# Redéployer avec les nouvelles variables
vercel --prod
```

### Déploiement continu automatique

Une fois configuré sur Vercel :
- ✅ Chaque `git push` déclenche un déploiement automatique
- ✅ Preview deployments pour les branches
- ✅ Production deployments pour `main`

---

## 🥈 Option 2 : Netlify

**Netlify** est une excellente alternative à Vercel.

### Avantages Netlify
- ✅ **100% gratuit** pour sites statiques
- ✅ **100 GB bande passante/mois** gratuit
- ✅ **CDN mondial**
- ✅ **SSL automatique**
- ✅ **Domaine gratuit** (.netlify.app)
- ✅ **Redirections** et **Headers** configurables
- ✅ **Forms** gratuits (formulaires sans backend)

### Déploiement Netlify - Dashboard

**Étape 1** : Créer un compte
1. Aller sur https://netlify.com/
2. Cliquer "Sign Up"
3. Se connecter avec GitHub

**Étape 2** : Nouveau site
1. Cliquer "Add new site" → "Import an existing project"
2. Connecter GitHub
3. Sélectionner le repository

**Étape 3** : Configuration du build
```
Base directory: ticketing-app
Build command: npm run build
Publish directory: ticketing-app/dist
```

**Étape 4** : Variables d'environnement
Dans "Site settings" → "Environment variables" :
```
VITE_API_URL = http://localhost:8000/api
VITE_CINETPAY_API_KEY = votre-clé
VITE_CINETPAY_SITE_ID = votre-site-id
```

**Étape 5** : Déployer
1. Cliquer "Deploy site"
2. Attendre 2-3 minutes
3. Site en ligne ! 🎉

### Configuration SPA pour Vue Router

Créer un fichier `netlify.toml` dans `ticketing-app/` :

```toml
[[redirects]]
  from = "/*"
  to = "/index.html"
  status = 200
```

Cela permet à Vue Router de fonctionner correctement.

### Déploiement Netlify - CLI

```bash
# Installer Netlify CLI
npm install -g netlify-cli

# Se connecter
netlify login

# Aller dans le dossier frontend
cd ticketing-app

# Déployer
netlify deploy --prod

# Suivre les instructions
# Build command: npm run build
# Publish directory: dist
```

---

## 🥉 Option 3 : Cloudflare Pages

**Cloudflare Pages** offre un excellent CDN gratuit.

### Avantages Cloudflare Pages
- ✅ **100% gratuit** illimité
- ✅ **Bande passante illimitée**
- ✅ **CDN ultra-rapide** (réseau Cloudflare)
- ✅ **500 builds/mois** gratuit
- ✅ **SSL automatique**

### Déploiement Cloudflare Pages

**Étape 1** : Créer un compte
1. Aller sur https://pages.cloudflare.com/
2. Se connecter avec GitHub

**Étape 2** : Créer un projet
1. "Create a project"
2. Sélectionner le repository
3. Configuration :
   ```
   Framework preset: Vue
   Build command: npm run build
   Build output directory: dist
   Root directory: ticketing-app
   ```

**Étape 3** : Variables d'environnement
```
VITE_API_URL = http://localhost:8000/api
VITE_CINETPAY_API_KEY = votre-clé
```

**Étape 4** : Déployer
1. Cliquer "Save and Deploy"
2. Site en ligne en 2-3 minutes

---

## 🎨 Option 4 : GitHub Pages (Simple mais Limité)

**GitHub Pages** est gratuit mais plus basique.

### Avantages
- ✅ **100% gratuit**
- ✅ Directement depuis GitHub
- ✅ Simple pour petits projets

### Inconvénients
- ⚠️ Pas de variables d'environnement
- ⚠️ Configuration manuelle requise
- ⚠️ Moins de fonctionnalités

### Déploiement GitHub Pages

**Étape 1** : Installer gh-pages

```bash
cd ticketing-app
npm install --save-dev gh-pages
```

**Étape 2** : Configurer package.json

Ajouter dans `ticketing-app/package.json` :

```json
{
  "scripts": {
    "predeploy": "npm run build",
    "deploy": "gh-pages -d dist"
  },
  "homepage": "https://votre-username.github.io/ticketing-app"
}
```

**Étape 3** : Configurer vite.config.ts

Modifier `ticketing-app/vite.config.ts` :

```typescript
export default defineConfig({
  base: '/ticketing-app/', // Nom du repo
  plugins: [vue()],
  // ... reste de la config
})
```

**Étape 4** : Déployer

```bash
npm run deploy
```

Site disponible à : `https://votre-username.github.io/ticketing-app/`

---

## 📊 Comparaison des Options Frontend

| Plateforme | Gratuit | Bande Passante | CDN | Déploiement Auto | Facile | Recommandé |
|------------|---------|----------------|-----|------------------|--------|------------|
| **Vercel** | ✅ Illimité | ✅ Généreux | ✅ Mondial | ✅ Oui | ⭐⭐⭐⭐⭐ | ✅ **OUI** |
| **Netlify** | ✅ Illimité | ✅ 100 GB/mois | ✅ Mondial | ✅ Oui | ⭐⭐⭐⭐⭐ | ✅ Oui |
| **Cloudflare** | ✅ Illimité | ✅ Illimité | ✅ Ultra-rapide | ✅ Oui | ⭐⭐⭐⭐ | ✅ Oui |
| **GitHub Pages** | ✅ Illimité | ✅ 100 GB/mois | ❌ Non | ⚠️ Manuel | ⭐⭐⭐ | ⚠️ Basique |

---

## 🔧 Configuration Backend pour Développement Local

Pendant que le frontend est déployé, vous pouvez tester avec le backend local :

### Option A : Backend en Local

```bash
# Terminal 1 : Backend
cd ticketing-api-rest-app
php artisan serve --host=0.0.0.0 --port=8000

# Le backend tourne sur http://localhost:8000
```

**Important** : Configurer CORS dans Laravel pour accepter les requêtes du frontend déployé.

Modifier `config/cors.php` :

```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'https://votre-app.vercel.app', // Ajouter votre URL Vercel
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Option B : Exposer Backend Local avec Ngrok

Pour que le frontend déployé puisse accéder à votre backend local :

```bash
# Installer ngrok
# https://ngrok.com/download

# Exposer le backend local
ngrok http 8000

# Ngrok vous donne une URL publique:
# https://abc123.ngrok.io

# Mettre à jour VITE_API_URL sur Vercel:
# VITE_API_URL = https://abc123.ngrok.io/api
```

---

## 📝 Guide Complet Étape par Étape - Vercel (Recommandé)

### Préparation (une seule fois)

1. **Créer fichier `.env.production` dans `ticketing-app/`** :

```bash
VITE_API_URL=http://localhost:8000/api
VITE_CINETPAY_API_KEY=votre-clé-production
VITE_CINETPAY_SITE_ID=votre-site-id
VITE_ENABLE_MULTI_ORG=true
VITE_ENABLE_CASH_PAYMENTS=true
VITE_BETA_FEATURES=false
```

2. **Tester le build localement** :

```bash
cd ticketing-app
npm install
npm run build
npm run preview  # Tester la version production

# Vérifier que tout fonctionne
# Ouvrir http://localhost:4173
```

### Déploiement

**Méthode Rapide - CLI** :

```bash
# 1. Installer Vercel CLI
npm install -g vercel

# 2. Se connecter
vercel login

# 3. Déployer
cd ticketing-app
vercel

# 4. Production
vercel --prod
```

**Méthode Simple - Dashboard** :

1. Aller sur https://vercel.com/
2. Se connecter avec GitHub
3. "Add New..." → "Project"
4. Sélectionner le repo
5. Configurer :
   - Root Directory: `ticketing-app`
   - Build Command: `npm run build`
   - Output Directory: `dist`
6. Ajouter les variables d'environnement
7. Cliquer "Deploy"

**C'est tout !** 🎉

### Obtenir l'URL de votre site

Après le déploiement, Vercel vous donne :
- URL automatique : `https://ticketing-app-xyz.vercel.app`
- Vous pouvez ajouter un domaine personnalisé gratuitement

---

## 🔄 Workflow de Développement Recommandé

### Phase 1 : Frontend Déployé + Backend Local

```
Frontend (Vercel) ──→ Backend (Local via ngrok)
https://app.vercel.app    https://xyz.ngrok.io/api
```

### Phase 2 : Frontend Déployé + Backend Déployé

```
Frontend (Vercel) ──→ Backend (Railway/Fly.io)
https://app.vercel.app    https://api.railway.app/api
```

---

## ✅ Checklist de Déploiement Frontend

- [ ] Créer fichier `.env.production`
- [ ] Tester `npm run build` localement
- [ ] Tester `npm run preview` localement
- [ ] Créer compte Vercel/Netlify
- [ ] Connecter GitHub
- [ ] Configurer le projet
- [ ] Ajouter variables d'environnement
- [ ] Déployer
- [ ] Tester le site en production
- [ ] Configurer CORS dans le backend si nécessaire

---

## 🎯 Recommandation Finale

**Pour déployer uniquement le frontend** :

1. **Vercel** (le plus simple et rapide)
   - Dashboard : 5 minutes
   - CLI : 2 minutes
   - Résultat professionnel

2. **Backend en local + Ngrok** (pour tester)
   - Permet de tester le frontend déployé avec backend local
   - Gratuit, pratique pour le développement

3. **Plus tard : Déployer le backend sur Railway** ($5 crédit/mois)
   - Quand vous êtes prêt pour la production complète

---

## 🚀 Commandes Rapides

```bash
# Déploiement ultra-rapide avec Vercel CLI
cd ticketing-app
npm install -g vercel
vercel login
vercel --prod

# C'est tout ! Site déployé en 2 minutes ⚡
```

---

## ❓ Questions Fréquentes

**Q: Le frontend peut-il fonctionner sans backend ?**
R: Non, mais vous pouvez pointer vers un backend local (avec ngrok) ou déployer le backend plus tard.

**Q: Les variables d'environnement sont-elles sécurisées ?**
R: Les variables `VITE_*` sont exposées dans le code JavaScript (frontend), donc ne pas y mettre de secrets. Les clés API publiques (comme CinetPay public key) sont OK.

**Q: Comment changer l'URL du backend après déploiement ?**
R: Dans Vercel/Netlify, aller dans Settings → Environment Variables → Modifier `VITE_API_URL` → Redéployer.

**Q: Le site est-il vraiment gratuit pour toujours ?**
R: Oui ! Vercel, Netlify, et Cloudflare offrent des plans gratuits permanents pour les sites statiques.

---

## 📚 Ressources

- [Documentation Vercel](https://vercel.com/docs)
- [Documentation Netlify](https://docs.netlify.com)
- [Documentation Cloudflare Pages](https://developers.cloudflare.com/pages)
- [Vue.js Deployment Guide](https://vuejs.org/guide/best-practices/production-deployment.html)
- [Vite Deployment](https://vitejs.dev/guide/static-deploy.html)
