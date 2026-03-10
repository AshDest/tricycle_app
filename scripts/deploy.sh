#!/bin/bash
#############################################
# Script de déploiement pour Debian VPS
# Tricycle App - New Technology Hub Sarl
#############################################
set -e
APP_DIR="/var/www/tricycle_app"
BRANCH="main"
echo "🚀 Démarrage du déploiement..."
cd $APP_DIR
# Mode maintenance
php artisan down --message="Mise à jour en cours..." --retry=60 || true
# Pull des dernières modifications
echo "📥 Récupération du code..."
git pull origin $BRANCH
# Installer les dépendances PHP
echo "📦 Installation Composer..."
composer install --no-dev --optimize-autoloader --no-interaction
# Build assets
echo "📦 Build des assets..."
npm ci && npm run build
# Exécuter les migrations
echo "🗄️ Migrations..."
php artisan migrate --force
# Cache et optimisation
echo "⚡ Optimisation..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
# Redémarrer les services (Debian)
echo "🔄 Redémarrage des services..."
sudo systemctl reload php8.2-fpm || true
sudo systemctl reload nginx || true

# Redémarrer le queue worker (pour prendre en compte les nouvelles notifications)
echo "🔄 Redémarrage du queue worker..."
sudo supervisorctl restart tricycle-queue-worker:* || echo "⚠️ Queue worker non configuré (optionnel)"

# Désactiver le mode maintenance
php artisan up
echo "✅ Déploiement terminé avec succès!"
