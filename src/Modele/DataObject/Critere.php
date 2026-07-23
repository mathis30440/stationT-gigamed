<?php

namespace App\Gigamed\Modele\DataObject;

class Critere extends AbstractDataObject
{
    private ?int $idCritere;
    private string $libelle;
    private float $ponderation;
    private ?string $commentaire;

    public function __construct(string $libelle, float $ponderation, ?int $idCritere = null, ?string $commentaire = null)
    {
        $this->idCritere = $idCritere;
        $this->libelle = $libelle;
        $this->ponderation = $ponderation;
        $this->commentaire = $commentaire;
    }

    public function getIdCritere(): ?int
    {
        return $this->idCritere;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getPonderation(): float
    {
        return $this->ponderation;
    }

    public function setLibelle(string $libelle): void
    {
        $this->libelle = $libelle;
    }

    public function setPonderation(float $ponderation): void
    {
        $this->ponderation = $ponderation;
    }

    public function setIdCritere(?int $idCritere): void
    {
        $this->idCritere = $idCritere;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }
}