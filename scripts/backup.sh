#!/bin/bash
# =============================================================================
#  Script de backup IBIG FactPro — MySQL + Fichiers
#  Usage : bash /var/www/factpro/scripts/backup.sh
#  Cron recommandé : 0 2 * * * /var/www/factpro/scripts/backup.sh >> /var/log/factpro-backup.log 2>&1
# =============================================================================

set -euo pipefail

# ─── Configuration ───────────────────────────────────────────────────────────
APP_DIR="/var/www/factpro"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/factpro}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="factpro_${DATE}"

# Charger les variables depuis .env
if [[ -f "$APP_DIR/.env" ]]; then
    set -a
    # Extraire uniquement les variables DB (éviter les injections)
    DB_CONNECTION=$(grep '^DB_CONNECTION=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    DB_HOST=$(grep '^DB_HOST=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    DB_PORT=$(grep '^DB_PORT=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    DB_DATABASE=$(grep '^DB_DATABASE=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    DB_USERNAME=$(grep '^DB_USERNAME=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    DB_PASSWORD=$(grep '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    set +a
else
    echo "[ERROR] Fichier .env introuvable : $APP_DIR/.env"
    exit 1
fi

# Valeurs par défaut
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

# ─── Couleurs ────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"; }
error() { echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"; exit 1; }

# ─── Création du dossier de backup ───────────────────────────────────────────
log "Démarrage du backup IBIG FactPro..."
mkdir -p "$BACKUP_DIR"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
mkdir -p "$BACKUP_PATH"

# ─── 1. Backup MySQL ─────────────────────────────────────────────────────────
log "Backup base de données MySQL : $DB_DATABASE..."

if [[ -z "$DB_DATABASE" ]]; then
    error "DB_DATABASE non configuré dans .env"
fi

MYSQL_DUMP_FILE="$BACKUP_PATH/${DB_DATABASE}.sql.gz"

# Utiliser un fichier de config temporaire pour éviter le mot de passe en clair
MYSQL_CONF=$(mktemp /tmp/.my.cnf.XXXXXX)
chmod 600 "$MYSQL_CONF"
cat > "$MYSQL_CONF" << EOF
[mysqldump]
host=$DB_HOST
port=$DB_PORT
user=$DB_USERNAME
password=$DB_PASSWORD
EOF

mysqldump \
    --defaults-extra-file="$MYSQL_CONF" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    --extended-insert \
    "$DB_DATABASE" | gzip -9 > "$MYSQL_DUMP_FILE"

rm -f "$MYSQL_CONF"

DUMP_SIZE=$(du -sh "$MYSQL_DUMP_FILE" | cut -f1)
log "Base de données sauvegardée : $MYSQL_DUMP_FILE ($DUMP_SIZE)"

# ─── 2. Backup des fichiers storage/ ─────────────────────────────────────────
log "Backup des fichiers storage/..."

STORAGE_ARCHIVE="$BACKUP_PATH/storage_${DATE}.tar.gz"

tar -czf "$STORAGE_ARCHIVE" \
    -C "$APP_DIR" \
    --exclude="storage/logs/*.log" \
    --exclude="storage/framework/cache/*" \
    --exclude="storage/framework/sessions/*" \
    --exclude="storage/framework/views/*" \
    storage/app/ \
    2>/dev/null || true

if [[ -f "$STORAGE_ARCHIVE" ]]; then
    STORAGE_SIZE=$(du -sh "$STORAGE_ARCHIVE" | cut -f1)
    log "Fichiers storage sauvegardés : $STORAGE_ARCHIVE ($STORAGE_SIZE)"
else
    log "Aucun fichier storage à sauvegarder"
fi

# ─── 3. Backup du .env (chiffré) ─────────────────────────────────────────────
log "Backup du fichier .env..."
cp "$APP_DIR/.env" "$BACKUP_PATH/.env.bak"
chmod 600 "$BACKUP_PATH/.env.bak"
log "Fichier .env sauvegardé"

# ─── 4. Archive finale ───────────────────────────────────────────────────────
log "Création de l'archive finale..."
FINAL_ARCHIVE="$BACKUP_DIR/${BACKUP_NAME}.tar.gz"

tar -czf "$FINAL_ARCHIVE" \
    -C "$BACKUP_DIR" \
    "$BACKUP_NAME/"

rm -rf "$BACKUP_PATH"

FINAL_SIZE=$(du -sh "$FINAL_ARCHIVE" | cut -f1)
log "Archive finale : $FINAL_ARCHIVE ($FINAL_SIZE)"

# ─── 5. Nettoyage des anciens backups ────────────────────────────────────────
log "Nettoyage des backups de plus de $RETENTION_DAYS jours..."
DELETED_COUNT=0

while IFS= read -r old_backup; do
    rm -f "$old_backup"
    log "Supprimé : $old_backup"
    ((DELETED_COUNT++))
done < <(find "$BACKUP_DIR" -name "factpro_*.tar.gz" -mtime +"$RETENTION_DAYS" 2>/dev/null)

log "$DELETED_COUNT ancien(s) backup(s) supprimé(s)"

# ─── 6. Listing des backups disponibles ──────────────────────────────────────
log "Backups disponibles :"
find "$BACKUP_DIR" -name "factpro_*.tar.gz" -printf "  %TY-%Tm-%Td %TH:%TM  %s bytes  %f\n" 2>/dev/null | sort

# ─── 7. Envoi vers stockage distant (optionnel) ──────────────────────────────
# Décommenter et configurer si vous souhaitez envoyer vers S3 ou un serveur distant

# Vers S3 (nécessite aws-cli)
# if command -v aws &>/dev/null && [[ -n "${AWS_BUCKET:-}" ]]; then
#     log "Upload vers S3 : s3://$AWS_BUCKET/backups/"
#     aws s3 cp "$FINAL_ARCHIVE" "s3://$AWS_BUCKET/backups/${BACKUP_NAME}.tar.gz"
#     log "Upload S3 terminé"
# fi

# Vers serveur distant via SFTP/rsync
# if [[ -n "${BACKUP_REMOTE_HOST:-}" ]]; then
#     log "Upload vers $BACKUP_REMOTE_HOST..."
#     rsync -azq "$FINAL_ARCHIVE" "${BACKUP_REMOTE_USER:-backup}@${BACKUP_REMOTE_HOST}:${BACKUP_REMOTE_PATH:-/backups/factpro}/"
#     log "Upload distant terminé"
# fi

# ─── Résumé ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     Backup IBIG FactPro terminé            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════╝${NC}"
echo -e " Fichier : $FINAL_ARCHIVE"
echo -e " Taille  : $FINAL_SIZE"
echo -e " Date    : $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
log "=== FIN DU BACKUP ==="
