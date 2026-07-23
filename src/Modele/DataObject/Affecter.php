<?php

namespace App\Gigamed\Modele\DataObject;

use DateTime;

class Affecter extends AbstractDataObject
{
    private int $idUtilisateur;
    private int $idCandidature;
    private DateTime $dateAffectation;

    

    public function __construct(int $idUtilisateur, int $idCandidature, DateTime $dateAffectation)
    {
        $this->idUtilisateur = $idUtilisateur;
        $this->idCandidature = $idCandidature;
        $this->dateAffectation = $dateAffectation;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function getDateAffectation(): DateTime
    {
        return $this->dateAffectation;
    }

    public function setDateAffectation(DateTime $dateAffectation): void
    {
        $this->dateAffectation = $dateAffectation;
    }

    public function getIdCandidature(): int
    {
        return $this->idCandidature;
    }
}