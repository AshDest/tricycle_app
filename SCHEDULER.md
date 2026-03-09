# Configuration du Scheduler Laravel - Tricycle App

## Fonctionnement

Laravel utilise un système de planification de tâches (Scheduler) qui permet d'exécuter des commandes automatiquement à des heures précises.

### Tâches planifiées actuelles

| Commande | Fréquence | Description |
|----------|-----------|-------------|
| `notifications:quotidiennes` | Chaque jour à 7h00 | Envoie les notifications d'arriérés, contrats expirants, maintenances programmées |

## Configuration sur le serveur

### 1. Ajouter le cron job (une seule fois)

Connectez-vous à votre serveur et exécutez :

```bash
# Ouvrir l'éditeur de cron
crontab -e

# Ajouter cette ligne à la fin du fichier :
* * * * * cd /var/www/tricycle_app && php artisan schedule:run >> /dev/null 2>&1
```

Cette ligne exécute le scheduler Laravel **chaque minute**. Laravel vérifie ensuite quelles tâches doivent être exécutées selon leur configuration.

### 2. Vérifier que le cron est actif

```bash
# Lister les crons actifs
crontab -l

# Vérifier le service cron
sudo systemctl status cron
```

### 3. Tester le scheduler manuellement

```bash
# Voir les tâches planifiées
cd /var/www/tricycle_app
php artisan schedule:list

# Exécuter le scheduler manuellement (pour test)
php artisan schedule:run

# Exécuter la commande de notifications directement
php artisan notifications:quotidiennes
```

## Logs

Les logs du scheduler sont enregistrés dans `storage/logs/laravel.log`.

Pour voir les logs en temps réel :
```bash
tail -f /var/www/tricycle_app/storage/logs/laravel.log
```

## Ajout de nouvelles tâches planifiées

Pour ajouter une nouvelle tâche, modifiez le fichier `routes/console.php` :

```php
use Illuminate\Support\Facades\Schedule;

// Exemples de fréquences disponibles :
Schedule::command('ma:commande')->everyMinute();
Schedule::command('ma:commande')->hourly();
Schedule::command('ma:commande')->daily();
Schedule::command('ma:commande')->dailyAt('13:00');
Schedule::command('ma:commande')->weekly();
Schedule::command('ma:commande')->monthly();
Schedule::command('ma:commande')->weekdays();
Schedule::command('ma:commande')->sundays();
```

## Dépannage

### Le scheduler ne s'exécute pas

1. Vérifiez que le cron est configuré : `crontab -l`
2. Vérifiez que le service cron est actif : `sudo systemctl status cron`
3. Vérifiez les permissions : `ls -la /var/www/tricycle_app`
4. Testez manuellement : `php artisan schedule:run`

### Les notifications ne sont pas envoyées

1. Vérifiez la table `system_notifications` dans la base de données
2. Exécutez manuellement : `php artisan notifications:quotidiennes`
3. Consultez les logs : `tail -f storage/logs/laravel.log`

