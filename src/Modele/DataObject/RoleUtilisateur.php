<?php

namespace App\Gigamed\Modele\DataObject;

enum RoleUtilisateur: string
{
    case SUPER_ADMIN = 'superAdmin';
    case ADMIN = 'admin';
    case PARTENAIRE = 'partenaire';
    case STARTUP = 'startup';
    case ENTREPRISE = 'entreprise';
    case PORTEUR_DE_PROJET = 'porteur projet';
}