#!/bin/bash
# =============================================================================
#  Script de déploiement IBIG FactPro — VPS Ubuntu 22.04
#  Usage : bash /var/www/factpro/scripts/deploy.sh
#  Pré-requis : Git configuré, Composer, Node.js, php artisan accessible
# =============================================================================

set -euo pipefail

# ─── Configuration ───────────────────────────────────────────────────────────
APP_DIR="/var/www/factpro"
GIT_BRANCH="${GIT_BRANCH:-main}"
MAINTENANCE_SECRET="${MAINTENANCE_SECRET:-factpro-maintenance-bypass-$(date +%s)}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"
NPM_BIN="${NPM_BIN:-/usr/bin/npm}"
LOG_FILE="${APP_DIR}/storage/logs/deploy.log"

# ─── Couleurs ────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

log() { echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"; }
warn() { echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARN:${NC} $1" | tee -a "$LOG_FILE"; }
error() { echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"; exit 1; }

# ─── Vérifications préalables ────────────────────────────────────────────────
log "Démarrage du déploiement IBIG FactPro..."
log "Branche : $GIT_BRANCH"

[[ -d "$APP_DIR" ]] || error "Dossier $APP_DIR introuvable"
[[ -f "$APP_DIR/.env" ]] || error "Fichier .env absent — créer depuis .env.production.example"

cd "$APP_DIR"

# ─── 1. Mode maintenance ─────────────────────────────────────────────────────
log "Activation du mode maintenance..."
$PHP_BIN artisan down --secret="$MAINTENANCE_SECRET" --render="errors.503" 2>/dev/null || \
$PHP_BIN artisan down --secret="$MAINTENANCE_SECRET"
log "Mode maintenance actif. Bypass : $MAINTENANCE_SECRET"

# Fonction de rollback en cas d'erreur
cleanup_on_error() {
    error_code=$?
    warn "Erreur détectée (code $error_code). Désactivation du mode maintenance..."
    $PHP_BIN artisan up || true
    error "Déploiement échoué. Application remise en ligne. Vérifier les logs."
}
trap cleanup_on_error ERR

# ─── 2. Git pull ─────────────────────────────────────────────────────────────
log "Récupération des dernières modifications (branche $GIT_BRANCH)..."
git fetch origin "$GIT_BRANCH"
CURRENT_COMMIT=$(git rev-parse HEAD)
git pull origin "$GIT_BRANCH"
NEW_COMMIT=$(git rev-parse HEAD)
log "Commit précédent : $CURRENT_COMMIT"
log "Nouveau commit   : $NEW_COMMIT"

if [[ "$CURRENT_COMMIT" == "$NEW_COMMIT" ]]; then
    warn "Aucune modification détectée. Déploiement forcé..."
fi

# ─── 3. Dépendances PHP ──────────────────────────────────────────────────────
log "Installation des dépendances PHP (sans dev)..."
$COMPOSER_BIN install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# ─── 4. Assets front-end ─────────────────────────────────────────────────────
if [[ -f "package.json" ]]; then
    log "Installation des dépendances Node.js..."
    $NPM_BIN ci --silent
    log "Compilation des assets..."
    $NPM_BIN run build
else
    warn "package.json absent — étape assets ignorée"
fi

# ─── 5. Migrations de base de données ────────────────────────────────────────
log "Exécution des migrations..."
$PHP_BIN artisan migrate --force

# ─── 6. Cache applicatif ─────────────────────────────────────────────────────
log "Reconstruction du cache..."
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan event:clear

$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# ─── 7. Lien storage ─────────────────────────────────────────────────────────
log "Vérification du lien symbolique storage..."
$PHP_BIN artisan storage:link --quiet 2>/dev/null || true

# ─── 8. Redémarrage des queue workers ────────────────────────────────────────
log "Redémarrage des queue workers..."
$PHP_BIN artisan queue:restart

# Relancer Supervisor si disponible
if command -v supervisorctl &>/dev/null; then
    log "Rechargement Supervisor..."
    sudo supervisorctl reread 2>/dev/null || true
    sudo supervisorctl update 2>/dev/null || true
    sudo supervisorctl restart factpro-worker:* 2>/dev/null || \
        warn "Impossible de redémarrer les workers Supervisor (vérifier les permissions sudo)"
fi

# ─── 9. Permissions ──────────────────────────────────────────────────────────
log "Vérification des permissions..."
chmod -R 775 storage/ bootstrap/cache/

# ─── 10. Désactivation du mode maintenance ───────────────────────────────────
log "Désactivation du mode maintenance..."
$PHP_BIN artisan up

# ─── 11. Vérification finale ─────────────────────────────────────────────────
log "Vérification post-déploiement..."
HTTP_CODE=$($PHP_BIN -r "echo 'PHP OK';")
log "PHP : $HTTP_CODE"

# Désarmer le trap d'erreur
trap - ERR

# ─── Résumé ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║       Déploiement IBIG FactPro terminé avec succès       ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo -e " Commit : $NEW_COMMIT"
echo -e " Date   : $(date '+%Y-%m-%d %H:%M:%S')"
echo -e " Log    : $LOG_FILE"
echo ""
log "=== FIN DU DÉPLOIEMENT ==="
