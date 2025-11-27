#!/bin/bash

echo "🚀 Démarrage de l'application Ticketing"
echo "========================================"
echo ""

# Vérifier PostgreSQL
echo "📊 Vérification de PostgreSQL..."
if ! sudo service postgresql status > /dev/null 2>&1; then
    echo "⚠️  PostgreSQL n'est pas démarré. Démarrage..."
    sudo service postgresql start
fi
echo "✅ PostgreSQL OK"
echo ""

# Vérifier la base de données
echo "🗄️  Vérification de la base de données..."
if ! psql -U postgres -lqt | cut -d \| -f 1 | grep -qw ticketing; then
    echo "⚠️  Base de données 'ticketing' n'existe pas. Création..."
    sudo -u postgres psql -c "CREATE DATABASE ticketing;"
fi
echo "✅ Base de données OK"
echo ""

# Backend
echo "🔧 Configuration du backend..."
cd ticketing-api-rest-app

# Migrations
if [ ! -f "database/database.sqlite" ] && [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "📦 Exécution des migrations..."
    php artisan migrate --force
fi

echo ""
echo "✅ Configuration terminée !"
echo ""
echo "📋 Pour démarrer l'application, ouvrez 2 terminaux :"
echo ""
echo "Terminal 1 (Backend) :"
echo "  cd ticketing-api-rest-app && php artisan serve"
echo ""
echo "Terminal 2 (Frontend) :"
echo "  cd ticketing-app && npm run dev"
echo ""
echo "Puis ouvrez : http://localhost:5173"
echo ""
