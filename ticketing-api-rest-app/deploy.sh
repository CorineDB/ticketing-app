#!/usr/bin/env bash
# =====================================================================
# deploy.sh — Déploiement de l'API sur le serveur EC2
#
# Utilisé par GitHub Actions (workflow deploy-backend.yml) ET en manuel :
#   cd /var/www/ticketing-app/ticketing-api-rest-app && ./deploy.sh
#
# Équivalent local de : git pull + composer install + migrate + restart
# =====================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "==> git pull"
git pull --ff-only

echo "==> composer install (--no-dev)"
composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$APP_DIR"

echo "==> migrations"
php artisan migrate --force

echo "==> cache (config/routes/views)"
php artisan optimize

echo "==> permissions storage"
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || sudo chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "==> redémarrage des services"
sudo systemctl restart ticketing-worker ticketing-reverb
sudo systemctl status ticketing-worker ticketing-reverb --no-pager || true

echo ""
echo "✅ Déploy terminé — $(date '+%Y-%m-%d %H:%M:%S')"
