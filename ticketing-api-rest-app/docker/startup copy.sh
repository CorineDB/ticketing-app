#!/bin/bash

# Arrête le script dès qu'une erreur survient (très important pour ne pas lancer un serveur cassé)
set -e

# 1. Configuration du Port Nginx (Requis par Railway)
# Si la variable PORT n'est pas définie, on utilise 8080
PORT=${PORT:-8080}
echo "Configuration du port Nginx sur $PORT..."
sed -i "s/8080/$PORT/g" /etc/nginx/conf.d/default.conf

# 2. 🚀 EXÉCUTION DES MIGRATIONS
echo "Exécution des migrations..."
# L'option --force est obligatoire en prod pour éviter la question "Are you sure?"
php artisan migrate --force

# 3. 🌱 EXÉCUTION DES SEEDERS (Optionnel mais demandé)
# ATTENTION : Assure-toi que tes seeders gèrent les doublons (utilisent firstOrCreate)
# Sinon, commente cette ligne après le premier déploiement pour éviter les erreurs.
echo "Exécution des seeders..."
php artisan db:seed --force

# 4. Commandes de maintenance et cache (Recommandé pour la prod)
echo "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Démarrage des services
echo "Démarrage de PHP-FPM..."
php-fpm -D

echo "Démarrage de Nginx..."
nginx -g "daemon off;"
nginx -g "daemon off;"
nginx -g "daemon off;"
