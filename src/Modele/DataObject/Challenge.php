<?php

namespace App\Gigamed\Modele\DataObject;

class Challenge extends AbstractDataObject
{
    private ?int $idChallenge;
    private string $titreChallenge;
    private string $objectifChallenge;
    private string $publicVise;
    private string $perimetrePrioritaire;
    private string $exemplesInnovations;
    private string $casUsagePilote;
    private string $perimetreCasUsage;
    private string $dureeIndicative;
    private string $sortieAttendue;
    private string $partenaires;
    private Filiere $filiere;
    private TypeChallenge $typeChallenge;
    private \DateTime $dateDepot;
    private StatutChallenge $statutChallenge;
    private int $idBesoin;

    public function __construct(
        string $titreChallenge,
        string $objectifChallenge,
        string $publicVise,
        string $perimetrePrioritaire,
        string $exemplesInnovations,
        string $casUsagePilote,
        string $perimetreCasUsage,
        string $dureeIndicative,
        string $sortieAttendue,
        string $partenaires,
        Filiere $filiere,
        TypeChallenge $typeChallenge,
        \DateTime $dateDepot,
        StatutChallenge $statutChallenge,
        int $idBesoin,
        ?int $idChallenge = null
    ) {
        $this->idChallenge = $idChallenge;
        $this->titreChallenge = $titreChallenge;
        $this->objectifChallenge = $objectifChallenge;
        $this->publicVise = $publicVise;
        $this->perimetrePrioritaire = $perimetrePrioritaire;
        $this->exemplesInnovations = $exemplesInnovations;
        $this->casUsagePilote = $casUsagePilote;
        $this->perimetreCasUsage = $perimetreCasUsage;
        $this->dureeIndicative = $dureeIndicative;
        $this->sortieAttendue = $sortieAttendue;
        $this->partenaires = $partenaires;
        $this->filiere = $filiere;
        $this->typeChallenge = $typeChallenge;
        $this->dateDepot = $dateDepot;
        $this->statutChallenge = $statutChallenge;
        $this->idBesoin = $idBesoin;
    }

    public function getIdChallenge(): ?int
    {
        return $this->idChallenge;
    }

    public function getTitreChallenge(): string
    {
        return $this->titreChallenge;
    }

    public function getObjectifChallenge(): string
    {
        return $this->objectifChallenge;
    }

    public function getPublicVise(): string
    {
        return $this->publicVise;
    }

    public function getPerimetrePrioritaire(): string
    {
        return $this->perimetrePrioritaire;
    }

    public function getExemplesInnovations(): string
    {
        return $this->exemplesInnovations;
    }

    public function getCasUsagePilote(): string
    {
        return $this->casUsagePilote;
    }

    public function getPerimetreCasUsage(): string
    {
        return $this->perimetreCasUsage;
    }

    public function getDureeIndicative(): string
    {
        return $this->dureeIndicative;
    }

    public function getSortieAttendue(): string
    {
        return $this->sortieAttendue;
    }

    public function getPartenaires(): string
    {
        return $this->partenaires;
    }

    public function getFiliere(): Filiere
    {
        return $this->filiere;
    }

    public function getTypeChallenge(): TypeChallenge
    {
        return $this->typeChallenge;
    }

    public function getDateDepot(): \DateTime
    {
        return $this->dateDepot;
    }

    public function getStatutChallenge(): StatutChallenge
    {
        return $this->statutChallenge;
    }

    public function getIdBesoin(): int
    {
        return $this->idBesoin;
    }

    public function setIdChallenge(?int $idChallenge): void
    {
        $this->idChallenge = $idChallenge;
    }

    public function setTitreChallenge(string $titreChallenge): void
    {
        $this->titreChallenge = $titreChallenge;
    }

    public function setObjectifChallenge(string $objectifChallenge): void
    {
        $this->objectifChallenge = $objectifChallenge;
    }

    public function setPublicVise(string $publicVise): void
    {
        $this->publicVise = $publicVise;
    }

    public function setPerimetrePrioritaire(string $perimetrePrioritaire): void
    {
        $this->perimetrePrioritaire = $perimetrePrioritaire;
    }

    public function setExemplesInnovations(string $exemplesInnovations): void
    {
        $this->exemplesInnovations = $exemplesInnovations;
    }

    public function setCasUsagePilote(string $casUsagePilote): void
    {
        $this->casUsagePilote = $casUsagePilote;
    }

    public function setPerimetreCasUsage(string $perimetreCasUsage): void
    {
        $this->perimetreCasUsage = $perimetreCasUsage;
    }

    public function setDureeIndicative(string $dureeIndicative): void
    {
        $this->dureeIndicative = $dureeIndicative;
    }

    public function setSortieAttendue(string $sortieAttendue): void
    {
        $this->sortieAttendue = $sortieAttendue;
    }

    public function setPartenaires(string $partenaires): void
    {
        $this->partenaires = $partenaires;
    }

    public function setFiliere(Filiere $filiere): void
    {
        $this->filiere = $filiere;
    }

    public function setTypeChallenge(TypeChallenge $typeChallenge): void
    {
        $this->typeChallenge = $typeChallenge;
    }

    public function setDateDepot(\DateTime $dateDepot): void
    {
        $this->dateDepot = $dateDepot;
    }

    public function setStatutChallenge(StatutChallenge $statutChallenge): void
    {
        $this->statutChallenge = $statutChallenge;
    }
}