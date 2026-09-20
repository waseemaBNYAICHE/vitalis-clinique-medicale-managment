<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContactAdministrateurNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $fullName,
        public string $email,
        public string $subject,
        public string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'contact_administrateur',
            'message' => 'Nouveau message envoyé à l’administrateur.',
            'full_name' => $this->fullName,
            'email' => $this->email,
            'subject' => $this->subject,
            'content' => $this->message,
        ];
    }
}