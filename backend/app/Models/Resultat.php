<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultat extends Model
{
    protected $primaryKey = 'id_resultat';

    protected $fillable = [
        'date_resultat',
        'resultat_detaille',
        'conclusion',
        'valeurs_mesurees',
        'fichier_resultat',
        'image_resultat',
        'observations',
        'id_demande_examen',
    ];

    public function demandeExamen(): BelongsTo
    {
        return $this->belongsTo(DemandeExamen::class, 'id_demande_examen', 'id_demande_examen');
    }
}
