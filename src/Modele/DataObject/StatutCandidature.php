<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutCandidature: string
{
    case BROUILLON = 'brouillon';
    case DEPOSE = 'déposé';
    case ELIGIBLE = 'éligible';
    case INCOMPLET = 'incomplet';
    case INSTRUCTION = 'en instruction';
    case SELECTIONNE = 'sélectionné';
    case REFUSE = 'refusé';

}