<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VersementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $type;
    protected string $titre;
    protected string $notificationMessage;
    protected ?string $actionUrl;
    protected ?string $actionText;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, string $titre, string $message, ?string $actionUrl = null, ?string $actionText = null)
    {
        $this->type = $type;
        $this->titre = $titre;
        $this->notificationMessage = $message;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
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
        $mail = (new MailMessage)
            ->subject('[Tricycle App] ' . $this->titre)
            ->greeting('Bonjour ' . ($notifiable->name ?? 'Utilisateur') . ',')
            ->line($this->notificationMessage);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        $mail->line('Merci d\'utiliser Tricycle App.')
            ->salutation('Cordialement, L\'équipe New Technology Hub Sarl');

        return $mail;
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
        ];
    }
}
