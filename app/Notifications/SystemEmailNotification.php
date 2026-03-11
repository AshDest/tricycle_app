<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use App\Models\SystemSetting;

/**
 * Notification générique pour envoyer des emails
 * Utilisée par le NotificationService pour envoyer des notifications par email
 */
class SystemEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $type;
    protected string $titre;
    protected string $notificationMessage;
    protected string $couleur;
    protected ?string $actionUrl;
    protected ?string $actionText;

    /**
     * Mapping des couleurs vers les couleurs d'email
     */
    protected array $colorMapping = [
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'orange',
        'info' => 'blue',
        'primary' => 'blue',
    ];

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $type,
        string $titre,
        string $message,
        string $couleur = 'info',
        ?string $actionUrl = null,
        ?string $actionText = null
    ) {
        $this->type = $type;
        $this->titre = $titre;
        $this->notificationMessage = $message;
        $this->couleur = $couleur;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Appliquer la configuration email depuis SystemSetting
     */
    protected function applyMailConfiguration(): void
    {
        $mailer = SystemSetting::get('mail_mailer', config('mail.default'));
        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', SystemSetting::get('mail_host', config('mail.mailers.smtp.host')));
            Config::set('mail.mailers.smtp.port', SystemSetting::get('mail_port', config('mail.mailers.smtp.port')));
            Config::set('mail.mailers.smtp.username', SystemSetting::get('mail_username', config('mail.mailers.smtp.username')));

            // Décrypter le mot de passe si stocké en base
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

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Appliquer la configuration email depuis la base de données
        $this->applyMailConfiguration();

        $mail = (new MailMessage);

        // Définir le niveau selon la couleur (pour le style du bouton)
        $level = match ($this->couleur) {
            'danger', 'red' => 'error',
            'success', 'green' => 'success',
            default => 'info',
        };

        // Ajouter un emoji selon le type
        $emoji = $this->getEmoji();

        $mail->subject("[Tricycle App] {$emoji} {$this->titre}")
            ->greeting('Bonjour ' . ($notifiable->name ?? 'Utilisateur') . ',')
            ->line($this->notificationMessage);

        // Ajouter des lignes supplémentaires selon le type
        $this->addTypeSpecificContent($mail);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        $mail->line('---')
            ->line('Ceci est une notification automatique de Tricycle App.')
            ->salutation('Cordialement, L\'équipe New Technology Hub Sarl');

        return $mail;
    }

    /**
     * Obtenir un emoji selon le type de notification
     */
    protected function getEmoji(): string
    {
        return match ($this->type) {
            'retard_paiement', 'arrieres_critiques', 'arrieres_motard' => '⚠️',
            'versement_valide', 'versement_moto', 'collecte_validee' => '✅',
            'paiement_recu', 'paiement_approuve' => '💰',
            'paiement_rejete', 'collecte_rejetee' => '❌',
            'accident_moto', 'accident_grave' => '🚨',
            'maintenance_moto', 'maintenance_programmee' => '🔧',
            'tournee_jour', 'tournee_confirmee', 'ramassage_prevu' => '🚚',
            'modification_tournee' => '📝',
            'fin_ramassage' => '✅',
            'contrat_expire', 'contrat_expire_bientot' => '📅',
            'immobilisation_prolongee' => '⏰',
            'depassement_budget' => '💸',
            default => '🔔',
        };
    }

    /**
     * Ajouter du contenu spécifique selon le type
     */
    protected function addTypeSpecificContent(MailMessage $mail): void
    {
        switch ($this->type) {
            case 'arrieres_critiques':
            case 'arrieres_motard':
                $mail->line('**Action requise:** Veuillez régulariser votre situation dès que possible.');
                break;

            case 'accident_grave':
            case 'accident_moto':
                $mail->line('**Important:** Veuillez consulter les détails de l\'accident dans l\'application.');
                break;

            case 'contrat_expire':
                $mail->line('**Action requise:** Veuillez renouveler le contrat pour continuer à utiliser le service.');
                break;

            case 'contrat_expire_bientot':
                $mail->line('**Rappel:** Pensez à renouveler le contrat avant son expiration.');
                break;

            case 'maintenance_programmee':
                $mail->line('**Note:** Assurez-vous que la moto soit disponible à la date prévue.');
                break;

            case 'tournee_jour':
            case 'ramassage_prevu':
                $mail->line('**Rappel:** Préparez votre versement ou votre caisse.');
                break;
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'titre' => $this->titre,
            'message' => $this->notificationMessage,
            'couleur' => $this->couleur,
        ];
    }
}

