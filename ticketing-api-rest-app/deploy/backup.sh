#!/usr/bin/env bash
# =====================================================================
# backup.sh — Sauvegarde quotidienne (cron 03h15, ajouté par setup-server.sh)
#
# - pg_dump du PostgreSQL local (ticketing_db)
# - archive de storage/app (QR codes, images d'événements)
# - rotation : 7 jours de conservés dans /var/backups/ticketing
#
# Relance manuelle : bash deploy/backup.sh
# =====================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="/var/backups/ticketing"
DB_NAME="ticketing_db"
DATE="$(date +%F)"
KEEP_DAYS=7

mkdir -p "$BACKUP_DIR"
chmod 755 "$BACKUP_DIR" 2>/dev/null || true

# --- Base de données ---
if [ "$(id -u)" -eq 0 ]; then
    pg_dump -Fc "$DB_NAME" > "$BACKUP_DIR/db_${DATE}.dump"
else
    sudo -u postgres pg_dump -Fc "$DB_NAME" > "$BACKUP_DIR/db_${DATE}.dump"
fi
echo "DB sauvegardée : $BACKUP_DIR/db_${DATE}.dump ($(du -h "$BACKUP_DIR/db_${DATE}.dump" | cut -f1))"

# --- Fichiers storage/app (QR codes, images) ---
if [ -d "$APP_DIR/storage/app" ]; then
    tar czf "$BACKUP_DIR/storage_${DATE}.tgz" -C "$APP_DIR/storage" app 2>/dev/null || true
    echo "Storage archivé : $BACKUP_DIR/storage_${DATE}.tgz"
fi

# --- Rotation 7 jours ---
find "$BACKUP_DIR" -type f -mtime +$KEEP_DAYS -delete
echo "Rotation OK — fichiers > $KEEP_DAYS jours supprimés"
echo "Contenu :"
ls -lh "$BACKUP_DIR" | tail -n +2
