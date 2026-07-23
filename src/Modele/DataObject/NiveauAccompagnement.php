<?php

namespace App\Gigamed\Modele\DataObject;

enum NiveauAccompagnement: string
{
    case ESSENTIEL = 'essentiel';
    case RENFORCE = 'renforcé';
    case A_DETERMINER = 'à déterminer';
}