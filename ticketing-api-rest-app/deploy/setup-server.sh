#!/usr/bin/env bash
# =====================================================================
# setup-server.sh — Montage du serveur EC2 Ubuntu 24.04
# Traite l'instance EC2 comme une machine Linux perso :
# PHP 8.3 + Nginx + PostgreSQL 16 + Composer + systemd + Certbot + cron.
#
# Utilisation (en tant qu'utilisateur sudo, ex. "ubuntu") :
#   git clone <repo> /var/www/ticketing-app   # ou laisser le script cloner
#   cd /var/www/ticketing-app/ticketing-api-rest-app
#   sudo ./deploy/setup-server.sh             # ou : ./deploy/setup-server.sh
#
# Script idempotent : relançable sans risque.
# Variables surchargeables : REPO_URL, APP_ROOT
# =====================================================================
set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/CorineDB/ticketing-app.git}"
APP_ROOT="${APP_ROOT:-/var/www/ticketing-app}"
APP_DIR="$APP_ROOT/ticketing-api-rest-app"
DOMAIN="api.ticketing.corinebocoga.com"
DB_USER="ticketing"
DB_NAME="ticketing_db"

log()  { echo -e "\n==> $*"; }
warn() { echo -e "    [!] $*"; }

if [ "$(id -u)" -eq 0 ]; then
    SUDO=""
else
    SUDO="sudo"
fi

# ---------------------------------------------------------------------
log "1/10 — Mise à jour du système + swap 1 Go"
$SUDO apt-get update -y
if [ ! -f /swapfile ]; then
    $SUDO fallocate -l 1G /swapfile
    $SUDO chmod 600 /swapfile
    $SUDO mkswap /swapfile
    $SUDO swapon /swapfile
    echo '/swapfile none swap sw 0 0' | $SUDO tee -a /etc/fstab >/dev/null
    warn "swap 1 Go créé"
else
    warn "swap déjà présent"
fi

# ---------------------------------------------------------------------
log "2/10 — Installation de Nginx, PostgreSQL, PHP 8.3, Certbot, Git"
$SUDO apt-get install -y \
    git curl unzip \
    nginx postgresql postgresql-contrib \
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml \
    php8.3-gd php8.3-curl php8.3-bcmath php8.3-zip php8.3-intl php8.3-imagick \
    certbot python3-certbot-nginx

# ---------------------------------------------------------------------
log "3/10 — Composer"
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | $SUDO php -- --install-dir=/usr/local/bin --filename=composer
else
    warn "composer déjà installé ($(composer --version 2>/dev/null | head -1))"
fi

# ---------------------------------------------------------------------
log "4/10 — Base de données PostgreSQL ($DB_NAME)"
if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    # Mot de passe : généré une fois, ou repris du .env existant
    if [ -f "$APP_DIR/.env" ]; then
        DB_PASSWORD="$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | tail -1 | cut -d= -f2-)"
    else
        DB_PASSWORD="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 32)"
    fi
    sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '${DB_PASSWORD}';"
    warn "utilisateur $DB_USER créé (mdp stocké/regénéré dans .env)"
else
    warn "utilisateur $DB_USER déjà présent"
fi
if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1; then
    sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;"
    warn "base $DB_NAME créée"
else
    warn "base $DB_NAME déjà présente"
fi

# ---------------------------------------------------------------------
log "5/10 — Code applicatif ($APP_DIR)"
if [ ! -d "$APP_ROOT/.git" ]; then
    $SUDO mkdir -p "$(dirname "$APP_ROOT")"
    git clone "$REPO_URL" "$APP_ROOT"
else
    (cd "$APP_ROOT" && git pull --ff-only) || warn "git pull impossible (fichiers locaux modifiés ?) — on continue"
fi
if [ ! -d "$APP_DIR" ]; then
    echo "ERREUR: $APP_DIR introuvable après clone ($REPO_URL)." >&2
    echo "Repo privé ? : git clone git@github.com:<org>/<repo>.git $APP_ROOT" >&2
    exit 1
fi
cd "$APP_DIR"

# ---------------------------------------------------------------------
log "6/10 — Dépendances Composer + .env"
composer install --no-dev --optimize-autoloader --no-interaction --working-dir="$APP_DIR"

if [ ! -f .env ]; then
    cp .env.production.example .env
    warn ".env créé depuis le template — À ÉDITER (étape 10)"
fi

# APP_KEY uniquement si absente
if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
    warn "APP_KEY générée"
