<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hospitalisation extends Model
{
    protected $primaryKey = 'id_hospitalisation';

    protected $fillable = [
        'date_entree',
        'heure_entree',
        'date_sortie',
        'motif_hospitalisation',
        'diagnostic_entree',
        'statut',
        'observations',
        'id_patient',
        'id_chambre',
        'id_medecin',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_patient', 'id_patient');
    }

    public function chambre(): BelongsTo
    {
        return $this->belongsTo(Chambre::class, 'id_chambre', 'id_chambre');
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class, 'id_medecin', 'id_medecin');
    }
}