# Configuration du Scheduler et Queue Worker - Tricycle App

## 1. Queue Worker avec Supervisor (Envoi des emails)

Le Queue Worker est essentiel pour l'envoi automatique des emails de notification. Sans lui, les emails restent en attente dans la base de données.

### Installation automatique

```bash
# Se connecter au serveur
ssh deploy@102.223.210.91

# Exécuter le script d'installation
cd /var/www/tricycle_app
sudo bash scripts/install-supervisor-queue.sh
```

### Installation manuelle

```bash
# 1. Installer Supervisor
sudo apt-get update
sudo apt-get install -y supervisor

# 2. Activer Supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor

# 3. Copier la configuration
sudo cp /var/www/tricycle_app/scripts/supervisor-tricycle.conf /etc/supervisor/conf.d/tricycle-queue.conf

# 4. Recharger et démarrer
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tricycle-queue-worker:*
```

### Commandes utiles Supervisor

```bash
# Voir le statut des workers
sudo supervisorctl status

# Redémarrer les workers (après un déploiement)
sudo supervisorctl restart tricycle-queue-worker:*

# Arrêter les workers
sudo supervisorctl stop tricycle-queue-worker:*

# Voir les logs des workers
tail -f /var/www/tricycle_app/storage/logs/queue-worker.log
```

---

## 2. Scheduler Laravel (Notifications quotidiennes)

### Tâches planifiées actuelles

| Commande | Fréquence | Description |
|----------|-----------|-------------|
| `notifications:quotidiennes` | Chaque jour à 7h00 | Envoie les notifications d'arriérés, contrats expirants, maintenances programmées |

### Configuration du cron job

```bash
# Ouvrir l'éditeur de cron
crontab -e

# Ajouter cette ligne à la fin du fichier :
* * * * * cd /var/www/tricycle_app && php artisan schedule:run >> /dev/null 2>&1
```

### Vérification

```bash
# Lister les crons actifs
crontab -l

# Vérifier le service cron
sudo systemctl status cron

# Voir les tâches planifiées
php artisan schedule:list

# Exécuter manuellement pour test
php artisan schedule:run
php artisan notifications:quotidiennes
```

---

## 3. Configuration complète après déploiement

Après chaque déploiement, exécutez :

```bash
cd /var/www/tricycle_app

# Redémarrer les workers de queue (pour prendre en compte les changements)
sudo supervisorctl restart tricycle-queue-worker:*

# Vider le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Dépannage

### Les emails ne sont pas envoyés

1. Vérifiez que le Queue Worker est actif :
   ```bash
   sudo supervisorctl status
   ```

2. Vérifiez les jobs en attente :
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count()
   ```

3. Vérifiez les jobs échoués :
   ```bash
   php artisan queue:failed
   ```

4. Relancer les jobs échoués :
   ```bash
   php artisan queue:retry all
   ```

5. Consultez les logs :
   ```bash
   tail -f storage/logs/queue-worker.log
   tail -f storage/logs/laravel.log
   ```

### Le scheduler ne s'exécute pas

1. Vérifiez que le cron est configuré : `crontab -l`
2. Vérifiez que le service cron est actif : `sudo systemctl status cron`
3. Testez manuellement : `php artisan schedule:run`

### Vérifier la configuration email

Dans l'interface SuperAdmin, allez dans **Configuration Emails** pour :
- Tester l'envoi d'email
- Voir les jobs en attente/échoués
- Activer/désactiver les emails

---

## 5. Architecture des notifications

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│   Action User   │      │  NotificationS. │      │   Queue (DB)    │
│  (ex: Accident) │ ───▶ │   envoyerEmail  │ ───▶ │   jobs table    │
└─────────────────┘      └─────────────────┘      └────────┬────────┘
                                                          │
                                                          ▼
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│  Email envoyé   │ ◀─── │   Mailer SMTP   │ ◀─── │  Queue Worker   │
│   (Gmail...)    │      │                 │      │  (Supervisor)   │
└─────────────────┘      └─────────────────┘      └─────────────────┘
```


