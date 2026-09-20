<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Medecin extends Model
{
    protected $primaryKey = 'id_medecin';

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'telephone',
        'email',
        'date_embauche',
        'tarif_consultation',
        'id_specialite',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id_medecin', 'id_medecin');
    }

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class, 'id_specialite', 'id_specialite');
    }
}