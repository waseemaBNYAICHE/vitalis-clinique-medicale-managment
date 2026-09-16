<?php

namespace App\Enums;

enum StatutFacture: string
{
    case IMPAYEE = 'impayee';
    case PARTIELLE = 'partielle';
    case PAYEE = 'payee';
}