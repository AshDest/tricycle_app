# 🚀 Déploiement Tricycle App
## Prérequis
- VPS Debian (102.223.210.91) avec PHP, Composer, Node.js, Nginx, MariaDB déjà installés
- Utilisateur `deploy` existant
---
## 📋 Configuration (une seule fois)
### 1. Sur le VPS - Préparer le répertoire
```bash
ssh deploy@102.223.210.91
# Créer le dossier de l'application
sudo mkdir -p /var/www/tricycle_app
sudo chown -R deploy:deploy /var/www/tricycle_app
# S'assurer que deploy peut recharger PHP-FPM (si pas déjà fait)
sudo visudo
# Ajouter: deploy ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.2-fpm
```
### 2. Sur le VPS - Créer la base de données
```bash
sudo mysql
```
```sql
CREATE DATABASE tricycle_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tricycle_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse';
GRANT ALL PRIVILEGES ON tricycle_db.* TO 'tricycle_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
### 3. Sur GitHub - Configurer les Secrets
**Repository → Settings → Secrets → Actions**
| Secret | Valeur |
|--------|--------|
| `VPS_HOST` | `102.223.210.91` |
| `VPS_USERNAME` | `deploy` |
| `VPS_PORT` | `22` |
| `VPS_SSH_KEY` | Clé privée SSH de deploy |
| `REPO_URL` | `git@github.com:VOTRE_USER/tricycle_app.git` |
### 4. Pousser le code
```bash
cd /home/ash/Documents/laravel/tricycle_app
git init
git remote add origin git@github.com:VOTRE_USER/tricycle_app.git
git add .
git commit -m "Initial commit"
git branch -M main
git push -u origin main
```
---
## 🚀 Premier Déploiement
### Étape 1: Cloner et configurer .env sur le VPS
```bash
ssh deploy@102.223.210.91
cd /var/www/tricycle_app
# Cloner le projet
git clone git@github.com:VOTRE_USER/tricycle_app.git .
# Configurer l'environnement
cp .env.example .env
nano .env
```
**Modifier dans .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://102.223.210.91
DB_DATABASE=tricycle_db
DB_USERNAME=tricycle_user
DB_PASSWORD=VotreMotDePasse
```
### Étape 2: Lancer le premier déploiement via GitHub Actions
1. Aller sur **GitHub → Actions → "First Deploy (avec Seeds)"**
2. Cliquer **Run workflow**
3. Cocher ✅ "Exécuter les seeders"
4. Cliquer **Run workflow**
### Étape 3: Configurer Nginx (si nouveau site)
```bash
sudo nano /etc/nginx/sites-available/tricycle_app
```
```nginx
server {
    listen 80;
    server_name 102.223.210.91;
    root /var/www/tricycle_app/public;
    index index.php;
    client_max_body_size 50M;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(env|git) {
        deny all;
    }
}
```
```bash
sudo ln -sf /etc/nginx/sites-available/tricycle_app /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```
---
## ✅ Déploiements Automatiques
Chaque `git push` ou merge sur `main` déclenche automatiquement le déploiement !
```bash
git add .
git commit -m "Ma modification"
git push
# → Déploiement automatique sur le VPS
```
---
## 🌐 Accès
- **URL**: http://102.223.210.91
- **Admin**: admin@nth.com / password
---
## 🔧 Commandes utiles sur le VPS
```bash
# Voir les logs
tail -f /var/www/tricycle_app/storage/logs/laravel.log
# Vider le cache
cd /var/www/tricycle_app && php artisan cache:clear
# Mode maintenance
php artisan down
php artisan up
```
