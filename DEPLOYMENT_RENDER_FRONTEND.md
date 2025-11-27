# Déploiement Frontend sur Render.com - Guide Complet

Ce guide explique comment déployer **uniquement le frontend Vue.js** sur Render.com (100% gratuit et illimité pour les sites statiques).

## ✅ Avantages du Frontend sur Render

- ✅ **100% gratuit ILLIMITÉ** pour les sites statiques
- ✅ **Pas de limite de temps** (contrairement à la base de données)
- ✅ **Pas de mise en veille** pour les sites statiques
- ✅ **CDN mondial** intégré
- ✅ **SSL/HTTPS automatique**
- ✅ **Déploiement automatique** depuis GitHub
- ✅ **Domaine gratuit** (.onrender.com)
- ✅ **Bande passante généreuse**

## 🚀 Méthode 1 : Déploiement via Dashboard Render (Recommandé)

### Étape 1 : Préparation du Projet

Créer le fichier de configuration Render pour le frontend.

**Créer `render-frontend.yaml` à la racine du projet** :

```yaml
services:
  # Frontend Vue.js - Static Site (100% Gratuit)
  - type: web
    name: ticketing-frontend
    env: static
    buildCommand: cd ticketing-app && npm install && npm run build
    staticPublishPath: ticketing-app/dist
    routes:
      - type: rewrite
        source: /*
        destination: /index.html
    envVars:
      - key: VITE_API_URL
        sync: false
      - key: VITE_CINETPAY_API_KEY
        sync: false
      - key: VITE_CINETPAY_SITE_ID
        sync: false
      - key: VITE_PAYDUNYA_API_KEY
        sync: false
      - key: VITE_MTN_MOMO_API_KEY
        sync: false
      - key: VITE_ENABLE_MULTI_ORG
        value: true
      - key: VITE_ENABLE_CASH_PAYMENTS
        value: true
      - key: VITE_BETA_FEATURES
        value: false
      - key: NODE_ENV
        value: production
```

### Étape 2 : Créer un Compte Render

1. Aller sur https://render.com/
2. Cliquer sur "Get Started for Free"
3. Se connecter avec GitHub

### Étape 3 : Déployer avec Blueprint

1. Dans le Dashboard Render, cliquer **"New +"** → **"Blueprint"**
2. Connecter votre repository GitHub
3. Sélectionner la branche : `claude/deploy-ren-environment-0139xhC4fcY4J1SJuqfrXYyK`
4. Render détecte automatiquement `render-frontend.yaml`
5. Cliquer **"Apply"**

### Étape 4 : Configurer les Variables d'Environnement

Une fois le site créé :

1. Aller dans votre service "ticketing-frontend"
2. Cliquer sur l'onglet **"Environment"**
3. Ajouter les variables suivantes :

```bash
# URL du Backend (choisir selon votre configuration)
VITE_API_URL=http://localhost:8000/api
# OU si backend local avec ngrok:
# VITE_API_URL=https://votre-url.ngrok.io/api
# OU si backend déployé:
# VITE_API_URL=https://votre-backend.onrender.com/api

# Paiement CinetPay
VITE_CINETPAY_API_KEY=votre-clé-cinetpay
VITE_CINETPAY_SITE_ID=votre-site-id

# (Optionnel) Autres passerelles de paiement
VITE_PAYDUNYA_API_KEY=votre-clé-paydunya
VITE_MTN_MOMO_API_KEY=votre-clé-mtn
```

4. Cliquer **"Save Changes"**

Le site se redéploie automatiquement avec les nouvelles variables.

### Étape 5 : Attendre le Déploiement

- Le déploiement prend environ **2-5 minutes**
- Vous pouvez suivre la progression dans l'onglet "Logs"
- Une fois terminé, vous verrez "Live" en vert

### Étape 6 : Obtenir l'URL

Votre site est accessible à :
```
https://ticketing-frontend.onrender.com
```

