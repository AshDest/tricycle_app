#!/bin/bash
# Script de déploiement manuel PRODUCTION
# Usage: ./deploy-manual-prod.sh

set -e

REPO_PATH="/var/www/tricycle_prod"
BRANCH="production"
LOG_FILE="$REPO_PATH/storage/logs/deploy.log"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

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

log "=== DÉBUT DU DÉPLOIEMENT MANUEL PRODUCTION ==="

cd "$REPO_PATH" || { error "Impossible d'accéder à $REPO_PATH"; exit 1; }

# Mode maintenance
log "Activation du mode maintenance..."
php artisan down --retry=60 || true

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

# 4. Build des assets
if command -v npm &> /dev/null; then
    log "Compilation des assets..."
    npm ci
    npm run build
else
    warn "npm non disponible, assets ignorés"
fi

# 5. Publier les assets Livewire (requis avec route:cache)
log "Publication des assets Livewire..."
php artisan livewire:publish --assets

# 6. Lien storage
log "Vérification du lien storage..."
php artisan storage:link --force 2>/dev/null || true

# 7. Cache et optimisation
log "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# 8. Permissions
log "Configuration des permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
sudo chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 9. Redémarrer les services
log "Redémarrage de PHP-FPM..."
sudo systemctl reload php8.2-fpm 2>/dev/null || true

log "Redémarrage de Nginx..."
sudo systemctl reload nginx 2>/dev/null || true

log "Redémarrage du queue worker..."
sudo supervisorctl restart tricycle-prod-queue-worker:* 2>/dev/null || true

# Désactiver le mode maintenance
php artisan up

log "=== DÉPLOIEMENT PRODUCTION TERMINÉ ==="
echo ""
echo -e "${GREEN}✓ Déploiement production terminé!${NC}"
echo "  Branche: $BRANCH"
echo "  Commit:  $(git rev-parse --short HEAD)"
echo "  URL:     https://tricycle.okamisarl.org"
