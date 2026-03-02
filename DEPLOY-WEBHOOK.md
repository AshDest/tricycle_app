# Déploiement Automatique - Tricycle App

## Solution sans GitHub Actions (GRATUIT)

Cette solution utilise un **webhook GitHub** directement sur votre serveur pour déclencher le déploiement automatiquement à chaque push sur `main`.

---

## 🚀 Configuration sur le Serveur (VPS)

### 1. Connectez-vous au serveur
```bash
ssh deploy@102.223.210.91
```

### 2. Générer un secret de déploiement
```bash
# Générer un secret aléatoire
openssl rand -hex 32 > /var/www/tricycle_app/.deploy_secret

# Vérifier le secret (notez-le pour GitHub)
cat /var/www/tricycle_app/.deploy_secret
```

### 3. Créer le lien symbolique pour le webhook
```bash
cd /var/www/tricycle_app
ln -sf scripts/webhook-deploy.php public/webhook-deploy.php
```

### 4. Configurer les permissions
```bash
# Le script doit pouvoir exécuter git et composer
sudo chown -R deploy:www-data /var/www/tricycle_app
sudo chmod -R 775 /var/www/tricycle_app
sudo chmod +x scripts/deploy-manual.sh

# Permettre à www-data d'exécuter certaines commandes
sudo visudo
# Ajouter cette ligne:
# www-data ALL=(deploy) NOPASSWD: /usr/bin/git, /usr/local/bin/composer, /usr/bin/php
```

### 5. Alternative: Configurer un cron pour le webhook
Si le webhook PHP a des problèmes de permissions, utilisez cette alternative:

```bash
# Créer un fichier de déclenchement
touch /var/www/tricycle_app/.deploy_trigger

# Ajouter ce cron (vérifie toutes les minutes)
crontab -e
```

Ajoutez:
```
* * * * * [ -f /var/www/tricycle_app/.deploy_trigger ] && /var/www/tricycle_app/scripts/deploy-manual.sh && rm /var/www/tricycle_app/.deploy_trigger
```

---

## 🔗 Configuration sur GitHub

### 1. Aller dans les paramètres du dépôt
- Allez sur https://github.com/AshDest/tricycle_app
- Settings → Webhooks → Add webhook

### 2. Configurer le webhook
- **Payload URL**: `https://tricycle.newtechnologyhub.org/webhook-deploy.php`
- **Content type**: `application/json`
- **Secret**: (le secret généré à l'étape 2 du serveur)
- **Which events?**: `Just the push event`
- **Active**: ✅ Coché

### 3. Cliquer sur "Add webhook"

---

## 📋 Déploiement Manuel

Si vous avez besoin de déployer manuellement:

```bash
ssh deploy@102.223.210.91
cd /var/www/tricycle_app
./scripts/deploy-manual.sh
```

Ou directement:
```bash
ssh deploy@102.223.210.91 "cd /var/www/tricycle_app && ./scripts/deploy-manual.sh"
```

---

## 🔧 Dépannage

### Vérifier les logs de déploiement
```bash
tail -f /var/www/tricycle_app/storage/logs/deploy.log
```

### Vérifier les logs Nginx
```bash
sudo tail -f /var/log/nginx/error.log
```

### Tester le webhook manuellement
```bash
curl -X POST https://tricycle.newtechnologyhub.org/webhook-deploy.php \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature-256: sha256=$(echo -n '{"ref":"refs/heads/main"}' | openssl dgst -sha256 -hmac 'VOTRE_SECRET' | cut -d' ' -f2)" \
  -d '{"ref":"refs/heads/main","after":"test","pusher":{"name":"test"}}'
```

### Permissions insuffisantes
Si le webhook ne peut pas exécuter les commandes:
```bash
# Option 1: Donner les droits à www-data
sudo usermod -aG deploy www-data

# Option 2: Utiliser la solution cron (recommandée)
# Voir section 5 de la configuration serveur
```

---

## 🔄 Workflow de Déploiement

1. **Développement local** → Commits sur une branche feature
2. **Pull Request** → Merge vers `main`
3. **GitHub** → Envoie un webhook au serveur
4. **Serveur** → Exécute le script de déploiement
5. **Application** → Mise à jour automatique

---

## 📁 Fichiers de déploiement

| Fichier | Description |
|---------|-------------|
| `scripts/webhook-deploy.php` | Webhook appelé par GitHub |
| `scripts/deploy-manual.sh` | Script de déploiement manuel |
| `.deploy_secret` | Secret partagé avec GitHub |
| `storage/logs/deploy.log` | Logs de déploiement |

---

## ⚠️ Notes importantes

1. **Le webhook GitHub est GRATUIT** - pas de limite d'utilisation
2. **Sécurité**: Le secret empêche les déploiements non autorisés
3. **Logs**: Tous les déploiements sont loggés dans `storage/logs/deploy.log`
4. **Rollback**: En cas de problème, utilisez `git reset --hard HEAD~1` sur le serveur

