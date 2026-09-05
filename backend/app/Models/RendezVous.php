<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $primaryKey = 'id_rendez_vous';

    protected $fillable = [
        'date_rendez_vous',
        'heure_debut',
        'heure_fin',
        'motif',
        'statut',
        'id_patient',
        'id_medecin',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_patient', 'id_patient');
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class, 'id_medecin', 'id_medecin');
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class, 'id_rendez_vous', 'id_rendez_vous');
    }
}
