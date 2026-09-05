<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chambre extends Model
{
    protected $primaryKey = 'id_chambre';

    protected $fillable = [
        'numero_chambre',
        'type_chambre',
        'etage',
        'capacite',
        'tarif_journalier',
        'statut',
        'description',
    ];

    public function hospitalisations(): HasMany
    {
        return $this->hasMany(Hospitalisation::class, 'id_chambre', 'id_chambre');
    }
}
