<?php

namespace App\Gigamed\Modele\DataObject;

enum TypeChallenge: string
{
    case FILIERE = 'filière';
    case INTERFILIERE = 'interfilières';
    case BESOIN_ENTREPRISE = 'besoin entreprise';
    case BESOIN_COMMUNE = 'besoin commune';
}