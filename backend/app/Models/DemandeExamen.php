<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DemandeExamen extends Model
{
    protected $table = 'demandes_examen';

    protected $primaryKey = 'id_demande_examen';

    protected $fillable = [
        'date_demande',
        'niveau_urgence',
        'indications_cliniques',
        'statut',
        'date_prevue',
        'date_realisation',
        'observation',
        'id_consultation',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'id_consultation', 'id_consultation');
    }

    public function resultat(): HasOne
    {
        return $this->hasOne(Resultat::class, 'id_demande_examen', 'id_demande_examen');
    }

    public function patient(): ?Patient
    {
        return $this->consultation?->patient();
    }

    public function medecin(): ?Medecin
    {
        return $this->consultation?->medecin();
    }
}
