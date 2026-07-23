<?php

namespace App\Gigamed\Modele\DataObject;

class AppartenirChallenge extends AbstractDataObject
{
    private int $idChallenge;
    private int $idCritere;

    public function __construct(int $idChallenge, int $idCritere)
    {
        $this->idChallenge = $idChallenge;
        $this->idCritere = $idCritere;
    }

    public function getIdChallenge(): int
    {
        return $this->idChallenge;
    }

    public function getIdCritere(): int
    {
        return $this->idCritere;
    }
}