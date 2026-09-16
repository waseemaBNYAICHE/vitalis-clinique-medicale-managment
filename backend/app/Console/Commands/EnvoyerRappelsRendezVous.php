<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use App\Models\User;
use App\Notifications\RendezVousRappelNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * SCRUM-613 - Rappel automatique 24h avant un rendez-vous.
 *
 * Concue pour tourner toutes les heures (voir routes/console.php) : a
 * chaque execution, on cible la fenetre [maintenant+24h, maintenant+25h[.
 * Un rendez-vous qui tombe dans cette fenetre recoit son rappel une seule
 * fois, jamais reconsidere ensuite grace a 'rappel_envoye'.
 */
class EnvoyerRappelsRendezVous extends Command
{
    protected $signature = 'rendezvous:envoyer-rappels';

    protected $description = 'Envoie un rappel aux patients et medecins ayant un rendez-vous dans 24h';

    public function handle(): int
    {
        $debutFenetre = Carbon::now()->addHours(24);
        $finFenetre = Carbon::now()->addHours(25);

        $rendezVousAcuellir = RendezVous::query()
            ->whereNotIn('statut', ['Annulé', 'Terminé'])
            ->where('rappel_envoye', false)
            ->get()
            ->filter(function (RendezVous $rendezVous) use ($debutFenetre, $finFenetre) {
                $date = $rendezVous->date_rendez_vous instanceof Carbon
                    ? $rendezVous->date_rendez_vous->format('Y-m-d')
                    : (string) $rendezVous->date_rendez_vous;

                $dateHeure = Carbon::parse($date.' '.$rendezVous->heure_debut);

                return $dateHeure->between($debutFenetre, $finFenetre);
            });

        $envoyes = 0;

        foreach ($rendezVousAcuellir as $rendezVous) {
            $destinataires = User::where('id_patient', $rendezVous->id_patient)
                ->orWhere('id_medecin', $rendezVous->id_medecin)
                ->get();

            foreach ($destinataires as $destinataire) {
                $destinataire->notify(new RendezVousRappelNotification($rendezVous));
            }

            $rendezVous->rappel_envoye = true;
            $rendezVous->save();

            $envoyes++;
        }

        $this->info("{$envoyes} rappel(s) envoye(s).");

        return self::SUCCESS;
    }
}