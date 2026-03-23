<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use App\Models\SystemSetting;

class WelcomeUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $plainPassword;
    protected string $roleName;

    public function __construct(string $plainPassword, string $roleName)
    {
        $this->plainPassword = $plainPassword;
        $this->roleName = $roleName;
    }

    protected function applyMailConfiguration(): void
    {
        $mailer = SystemSetting::get('mail_mailer', config('mail.default'));
        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', SystemSetting::get('mail_host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', SystemSetting::get('mail_port', config('mail.mailers.smtp.port')));
            Config::set('mail.mailers.smtp.username', SystemSetting::get('mail_username', config('mail.mailers.smtp.username')));

            $password = SystemSetting::get('mail_password');
            if ($password) {
                try {
                    $password = decrypt($password);
                } catch (\Exception $e) {
                    $password = config('mail.mailers.smtp.password');
                }
            } else {
                $password = config('mail.mailers.smtp.password');
            }
            Config::set('mail.mailers.smtp.password', $password);

            $encryption = SystemSetting::get('mail_encryption', config('mail.mailers.smtp.encryption'));
            Config::set('mail.mailers.smtp.encryption', $encryption === 'null' ? null : $encryption);
        }

        Config::set('mail.from.address', SystemSetting::get('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', SystemSetting::get('mail_from_name', config('mail.from.name')));
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->applyMailConfiguration();

        $roleLabels = [
            'admin' => 'Administrateur',
            'supervisor' => 'Superviseur OKAMI',
            'owner' => 'Propriétaire',
            'driver' => 'Motard',
            'cashier' => 'Caissier',
            'collector' => 'Collecteur',
            'cleaner' => 'Laveur',
        ];

        $roleLabel = $roleLabels[$this->roleName] ?? ucfirst($this->roleName);
        $appUrl = config('app.url', 'https://tricycle.okamisarl.org');

        return (new MailMessage)
            ->subject('Votre compte Tricycle App a été créé')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre compte a été créé sur **Tricycle App** avec le rôle **{$roleLabel}**.")
            ->line('Voici vos identifiants de connexion :')
            ->line("**Email :** {$notifiable->email}")
            ->line("**Mot de passe :** {$this->plainPassword}")
            ->action('Se connecter', $appUrl . '/login')
            ->line('⚠️ **Pour votre sécurité, veuillez changer votre mot de passe après votre première connexion.**')
            ->salutation('Cordialement, l\'équipe Tricycle App');
    }
}

