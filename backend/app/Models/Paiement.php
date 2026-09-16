<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $primaryKey = 'id_paiement';

    protected $fillable = [
        'date_paiement',
        'heure_paiement',
        'montant_paye',
        'mode_paiement',
        'statut',
        'observations',
        'id_facture',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(
            Facture::class,
            'id_facture',
            'id_facture'
        );
    }
}