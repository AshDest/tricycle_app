#!/bin/bash
# Script de déploiement manuel
# Usage: ./deploy-manual.sh

set -e

REPO_PATH="/var/www/tricycle_app"
BRANCH="main"
LOG_FILE="$REPO_PATH/storage/logs/deploy.log"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERREUR]${NC} $1"
    echo "[ERREUR] $1" >> "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[ATTENTION]${NC} $1"
}

log "=== DÉBUT DU DÉPLOIEMENT MANUEL ==="

cd "$REPO_PATH" || { error "Impossible d'accéder à $REPO_PATH"; exit 1; }

# 1. Récupérer les dernières modifications
log "Récupération des modifications depuis GitHub..."
git stash --include-untracked || true
git fetch origin "$BRANCH"
git reset --hard origin/"$BRANCH"

# 2. Installer les dépendances PHP
log "Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Migrations de base de données
log "Exécution des migrations..."
php artisan migrate --force

# 4. Cache de configuration
log "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Lien storage (si nécessaire)
log "Vérification du lien storage..."
php artisan storage:link --force 2>/dev/null || true

# 6. Assets (si npm est disponible)
if command -v npm &> /dev/null; then
    log "Compilation des assets..."
    npm install --production 2>/dev/null || true
    npm run build 2>/dev/null || true
else
    warn "npm non disponible, assets ignorés"
fi

# 7. Permissions
log "Configuration des permissions..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 8. Redémarrer les services si nécessaire
log "Redémarrage de PHP-FPM..."
sudo systemctl reload php8.2-fpm 2>/dev/null || sudo systemctl reload php-fpm 2>/dev/null || true

log "=== DÉPLOIEMENT TERMINÉ AVEC SUCCÈS ==="
echo ""
echo -e "${GREEN}✓ Déploiement terminé!${NC}"
echo "  Branche: $BRANCH"
echo "  Commit: $(git rev-parse --short HEAD)"
echo "  Date: $(date)"

