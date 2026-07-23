<?php

namespace App\Gigamed\Modele\DataObject;

enum ProgrammeGigamed: string
{
    case INCUBATION = 'incubation';
    case AMORCAGE = 'amorçage';
    case ACCELERATION = 'accélération';
    case A_DETERMINER = 'à déterminer';
}