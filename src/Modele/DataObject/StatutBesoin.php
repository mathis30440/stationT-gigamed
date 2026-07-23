<?php

namespace App\Gigamed\Modele\DataObject;

enum StatutBesoin: string
{
    case BROUILLON = 'brouillon';
    case ATTENTE = 'en attente';
    case TRANSFORMEC = 'transformé challenge';
    case TRANSFORMEA = 'transformé AMI';
    case REFUSE = 'refusé';
}