(L'URL exacte est affichée en haut de la page du service)

---

## 🚀 Méthode 2 : Déploiement Manuel (Sans Blueprint)

Si vous préférez créer le service manuellement :

### Étape 1 : Nouveau Site Statique

1. Dans le Dashboard Render, cliquer **"New +"** → **"Static Site"**
2. Connecter votre repository GitHub
3. Sélectionner le repository et la branche

### Étape 2 : Configuration du Build

Remplir les champs suivants :

```
Name: ticketing-frontend
Root Directory: (laisser vide)
Build Command: cd ticketing-app && npm install && npm run build
Publish Directory: ticketing-app/dist
Branch: claude/deploy-ren-environment-0139xhC4fcY4J1SJuqfrXYyK
```

### Étape 3 : Configuration Avancée

Cliquer sur **"Advanced"** et ajouter :

**Rewrite Rules** (pour Vue Router) :
```
Source: /*
Destination: /index.html
Action: Rewrite
```

### Étape 4 : Variables d'Environnement

Ajouter les variables (comme dans la Méthode 1)

### Étape 5 : Créer le Site

Cliquer **"Create Static Site"**

Le déploiement commence automatiquement.

---

## 🔧 Configuration Backend

### Option A : Backend Local avec Ngrok (Pour Tests)

**Terminal 1 - Backend Laravel** :
```bash
cd ticketing-api-rest-app
php artisan serve
```

**Terminal 2 - Ngrok** :
```bash
# Installer ngrok: https://ngrok.com/download
ngrok http 8000

# Ngrok vous donne une URL publique:
# https://abc123.ngrok.io
```

**Dans Render** :
- Aller dans Environment Variables
- Mettre `VITE_API_URL = https://abc123.ngrok.io/api`
- Sauvegarder (redéploie automatiquement)

✅ Votre frontend déployé peut maintenant accéder à votre backend local !

### Option B : Backend Déployé sur Render (Payant)

Si vous déployez aussi le backend sur Render :

```bash
VITE_API_URL=https://votre-backend-api.onrender.com/api
```

### Option C : Backend sur une Autre Plateforme

Si le backend est sur Railway, Fly.io, etc. :

```bash
VITE_API_URL=https://votre-backend.railway.app/api
```

---

## 🔒 Configuration CORS dans Laravel

Pour que le frontend déployé puisse accéder au backend, configurer CORS.

**Dans `ticketing-api-rest-app/config/cors.php`** :

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:4173',
        'https://ticketing-frontend.onrender.com', // Ajouter votre URL Render
        'https://*.ngrok.io', // Si vous utilisez ngrok
        'https://*.ngrok-free.app', // Nouveau domaine ngrok
    ],

    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.ngrok\.io$/',
        '/^https:\/\/.*\.ngrok-free\.app$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
```

**Redémarrer le backend après modification**.

---

## 📝 Vérification du Déploiement

### 1. Vérifier que le Site est en Ligne

- Ouvrir l'URL Render dans le navigateur
- L'interface Vue.js doit s'afficher

### 2. Tester la Connexion Backend

- Ouvrir les DevTools (F12)
- Onglet "Network"
- Naviguer dans l'application
- Vérifier que les requêtes API fonctionnent

### 3. Vérifier les Variables d'Environnement

Dans le code source de la page (Ctrl+U), chercher :
```javascript
// Les variables VITE_ sont compilées dans le code
```

Si vous voyez `undefined` ou `localhost` alors que vous avez configuré autre chose, les variables n'ont pas été prises en compte.

**Solution** : Vérifier que les variables sont dans l'onglet "Environment" et redéployer.

---

## 🔄 Déploiement Automatique

Une fois configuré :

1. **Chaque `git push`** sur la branche configurée déclenche un déploiement automatique
2. Render rebuild et redéploie automatiquement
3. Le site est mis à jour en 2-5 minutes

**Désactiver le déploiement automatique** (optionnel) :
- Settings → "Auto-Deploy" → Désactiver
- Déployer manuellement avec "Manual Deploy"

---

## 🎨 Personnalisation

### Ajouter un Domaine Personnalisé

1. Aller dans "Settings" → "Custom Domains"
2. Ajouter votre domaine (ex: `app.votresite.com`)
3. Configurer les DNS selon les instructions Render
4. SSL automatique fourni par Render

### Ajouter des Headers Personnalisés

Dans Settings → "Headers" :

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
```

### Ajouter des Redirections

Dans Settings → "Redirects" :

```
Source: /old-page
Destination: /new-page
Status: 301
```

