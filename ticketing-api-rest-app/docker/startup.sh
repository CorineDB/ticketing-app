#!/bin/bash

# Arrête le script dès qu'une erreur survient (très important pour ne pas lancer un serveur cassé)
set -e

# Définition du rôle par défaut (web) si la variable n'existe pas
ROLE=${CONTAINER_ROLE:-web}

echo "🤖 Démarrage du conteneur en mode : $ROLE"

if [ "$ROLE" = "worker" ]; then
    # --- MODE WORKER ---
    # Ne lance PAS Nginx. Ne lance PAS les migrations (pour éviter les conflits).
    # Lance juste le traitement des queues.

    echo "✅ Lancement du Worker Laravel..."
    php artisan queue:work --tries=3 --timeout=90

elif [ "$ROLE" = "scheduler" ]; then
    # --- MODE SCHEDULER (Cron) ---
    # Pour les tâches planifiées (emails auto, nettoyage...)

    echo "⏰ Lancement du Scheduler..."
    php artisan schedule:work

else
    # --- MODE WEB (Par défaut) ---
    # C'est lui qui gère la base de données et le trafic HTTP.

    # 1. Configurer le port Nginx (Requis par Railway)
    PORT=${PORT:-8080}
    echo "Configuration du port Nginx sur $PORT..."
    sed -i "s/8080/$PORT/g" /etc/nginx/conf.d/default.conf

    # 2. Exécuter les MIGRATIONS (Seulement le web le fait)
    echo "🚀 Exécution des migrations..."
    # L'option --force est obligatoire en prod pour éviter la question "Are you sure?"
    php artisan migrate --force

    # 3. 🌱 EXÉCUTION DES SEEDERS
    # ATTENTION : Assure-toi que tes seeders gèrent les doublons (utilisent firstOrCreate)
    # Sinon, commente cette ligne après le premier déploiement pour éviter les erreurs.
    echo "🌱 Exécution des seeders..."
    php artisan db:seed --force

    # 4. Mise en cache pour la PROD
    echo "⚡ Mise en cache de la configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # 5. Démarrage du Serveur Web
    echo "🌍 Démarrage de Nginx et PHP-FPM..."

    # 5. Démarrage des services
    echo "Démarrage de PHP-FPM..."
    php-fpm -D

    echo "Démarrage de Nginx..."
    nginx -g "daemon off;"
fi
