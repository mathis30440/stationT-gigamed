<?php

namespace App\Gigamed\Modele\DataObject;

enum BesoinsPrioritaires: string
{
    case CLARIFICATION_PROPOSITION = 'Clarification de la proposition de valeur';
    case VALIDATION_BESOIN_MARCHE   = 'Validation du besoin marché';
    case GO_TO_MARKET               = 'Go-to-market et acquisition client';
    case STRUCTURATION_COMMERCIALE  = 'Structuration commerciale';
    case BUSINESS_MODEL             = 'Business model et stratégie';
    case PREPARATION_FINANCEMENT    = 'Préparation financement / pitch deck';
    case TESTS_UTILISATEURS         = 'Tests utilisateurs / clients';
    case ACCES_TERRAINS             = 'Accès à des terrains d\'expérimentation';
    case MISE_EN_RELATION           = 'Mise en relation partenaires / mentors';
    case HEBERGEMENT                = 'Hébergement / espace de travail';
    case AUTRE                      = 'Autre';
}