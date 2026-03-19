# 🚀 Déploiement Tricycle App

## Prérequis
- VPS Debian (102.223.210.91) avec PHP, Composer, Node.js, Nginx, MariaDB déjà installés
- Utilisateur `deploy` existant
- Domaine: tricycle.newtechnologyhub.org

---

## 📋 Configuration GitHub Actions (une seule fois)

### 1. Configurer les Secrets sur GitHub

**Repository → Settings → Secrets and variables → Actions → New repository secret**

| Secret | Valeur |
|--------|--------|
| `VPS_HOST` | `102.223.210.91` |
| `VPS_USERNAME` | `deploy` |
| `VPS_PORT` | `22` |
| `VPS_SSH_KEY` | Clé privée SSH de deploy (contenu complet du fichier `~/.ssh/tricycle_deploy_key`) |

### 2. Vérifier la clé SSH sur le VPS

```bash
ssh deploy@102.223.210.91
cat ~/.ssh/authorized_keys
```

### 3. Vérifier que le repo est cloné sur le VPS

```bash
ssh deploy@102.223.210.91
cd /var/www/tricycle_app
git status
```

---

## 🚀 Déploiement Automatique

Chaque **merge ou push sur `main`** déclenche automatiquement le déploiement via GitHub Actions.

```bash
git add .
git commit -m "Ma modification"
git push origin main
# → Déploiement automatique sur le VPS !
```

---

## 🌱 Premier Déploiement (avec Seeds)

1. Aller sur **GitHub → Actions → "🌱 First Deploy (avec Seeds)"**
2. Cliquer **Run workflow**
3. Cocher les options souhaitées
4. Cliquer **Run workflow**

---

## 🌐 Accès
- **URL**: https://tricycle.newtechnologyhub.org

---

## 🔧 Commandes utiles sur le VPS
```bash
ssh deploy@102.223.210.91
cd /var/www/tricycle_app

# Logs
tail -f storage/logs/laravel.log

# Cache
php artisan cache:clear

# Maintenance
php artisan down
php artisan up

# Queue worker
sudo supervisorctl status
sudo supervisorctl restart tricycle-queue-worker:*

# Déploiement manuel
bash scripts/deploy-manual.sh
```
