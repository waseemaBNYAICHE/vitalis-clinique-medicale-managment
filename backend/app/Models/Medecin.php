<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
   {
    return $this->hasOne(User::class, 'id_medecin', 'id_medecin');
   }
}
