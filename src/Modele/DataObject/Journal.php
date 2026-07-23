<?php

namespace App\Gigamed\Modele\DataObject;

class Journal extends AbstractDataObject
{
    private ?int $idJournal;
    private string $action;
    private string $objet;
    private \DateTime $horodatage;
    private int $idUtilisateur;

    

    public function __construct(string $action, string $objet, \DateTime $horodatage, int $idUtilisateur, ?int $idJournal = null)
    {
        $this->idJournal = $idJournal;
        $this->action = $action;
        $this->objet = $objet;
        $this->horodatage = $horodatage;
        $this->idUtilisateur = $idUtilisateur;
    }

    public function getIdJournal(): ?int
    {
        return $this->idJournal;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getObjet(): string
    {
        return $this->objet;
    }

    public function getHorodatage(): \DateTime
    {
        return $this->horodatage;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setObjet(string $objet): void
    {
        $this->objet = $objet;
    }

    public function setHorodatage(\DateTime $horodatage): void
    {
        $this->horodatage = $horodatage;
    }

    public function setIdJournal(?int $idJournal): void
    {
        $this->idJournal = $idJournal;
    }
}