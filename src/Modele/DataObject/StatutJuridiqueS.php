<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutJuridiqueS: string
{
    case STARTUP_MOINS_3_ANS = 'startup moins 3 ans';
    case MICRO_ENTREPRISE = 'micro-entreprise';
    case AUTRE = 'autre';
}