<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutChallenge: string
{
    case BROUILLON = 'brouillon';
    case OUVERT = 'ouvert';
    case CLOTURE = 'cloturé';
    case ARCHIVE = 'archivé';
}