fi

# Mot de passe DB : remplace le placeholder "changeme" (une seule fois)
if grep -qE '^DB_PASSWORD=changeme$' .env; then
    if [ -n "${DB_PASSWORD:-}" ]; then
        sed -i "s|^DB_PASSWORD=changeme$|DB_PASSWORD=${DB_PASSWORD}|" .env
        warn "mot de passe DB injecté dans .env"
    else
        warn "DB_PASSWORD=changeme toujours présent — le mettre à jour manuellement"
    fi
fi

# ---------------------------------------------------------------------
log "7/10 — Permissions (user www-data)"
$SUDO usermod -aG www-data "$(id -un)" 2>/dev/null || true
$SUDO chown -R "$(id -un):www-data" "$APP_DIR"
$SUDO chmod -R ug+rwX "$APP_DIR"
$SUDO find "$APP_DIR" -type d -exec chmod g+s {} + 2>/dev/null || true
$SUDO mkdir -p "$APP_DIR/storage/app/public" "$APP_DIR/storage/app/private" \
    "$APP_DIR/storage/framework/cache" "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" "$APP_DIR/storage/logs"
$SUDO chown -R "www-data:www-data" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
$SUDO chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ---------------------------------------------------------------------
log "8/10 — Nginx + systemd (worker + reverb)"
$SUDO cp "$APP_DIR/deploy/nginx/$DOMAIN.conf" "/etc/nginx/sites-available/$DOMAIN.conf"
$SUDO ln -sf "/etc/nginx/sites-available/$DOMAIN.conf" "/etc/nginx/sites-enabled/$DOMAIN.conf"
$SUDO rm -f /etc/nginx/sites-enabled/default
$SUDO nginx -t
$SUDO systemctl reload nginx

$SUDO cp "$APP_DIR/deploy/systemd/ticketing-worker.service" /etc/systemd/system/
$SUDO cp "$APP_DIR/deploy/systemd/ticketing-reverb.service"  /etc/systemd/system/
$SUDO systemctl daemon-reload
$SUDO systemctl enable --now php8.3-fpm nginx ticketing-worker ticketing-reverb >/dev/null 2>&1 || true
$SUDO systemctl restart php8.3-fpm ticketing-worker ticketing-reverb

# ---------------------------------------------------------------------
log "9/10 — Migrations, storage:link, cache + crons"
php artisan storage:link || true
php artisan migrate --force
php artisan optimize

# Cron scheduler Laravel (chaque minute) + backup quotidien (03h15)
CRON_SCHEDULE="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
CRON_BACKUP="15 3 * * * bash $APP_DIR/deploy/backup.sh >> $APP_DIR/storage/logs/backup.log 2>&1"
( crontab -l 2>/dev/null | grep -vF "artisan schedule:run" | grep -vF "deploy/backup.sh" ; \
  echo "$CRON_SCHEDULE"; echo "$CRON_BACKUP" ) | crontab -

# ---------------------------------------------------------------------
log "10/10 — Pare-feu UFW"
$SUDO ufw allow 22/tcp >/dev/null
$SUDO ufw allow 80/tcp >/dev/null
$SUDO ufw allow 443/tcp >/dev/null
$SUDO ufw --force enable >/dev/null

# ---------------------------------------------------------------------
cat <<EOF

=========================================================================
 ✅ SERVEUR PRÊT — reste à faire manuellement :

 1. DNS : enregistrement A  "api.ticketing" → IP publique EC2 (Hostinger)
    (puis relancer : sudo certbot --nginx -d $DOMAIN)

 2. Éditer le .env :   nano $APP_DIR/.env
    - DB_PASSWORD (si "changeme")
    - APP_KEY générée automatiquement
    - REVERB_APP_ID / REVERB_APP_KEY / REVERB_APP_SECRET (copier le .env dev)
    - FEDAPAY_* (live), MAIL_*, SMS_*, TOKEN_ENCRYPTION_KEY
    Puis : php artisan optimize && sudo systemctl restart ticketing-worker ticketing-reverb

 3. SSL :        sudo certbot --nginx -d $DOMAIN   (après pointage DNS)

 4. GitHub Secrets : EC2_HOST, EC2_SSH_KEY, EC2_USER=ubuntu
    pour le deploy auto (workflow deploy-backend.yml)

 5. Vérifier :   curl http://localhost/api/health
                 sudo systemctl status ticketing-worker ticketing-reverb
=========================================================================
EOF
