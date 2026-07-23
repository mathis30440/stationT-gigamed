<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutAmi: string
{
    case BROUILLON = 'brouillon';
    case ATTENTE = 'en attente';
    case OUVERT = 'ouvert';
    case CLOTURE = 'cloturé';
    case ARCHIVE = 'archivé';
}