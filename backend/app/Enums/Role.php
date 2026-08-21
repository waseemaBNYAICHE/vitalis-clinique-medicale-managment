<?php

namespace App\Enums;

enum Role: string
{
    case ADMINISTRATEUR = 'administrateur';
    case MEDECIN = 'medecin';
    case SECRETAIRE = 'secretaire';
    case INFIRMIER = 'infirmier';
    case PATIENT = 'patient';
}