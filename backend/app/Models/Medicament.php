<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    protected $primaryKey = 'id_medicament';

    protected $fillable = [
        'nom_medicament',
        'forme',
        'dosage',
        'fabricant',
        'description',
    ];
}