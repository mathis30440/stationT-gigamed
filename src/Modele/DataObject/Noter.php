<?php

namespace App\Gigamed\Modele\DataObject;

class Noter extends AbstractDataObject
{
    private int $idUtilisateur;
    private int $idCandidature;
    private int $idCritere;
    private int $valeur;
    private string $commentaire;
    private \DateTime $dateNote;

    

    public function __construct(int $idUtilisateur, int $idCandidature, int $idCritere, int $valeur, string $commentaire, \DateTime $dateNote)
    {
        $this->idUtilisateur = $idUtilisateur;
        $this->idCandidature = $idCandidature;
        $this->idCritere = $idCritere;
        $this->valeur = $valeur;
        $this->commentaire = $commentaire;
        $this->dateNote = $dateNote;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function getIdCandidature(): int
    {
        return $this->idCandidature;
    }

    public function getIdCritere(): int
    {
        return $this->idCritere;
    }

    public function getValeur(): int
    {
        return $this->valeur;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function getDateNote(): \DateTime
    {
        return $this->dateNote;
    }

    public function setValeur(int $valeur): void
    {
        $this->valeur = $valeur;
    }

    public function setCommentaire(string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function setDateNote(\DateTime $dateNote): void
    {
        $this->dateNote = $dateNote;
    }
}