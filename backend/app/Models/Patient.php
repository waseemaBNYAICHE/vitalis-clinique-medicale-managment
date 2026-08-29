<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $primaryKey = 'id_patient';

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'sexe',
        'cin',
        'telephone',
        'email',
        'groupe_sanguin',
    ];
}
