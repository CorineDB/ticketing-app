# 🚀 Déploiement Frontend - Guide Ultra-Rapide

Déployez votre frontend en **2 minutes** sur Vercel (100% gratuit).

## ⚡ Méthode Express (2 minutes)

### Option 1 : Script Automatique

```bash
cd ticketing-app
./deploy-vercel.sh
```

Le script fait tout automatiquement ! ✅

### Option 2 : Commandes Manuelles

```bash
# 1. Installer Vercel CLI
npm install -g vercel

# 2. Aller dans le dossier frontend
cd ticketing-app

# 3. Créer .env.production
cp .env.production.example .env.production
# Éditer .env.production avec vos valeurs

# 4. Se connecter à Vercel
vercel login

# 5. Déployer
vercel --prod
```

**C'est tout !** 🎉 Votre site est en ligne.

## 🌐 URL de votre site

Après le déploiement, Vercel vous donne une URL automatique :
```
https://ticketing-app-xxxxx.vercel.app
```

## 📝 Variables d'Environnement à Configurer

Dans votre fichier `.env.production` ou dans le dashboard Vercel :

```bash
# API Backend (choisir une option)
VITE_API_URL=http://localhost:8000/api           # Backend local
# OU
VITE_API_URL=https://votre-backend.railway.app/api  # Backend déployé

# Paiement CinetPay
VITE_CINETPAY_API_KEY=votre-clé
VITE_CINETPAY_SITE_ID=votre-site-id

# Features
VITE_ENABLE_MULTI_ORG=true
VITE_ENABLE_CASH_PAYMENTS=true
```

## 🔧 Backend Local avec Ngrok (Optionnel)

Pour tester le frontend déployé avec votre backend local :

```bash
# Terminal 1 : Backend Laravel
cd ticketing-api-rest-app
php artisan serve

# Terminal 2 : Ngrok
ngrok http 8000

# Copier l'URL ngrok (ex: https://abc123.ngrok.io)
# Mettre à jour VITE_API_URL dans Vercel avec cette URL + /api
```

## ✅ Vérification

1. Ouvrir l'URL Vercel dans votre navigateur
2. Vérifier que l'interface s'affiche correctement
3. Tester la connexion au backend (si configuré)

## 📚 Documentation Complète

Pour plus de détails, voir : **DEPLOYMENT_FRONTEND_ONLY.md**

## 🆘 Problèmes Courants

**Erreur lors du build** :
```bash
# Tester le build localement
npm run build
npm run preview
```

**Site déployé mais API ne fonctionne pas** :
- Vérifier `VITE_API_URL` dans les variables Vercel
- Vérifier que le backend accepte les requêtes CORS
- Vérifier que le backend est accessible

**Variables d'environnement non prises en compte** :
- Les variables doivent commencer par `VITE_`
- Redéployer après avoir modifié les variables
- Vider le cache du navigateur

## 🎯 Prochaines Étapes

1. ✅ Frontend déployé sur Vercel
2. 🔜 Déployer le backend sur Railway ($5 crédit gratuit)
3. 🔜 Connecter frontend et backend
4. 🔜 Tester les paiements en production

---

**Besoin d'aide ?** Consultez **DEPLOYMENT_FRONTEND_ONLY.md** pour le guide complet.
