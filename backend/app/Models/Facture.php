<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}