---

## 🐛 Dépannage

### Le site ne se construit pas

**Erreur** : `Build failed`

**Solutions** :
```bash
# Tester le build localement
cd ticketing-app
npm install
npm run build

# Si ça fonctionne localement mais pas sur Render:
# - Vérifier la version de Node.js
# - Ajouter dans Environment Variables:
NODE_VERSION=18
```

### Les routes Vue Router ne fonctionnent pas (404)

**Problème** : `/events/123` retourne 404

**Solution** : Vérifier que les rewrites sont configurés :
- Blueprint : Déjà inclus dans `render-frontend.yaml`
- Manuel : Ajouter dans "Rewrite Rules"

### Variables d'environnement non prises en compte

**Problème** : `VITE_API_URL` est `undefined`

**Solutions** :
1. Vérifier que la variable commence par `VITE_`
2. Sauvegarder les variables dans Environment
3. **Redéployer manuellement** (Manual Deploy)
4. Vider le cache du navigateur

### Le backend ne répond pas (CORS)

**Erreur** dans Console :
```
Access to fetch at 'http://localhost:8000/api' from origin 'https://ticketing-frontend.onrender.com' has been blocked by CORS policy
```

**Solution** :
- Configurer CORS dans Laravel (voir section CORS ci-dessus)
- Redémarrer le backend
- Si ngrok : utiliser l'URL ngrok dans `VITE_API_URL`

### Le site est lent à charger

**Problème** : Premier chargement lent

**Causes possibles** :
- Fichiers JavaScript/CSS trop gros
- Images non optimisées

**Solutions** :
```bash
# Analyser la taille du bundle
npm run build
# Vérifier dist/assets/

# Optimiser les images
# Utiliser lazy loading pour les routes
```

---

## 📊 Monitoring

### Logs en Temps Réel

1. Aller dans votre service
2. Onglet "Logs"
3. Voir les logs de build et déploiement

### Statistiques

Render Dashboard → "Activity" :
- Nombre de déploiements
- Temps de build
- Taille du site

---

## 💰 Coût

**Frontend statique sur Render** :
- ✅ **0€/mois** - 100% gratuit
- ✅ Illimité dans le temps
- ✅ Bande passante généreuse
- ✅ Pas de carte bancaire requise

---

## 🎯 Checklist de Déploiement

- [ ] Compte Render créé
- [ ] Repository GitHub connecté
- [ ] `render-frontend.yaml` créé (ou configuration manuelle)
- [ ] Blueprint appliqué / Site créé
- [ ] Variables d'environnement configurées
- [ ] Backend configuré (local avec ngrok ou déployé)
- [ ] CORS configuré dans Laravel
- [ ] Site déployé et en ligne
- [ ] Routes Vue Router fonctionnent
- [ ] Connexion au backend testée
- [ ] Paiements testés (si applicable)

---

## 🚀 Prochaines Étapes

1. ✅ Frontend déployé sur Render
2. 🔜 Tester avec backend local (via ngrok)
3. 🔜 Déployer le backend (Railway recommandé, ou Render payant)
4. 🔜 Connecter frontend et backend en production
5. 🔜 Configurer les webhooks de paiement
6. 🔜 Tester le flux complet

---

## 📚 Ressources

- [Documentation Render Static Sites](https://render.com/docs/static-sites)
- [Render Environment Variables](https://render.com/docs/environment-variables)
- [Vue.js Deployment Guide](https://vuejs.org/guide/best-practices/production-deployment.html)
- [Ngrok Documentation](https://ngrok.com/docs)

---

## 💡 Conseils

1. **Toujours tester le build localement avant de déployer**
   ```bash
   npm run build && npm run preview
   ```

2. **Utiliser les Preview Deployments** pour tester les branches
   - Chaque PR peut avoir son propre déploiement de test

3. **Surveiller la taille du bundle**
   - Garder `dist/` sous 10 MB pour de meilleures performances

4. **Utiliser ngrok pour le développement**
   - Permet de tester le frontend déployé avec le backend local

5. **Configurer les variables d'environnement dès le début**
   - Évite de devoir redéployer plusieurs fois

---

**Besoin d'aide ?** Suivez ce guide étape par étape ou consultez la documentation Render.
