<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutJuridiqueE: string
{
    case STARTUP_PLUS_3_ANS = 'startup plus 3 ans';
    case COLLECTIF = 'collectif';
    case ASSOCIATION = 'association';
    case LABORATOIRE = 'laboratoire';
    case CONSORTIUM = 'consortium';
    case AUTRE = 'autre';
}