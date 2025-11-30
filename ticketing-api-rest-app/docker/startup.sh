#!/bin/bash

# Affiche les erreurs si une commande échoue
set -e

# 1. Remplacer le port dans Nginx par celui fourni par Railway ($PORT)
# Si PORT n'est pas défini, on utilise 8080 par défaut
PORT=${PORT:-8080}
sed -i "s/8080/$PORT/g" /etc/nginx/conf.d/default.conf

# 2. Commandes de maintenance (Optionnel : à activer selon vos besoins)
# echo "Caching configuration..."
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# 3. Démarrer PHP-FPM en arrière-plan (-D)
php-fpm -D

# 4. Démarrer Nginx au premier plan (daemon off) pour garder le conteneur actif
echo "Starting Nginx on port $PORT..."
nginx -g "daemon off;"
