<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facture extends Model
{
    protected $primaryKey = 'id_facture';

    protected $fillable = [
        'numero_facture',
        'date_facture',
        'montant_total',
        'remise',
        'montant_net',
        'statut_paiement',
        'observations',
        'id_consultation',
        'id_hospitalisation',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(
            Consultation::class,
            'id_consultation',
            'id_consultation'
        );
    }

    public function hospitalisation(): BelongsTo
    {
        return $this->belongsTo(
            Hospitalisation::class,
            'id_hospitalisation',
            'id_hospitalisation'
        );
    }
}