<?php

namespace App\Gigamed\Modele\DataObject;

class AppartenirAmi extends AbstractDataObject
{
    private int $idAmi;
    private int $idCritere;

    public function __construct(int $idAmi, int $idCritere)
    {
        $this->idAmi = $idAmi;
        $this->idCritere = $idCritere;
    }

    public function getIdAmi(): int
    {
        return $this->idAmi;
    }

    public function getIdCritere(): int
    {
        return $this->idCritere;
    }
}