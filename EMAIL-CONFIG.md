# Configuration des Emails - Tricycle App

## Vue d'ensemble

Le système de notifications envoie automatiquement des emails aux utilisateurs en plus des notifications dans l'application.

## Configuration SMTP

### 1. Gmail (Recommandé pour les tests)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@newtechnologyhub.org"
MAIL_FROM_NAME="Tricycle App - NTH"
```

**Note**: Pour Gmail, vous devez créer un "mot de passe d'application" :
1. Allez sur https://myaccount.google.com/apppasswords
2. Connectez-vous avec votre compte Google
3. Créez un nouveau mot de passe d'application
4. Utilisez ce mot de passe dans `MAIL_PASSWORD`

### 2. Mailgun (Recommandé pour la production)

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.newtechnologyhub.org
MAILGUN_SECRET=your-mailgun-api-key
MAIL_FROM_ADDRESS="noreply@newtechnologyhub.org"
MAIL_FROM_NAME="Tricycle App - NTH"
```

### 3. Amazon SES

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=eu-west-1
MAIL_FROM_ADDRESS="noreply@newtechnologyhub.org"
MAIL_FROM_NAME="Tricycle App - NTH"
```

### 4. Mode Log (pour les tests locaux)

```env
MAIL_MAILER=log
```

Les emails seront enregistrés dans `storage/logs/laravel.log` au lieu d'être envoyés.

## Types de notifications par email

| Type | Destinataire | Description |
|------|--------------|-------------|
| Retard de versement | Motard | Alerte quand un versement est en retard |
| Versement validé | Motard | Confirmation de versement |
| Arriérés critiques | Motard + OKAMI | Alerte quand les arriérés dépassent 30 000 FC |
| Versement reçu | Propriétaire | Notification d'un versement sur sa moto |
| Paiement reçu | Propriétaire | Confirmation de paiement |
| Accident déclaré | Propriétaire + OKAMI + Admin | Alerte accident (urgent si grave) |
| Maintenance | Propriétaire + Motard | Notification de maintenance |
| Tournée du jour | Collecteur | Rappel de tournée |
| Collecteur en route | Caissier | Alerte de collecte imminente |
| Contrat expiré | Propriétaire + Admin | Alerte d'expiration de contrat |

## Queue (File d'attente)

Les emails sont envoyés via la file d'attente pour ne pas bloquer l'application.

### Configuration de la file d'attente

```env
QUEUE_CONNECTION=database
```

### Démarrer le worker de file d'attente

```bash
# En développement
php artisan queue:work

# En production (avec supervisor)
php artisan queue:work --daemon --tries=3
```

### Configuration Supervisor (Production)

Créez le fichier `/etc/supervisor/conf.d/tricycle-worker.conf` :

```ini
[program:tricycle-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tricycle_app/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/tricycle_app/storage/logs/worker.log
stopwaitsecs=3600
```

Puis exécutez :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tricycle-worker:*
```

## Tester l'envoi d'emails

```bash
# Tester la configuration email
php artisan tinker

# Dans tinker
Mail::raw('Test email', function ($message) {
    $message->to('votre-email@test.com')->subject('Test');
});
```

## Dépannage

### Les emails ne sont pas envoyés

1. Vérifiez la configuration dans `.env`
2. Vérifiez les logs : `tail -f storage/logs/laravel.log`
3. Vérifiez que le worker de file d'attente est actif
4. Testez avec `MAIL_MAILER=log` pour voir les emails dans les logs

### Les emails sont en file d'attente mais ne partent pas

1. Démarrez le worker : `php artisan queue:work`
2. Vérifiez la table `jobs` dans la base de données
3. Relancez les jobs échoués : `php artisan queue:retry all`

### Erreur d'authentification SMTP

1. Vérifiez le mot de passe
2. Pour Gmail, utilisez un "mot de passe d'application"
3. Vérifiez que le port est correct (587 pour TLS, 465 pour SSL)

