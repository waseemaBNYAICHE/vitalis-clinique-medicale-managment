<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ordonnance extends Model
{
    protected $primaryKey = 'id_ordonnance';

    protected $fillable = [
        'date_ordonnance',
        'instructions_generales',
        'duree_traitement',
        'type',
        'id_consultation',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'id_consultation', 'id_consultation');
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
