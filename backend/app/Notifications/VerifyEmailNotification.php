<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    public string $verificationUrl;

    public function __construct(string $verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Vérifiez votre adresse email - QuickHelp')
            ->greeting('Bienvenue sur QuickHelp !')
            ->line('Merci de vous être inscrit. Cliquez sur le bouton ci-dessous pour vérifier votre adresse email.')
            ->action('Vérifier mon email', $this->verificationUrl)
            ->line('Si vous n\'avez pas créé de compte, ignorez cet email.')
            ->salutation('L\'équipe QuickHelp');
    }
}
