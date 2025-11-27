#!/bin/bash

# Script de déploiement rapide pour Vercel
# Usage: ./deploy-vercel.sh

set -e

echo "🚀 Déploiement Frontend sur Vercel"
echo "===================================="

# Vérifier que nous sommes dans le bon dossier
if [ ! -f "package.json" ]; then
    echo "❌ Erreur: package.json non trouvé"
    echo "   Assurez-vous d'être dans le dossier ticketing-app"
    exit 1
fi

# Vérifier que .env.production existe
if [ ! -f ".env.production" ]; then
    echo "⚠️  Fichier .env.production non trouvé"
    echo "   Création à partir de .env.production.example..."
    if [ -f ".env.production.example" ]; then
        cp .env.production.example .env.production
        echo "✅ Fichier .env.production créé"
        echo "⚠️  IMPORTANT: Éditez .env.production avec vos vraies valeurs!"
        read -p "Appuyez sur Entrée pour continuer..."
    else
        echo "❌ .env.production.example non trouvé"
        exit 1
    fi
fi

# Vérifier que Vercel CLI est installé
if ! command -v vercel &> /dev/null; then
    echo "📦 Installation de Vercel CLI..."
    npm install -g vercel
fi

# Tester le build localement
echo ""
echo "🔨 Test du build local..."
npm install
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Le build a échoué. Corrigez les erreurs avant de déployer."
    exit 1
fi

echo "✅ Build local réussi!"
echo ""

# Demander confirmation
read -p "🚀 Déployer en production sur Vercel ? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🚀 Déploiement en cours..."
    vercel --prod

    echo ""
    echo "✅ Déploiement terminé!"
    echo "🌐 Votre site est maintenant en ligne!"
else
    echo "❌ Déploiement annulé"
    exit 0
fi
