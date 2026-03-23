# 🚀 Déploiement Production - tricycle.okamisarl.org

## Architecture

| | Staging | Production |
|---|---|---|
| **Branche** | `main` | `production` |
| **Domaine** | tricycle.newtechnologyhub.org | tricycle.okamisarl.org |
| **Répertoire** | `/var/www/tricycle_app` | `/var/www/tricycle_prod` |
| **Base de données** | `tricycle_db` | `tricycle_prod_db` |
| **Queue Worker** | `tricycle-queue-worker` | `tricycle-prod-queue-worker` |
| **Déclencheur** | Push sur `main` | Push sur `production` |

## Workflow de travail

```
feature → PR → merge dans main → déploie sur staging (test)
                      ↓
              Quand validé / stable :
              merge main → production → déploie en production
```

---

## Premier déploiement (une seule fois)

### 1. Préparer le DNS
Chez votre registrar DNS, ajoutez un enregistrement A :
```
tricycle.okamisarl.org → 102.223.210.91
```

### 2. Sur le serveur (SSH)

```bash
# Créer la base de données
sudo mysql -e "CREATE DATABASE tricycle_prod_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'tricycle_prod_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE';"
sudo mysql -e "GRANT ALL PRIVILEGES ON tricycle_prod_db.* TO 'tricycle_prod_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Cloner le repo
sudo mkdir -p /var/www/tricycle_prod
sudo chown -R deploy:deploy /var/www/tricycle_prod
cd /var/www/tricycle_prod
git clone -b production https://github.com/AshDest/tricycle_app.git .

# Configurer le .env
cp .env.production .env
nano .env   # Remplir: DB_PASSWORD, MAIL_*, APP_KEY (sera généré)

# Lancer le script d'installation
chmod +x scripts/first-deploy-prod.sh
./scripts/first-deploy-prod.sh

# SSL
sudo certbot --nginx -d tricycle.okamisarl.org
```

### 3. Vérification
- Accédez à https://tricycle.okamisarl.org
- Connectez-vous avec `superadmin@okamisarl.org` / `OkamiAdmin@2026!`
- **Changez le mot de passe immédiatement**
- Créez les comptes Admin depuis l'interface

---

## Déploiement automatique (quotidien)

Le déploiement se fait **automatiquement** via GitHub Actions à chaque push/merge sur la branche `production`.

### Pour mettre à jour la production :
```bash
# Depuis votre PC local
git checkout main
git pull
git checkout production
git merge main
git push origin production
# → GitHub Actions déploie automatiquement
```

### Déploiement manuel (si nécessaire)
```bash
# Sur le serveur
cd /var/www/tricycle_prod
chmod +x scripts/deploy-manual-prod.sh
./scripts/deploy-manual-prod.sh
```

---

## Commandes utiles sur le serveur

```bash
# Voir les logs production
tail -f /var/www/tricycle_prod/storage/logs/laravel.log

# Voir le queue worker
sudo supervisorctl status tricycle-prod-queue-worker:*

# Redémarrer le queue worker
sudo supervisorctl restart tricycle-prod-queue-worker:*

# Mode maintenance
cd /var/www/tricycle_prod && php artisan down
cd /var/www/tricycle_prod && php artisan up
```

