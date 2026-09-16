<?php

namespace App\Notifications;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RendezVousCreeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RendezVous $rendezVous
    ) {
    }

    /**
     * Définir les canaux de notification.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Données enregistrées dans la notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'rendez_vous_cree',
            'message' => 'Un nouveau rendez-vous a été créé.',
            'id_rendez_vous' => $this->rendezVous->id_rendez_vous,
            'date_rendez_vous' => $this->rendezVous->date_rendez_vous,
            'heure_debut' => $this->rendezVous->heure_debut,
            'heure_fin' => $this->rendezVous->heure_fin,
            'motif' => $this->rendezVous->motif,
            'id_patient' => $this->rendezVous->id_patient,
            'id_medecin' => $this->rendezVous->id_medecin,
        ];
    }
}