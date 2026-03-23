#!/bin/bash
#############################################
# Premier déploiement PRODUCTION
# Tricycle App - OKAMI Sarl
# tricycle.okamisarl.org
#
# Exécute: migrations + SuperAdmin uniquement
#############################################
set -e

APP_DIR="/var/www/tricycle_prod"
BRANCH="production"
REPO="git@github.com:AshDest/tricycle_app.git"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}============================================="
echo "🚀 Premier déploiement PRODUCTION"
echo "   Domaine: tricycle.okamisarl.org"
echo "=============================================${NC}"
echo ""

# 1. Créer le répertoire
echo -e "${GREEN}[1/12] Création du répertoire...${NC}"
sudo mkdir -p $APP_DIR
sudo chown -R deploy:deploy $APP_DIR
cd $APP_DIR

# 2. Cloner le repo
if [ ! -d ".git" ]; then
    echo -e "${GREEN}[2/12] Clonage du repository (branche $BRANCH)...${NC}"
    git clone -b $BRANCH $REPO .
else
    echo -e "${GREEN}[2/12] Mise à jour du repository...${NC}"
    git fetch origin
    git checkout $BRANCH
    git pull origin $BRANCH
fi

# 3. Vérifier le .env
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo ""
    echo -e "${YELLOW}⚠️  Fichier .env créé à partir de .env.example${NC}"
    echo -e "${YELLOW}   MODIFIEZ-LE AVANT DE CONTINUER :${NC}"
    echo ""
    echo "   nano $APP_DIR/.env"
    echo ""
    echo "   Paramètres OBLIGATOIRES à changer :"
    echo "   - APP_ENV=production"
    echo "   - APP_DEBUG=false"
    echo "   - APP_URL=https://tricycle.okamisarl.org"
    echo "   - DB_DATABASE=tricycle_prod_db"
    echo "   - DB_USERNAME=tricycle_prod_user"
    echo "   - DB_PASSWORD=<votre_mot_de_passe>"
    echo ""
    echo -e "${RED}   Relancez ce script après avoir configuré le .env${NC}"
    exit 1
fi

# 4. Installer les dépendances PHP
echo -e "${GREEN}[3/12] Installation Composer...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Générer la clé
echo -e "${GREEN}[4/12] Génération de la clé d'application...${NC}"
php artisan key:generate --force

# 6. Build assets
echo -e "${GREEN}[5/12] Build des assets...${NC}"
npm ci
npm run build

# 7. Lien storage
echo -e "${GREEN}[6/12] Lien storage...${NC}"
php artisan storage:link

# 8. Migrations (sans seeds)
echo -e "${GREEN}[7/12] Migrations de la base de données...${NC}"
php artisan migrate --force

# 9. Seed UNIQUEMENT le SuperAdmin + Rôles/Permissions
echo -e "${GREEN}[8/12] Création du Super Admin...${NC}"
php artisan db:seed --class=ProductionSeeder --force

# 10. Permissions fichiers
echo -e "${GREEN}[9/12] Configuration des permissions...${NC}"
sudo chown -R www-data:www-data $APP_DIR/storage
sudo chown -R www-data:www-data $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# 11. Cache et optimisation
echo -e "${GREEN}[10/12] Optimisation...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# 12. Configuration Nginx
echo -e "${GREEN}[11/12] Configuration Nginx...${NC}"
sudo cp $APP_DIR/scripts/nginx-tricycle-prod.conf /etc/nginx/sites-available/tricycle_prod
sudo ln -sf /etc/nginx/sites-available/tricycle_prod /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 13. Configuration Supervisor
echo -e "${GREEN}[12/12] Configuration Supervisor...${NC}"
sudo cp $APP_DIR/scripts/supervisor-tricycle-prod.conf /etc/supervisor/conf.d/tricycle-prod-queue-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tricycle-prod-queue-worker:*

# 14. Cron Job Scheduler
echo -e "${GREEN}[BONUS] Configuration du Scheduler (Cron)...${NC}"
CRON_JOB="* * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
CRON_EXISTS=$(crontab -l 2>/dev/null | grep -F "tricycle_prod" | grep -F "schedule:run" || true)
if [ -z "$CRON_EXISTS" ]; then
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "   ✅ Cron job ajouté"
else
    echo "   ℹ️  Cron job déjà configuré"
fi

echo ""
echo -e "${GREEN}============================================="
echo "✅ DÉPLOIEMENT PRODUCTION TERMINÉ !"
echo "=============================================${NC}"
echo ""
echo "🌐 URL: https://tricycle.okamisarl.org"
echo ""
echo "📧 Compte Super Admin :"
echo "   Email:    superadmin@okamisarl.org"
echo "   Mot de passe: OkamiAdmin@2026!"
echo ""
echo -e "${RED}⚠️  CHANGEZ LE MOT DE PASSE IMMÉDIATEMENT !${NC}"
echo ""
echo -e "${YELLOW}📌 Prochaines étapes :${NC}"
echo "   1. Configurez le DNS : tricycle.okamisarl.org → 102.223.210.91"
echo "   2. SSL : sudo certbot --nginx -d tricycle.okamisarl.org"
echo "   3. Connectez-vous et changez le mot de passe"
echo "   4. Créez les comptes Admin depuis l'interface Super Admin"

