<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneOrdonnance extends Model
{
    protected $primaryKey = 'id_ligne_ordonnance';

    protected $fillable = [
        'dosologie',
        'frequence',
        'duree',
        'quantite',
        'instructions',
        'id_ordonnance',
        'id_medicament',
    ];

    public function ordonnance(): BelongsTo
    {
        return $this->belongsTo(
            Ordonnance::class,
            'id_ordonnance',
            'id_ordonnance'
        );
    }

    public function medicament(): BelongsTo
    {
        return $this->belongsTo(
            Medicament::class,
            'id_medicament',
            'id_medicament'
        );
    }
}