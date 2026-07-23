<?php

namespace App\Gigamed\Modele\DataObject;

enum Filiere: string {
    case TOURISME = 'tourisme';
    case NAUTISME = 'nautisme';
    case COMMERCE = 'commerce';
    case AGRICULTURE = 'agriculture';
    case SPORT = 'sport';
    case VILLE = 'ville';
}