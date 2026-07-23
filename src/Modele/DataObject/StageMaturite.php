<?php

namespace App\Gigamed\Modele\DataObject;

enum StageMaturite: string
{
    case IDEE                  = 'idée';
    case MAQUETTE              = 'maquette';
    case MVP                   = 'MVP';
    case PREMIERES_VENTES      = 'premières ventes';
    case RECHERCHE_FINANCEMENT = 'recherche de financement';
    case SOLUTION_VENDUE       = 'solution vendue';
    case DEMONSTRATEUR         = 'démonstrateur';
    case AUTRE                 = 'autre';
}