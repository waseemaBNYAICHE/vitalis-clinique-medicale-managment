<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    protected $primaryKey = 'id_consultation';

    protected $fillable = [
        'motif',
        'diagnostic',
        'observations',
        'poids',
        'taille',
        'tension_arterielle',
        'temperature',
        'id_rendez_vous',
    ];

    /**
     * SCRUM-524 - Une consultation n'a pas de patient/medecin directement :
     * elle passe par le rendez-vous. Ces raccourcis evitent de remonter la
     * chaine a chaque fois (utile pour les Policies de scope "les siennes"
     * a venir). Ce ne sont pas des relations Eloquent classiques : elles
     * renvoient directement le modele, pas un objet Relation.
     */
    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class, 'id_rendez_vous', 'id_rendez_vous');
    }

    public function patient(): ?Patient
    {
        return $this->rendezVous?->patient;
    }

    public function medecin(): ?Medecin
    {
        return $this->rendezVous?->medecin;
    }

    public function ordonnances(): HasMany
    {
        return $this->hasMany(Ordonnance::class, 'id_consultation', 'id_consultation');
    }

    public function demandesExamen(): HasMany
    {
        return $this->hasMany(DemandeExamen::class, 'id_consultation', 'id_consultation');
    }
}
