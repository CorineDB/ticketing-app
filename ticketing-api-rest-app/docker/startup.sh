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
    # php artisan queue:work --tries=3 --timeout=90
    # Après : On force le redémarrage du processus après CHAQUE job (--max-jobs=1)
    # et on limite la mémoire PHP à 128Mo pour qu'il échoue proprement avant d'être tué par le système.
    echo "✅ Lancement du Worker Laravel (Mode Optimisé)..."
    #php artisan queue:work --tries=3 --timeout=120 --max-jobs=1 --memory=128
    php artisan queue:listen --tries=3 --timeout=120
    # Ajout de --memory=128 pour forcer l'arrêt si ça dépasse,
    # et suppression du timeout trop long qui garde le processus actif
    #php artisan queue:work --tries=3 --timeout=60 --memory=256

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

    # 2. Storage Link (Toujours utile)

    # [AJOUT] Force les permissions sur le dossier du volume (au cas où)
    echo "🔧 Correction des permissions du stockage..."
    chown -R www-data:www-data /var/www/html/storage/app/public
    chmod -R 775 /var/www/html/storage/app/public

    if [ ! -L public/storage ]; then
        echo "🔗 Création du lien symbolique storage..."
        php artisan storage:link
    fi

    # 3. MIGRATIONS (Contrôlable via variable)
    # Par défaut on le fait (true), sauf si SKIP_MIGRATIONS=true
    if [ "${SKIP_MIGRATIONS:-false}" != "true" ]; then
        echo "🚀 Exécution des migrations..."
        # L'option --force est obligatoire en prod pour éviter la question "Are you sure?"
        php artisan migrate --force
    else
        echo "⏭️ Migrations ignorées (SKIP_MIGRATIONS=true)"
    fi

    # 4. SEEDERS (Contrôlable via variable)
    # Par défaut on NE LE FAIT PAS (false), sauf si RUN_SEEDS=true
    if [ "${RUN_SEEDS:-false}" = "true" ]; then
        # ATTENTION : Assure-toi que tes seeders gèrent les doublons (utilisent firstOrCreate)
        # Sinon, commente cette ligne après le premier déploiement pour éviter les erreurs.
        echo "🌱 Exécution des seeders..."
        php artisan db:seed --force
    else
        echo "⏭️ Seeders ignorés (RUN_SEEDS n'est pas 'true')"
    fi

    # 5. Cache & Démarrage (Toujours faire)
    # 5. Mise en cache pour la PROD
    echo "⚡ Mise en cache..."
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
