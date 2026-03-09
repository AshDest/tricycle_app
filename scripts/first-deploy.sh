#!/bin/bash
#############################################
# Premier déploiement pour Debian VPS
# Tricycle App - New Technology Hub Sarl
# Exécute migrations + seeds
#############################################
set -e
APP_DIR="/var/www/tricycle_app"
echo "🚀 Premier déploiement sur Debian..."
# Créer le répertoire
sudo mkdir -p $APP_DIR
sudo chown -R deploy:deploy $APP_DIR
cd $APP_DIR
# Cloner le repo si nécessaire
if [ ! -d ".git" ]; then
    echo "📥 Clonage du repository..."
    git clone git@github.com:VOTRE_USERNAME/tricycle_app.git .
else
    echo "📥 Mise à jour du repository..."
    git pull origin main
fi
# Configuration
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "⚠️ Fichier .env créé - MODIFIEZ-LE AVANT DE CONTINUER!"
    echo "   nano $APP_DIR/.env"
    exit 1
fi
# Installer les dépendances
echo "📦 Installation Composer..."
composer install --no-dev --optimize-autoloader
# Générer la clé
php artisan key:generate --force
# Build assets
echo "📦 Build des assets..."
npm ci
npm run build
# Créer le lien storage
php artisan storage:link
# Migrations et Seeds
echo "🗄️ Migrations..."
php artisan migrate --force
echo "🌱 Seeds..."
php artisan db:seed --force
# Permissions (Debian - www-data)
echo "🔐 Permissions..."
sudo chown -R www-data:www-data $APP_DIR/storage
sudo chown -R www-data:www-data $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache
# Cache et optimisation
echo "⚡ Optimisation..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
# Configurer Nginx
echo "🌐 Configuration Nginx..."
sudo cp $APP_DIR/scripts/nginx-tricycle.conf /etc/nginx/sites-available/tricycle_app
sudo ln -sf /etc/nginx/sites-available/tricycle_app /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
# Redémarrer PHP-FPM
sudo systemctl restart php8.2-fpm

# Configurer le Scheduler (Cron Job)
echo "⏰ Configuration du Scheduler..."
CRON_JOB="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
CRON_EXISTS=$(crontab -l 2>/dev/null | grep -F "tricycle_app" | grep -F "schedule:run" || true)
if [ -z "$CRON_EXISTS" ]; then
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "   ✅ Cron job ajouté pour le scheduler"
else
    echo "   ℹ️ Cron job déjà configuré"
fi

echo ""
echo "✅ Premier déploiement terminé!"
echo ""
echo "🌐 L'application est accessible sur: http://102.223.210.91"
echo ""
echo "📝 Comptes créés par défaut:"
echo "   Admin: admin@nth.com / password"
echo "   (Changez le mot de passe immédiatement!)"
