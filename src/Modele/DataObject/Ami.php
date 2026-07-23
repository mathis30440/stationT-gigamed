<?php

namespace App\Gigamed\Modele\DataObject;

class Ami extends AbstractDataObject
{
    private ?int $idAmi;
    private string $titreAmi;
    private string $objectifAmi;
    private string $publicVise;
    private string $perimetrePrioritaire;
    private Filiere $filiere;
    private string $exemplesInnovations;
    private string $casUsagePilote;
    private string $perimetreCasUsage;
    private string $dureeIndicative;
    private string $sortieAttendue;
    private string $partenaires;
    private \DateTime $dateOuverture;
    private \DateTime $dateCloture;
    private \DateTime $dateAuditions;
    private \DateTime $dateResultats;
    private \DateTime $dateDemarrage;
    private StatutAmi $statutAmi;
    private ?int $idUtilisateur;
    private ?int $idBesoin;

    public function __construct(
        string $titreAmi,
        string $objectifAmi,
        string $publicVise,
        string $perimetrePrioritaire,
        Filiere $filiere,
        string $exemplesInnovations,
        string $casUsagePilote,
        string $perimetreCasUsage,
        string $dureeIndicative,
        string $sortieAttendue,
        string $partenaires,
        \DateTime $dateOuverture,
        \DateTime $dateCloture,
        \DateTime $dateAuditions,
        \DateTime $dateResultats,
        \DateTime $dateDemarrage,
        StatutAmi $statutAmi,
        ?int $idUtilisateur,
        ?int $idBesoin,
        ?int $idAmi = null
    ) {
        $this->idAmi = $idAmi;
        $this->titreAmi = $titreAmi;
        $this->objectifAmi = $objectifAmi;
        $this->publicVise = $publicVise;
        $this->perimetrePrioritaire = $perimetrePrioritaire;
        $this->filiere = $filiere;
        $this->exemplesInnovations = $exemplesInnovations;
        $this->casUsagePilote = $casUsagePilote;
        $this->perimetreCasUsage = $perimetreCasUsage;
        $this->dureeIndicative = $dureeIndicative;
        $this->sortieAttendue = $sortieAttendue;
        $this->partenaires = $partenaires;
        $this->dateOuverture = $dateOuverture;
        $this->dateCloture = $dateCloture;
        $this->dateAuditions = $dateAuditions;
        $this->dateResultats = $dateResultats;
        $this->dateDemarrage = $dateDemarrage;
        $this->statutAmi = $statutAmi;
        $this->idUtilisateur = $idUtilisateur;
        $this->idBesoin = $idBesoin;
    }

    public function setIdAmi(?int $idAmi): void
    {
        $this->idAmi = $idAmi;
    }

    public function getIdAmi(): ?int
    {
        return $this->idAmi;
    }

    public function getTitreAmi(): string
    {
        return $this->titreAmi;
    }

    public function getObjectifAmi(): string
    {
        return $this->objectifAmi;
    }

    public function getPublicVise(): string
    {
        return $this->publicVise;
    }

    public function getPerimetrePrioritaire(): string
    {
        return $this->perimetrePrioritaire;
    }

    public function getFiliere(): Filiere
    {
        return $this->filiere;
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

    public function getDateOuverture(): \DateTime
    {
        return $this->dateOuverture;
    }

    public function getDateCloture(): \DateTime
    {
        return $this->dateCloture;
    }

    public function getDateAuditions(): \DateTime
    {
        return $this->dateAuditions;
    }

    public function getDateResultats(): \DateTime
    {
        return $this->dateResultats;
    }

    public function getDateDemarrage(): \DateTime
    {
        return $this->dateDemarrage;
    }

    public function getStatutAmi(): StatutAmi
    {
        return $this->statutAmi;
    }

    public function getIdUtilisateur(): ?int
    {
        return $this->idUtilisateur;
    }

    public function getIdBesoin(): ?int
    {
        return $this->idBesoin;
    }

    public function setTitreAmi(string $titreAmi): void
    {
        $this->titreAmi = $titreAmi;
    }

    public function setObjectifAmi(string $objectifAmi): void
    {
        $this->objectifAmi = $objectifAmi;
    }

    public function setPublicVise(string $publicVise): void
    {
        $this->publicVise = $publicVise;
    }

    public function setPerimetrePrioritaire(string $perimetrePrioritaire): void
    {
        $this->perimetrePrioritaire = $perimetrePrioritaire;
    }

    public function setFiliere(Filiere $filiere): void
    {
        $this->filiere = $filiere;
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

    public function setDateOuverture(\DateTime $dateOuverture): void
    {
        $this->dateOuverture = $dateOuverture;
    }

    public function setDateCloture(\DateTime $dateCloture): void
    {
        $this->dateCloture = $dateCloture;
    }

    public function setDateAuditions(\DateTime $dateAuditions): void
    {
        $this->dateAuditions = $dateAuditions;
    }

    public function setDateResultats(\DateTime $dateResultats): void
    {
        $this->dateResultats = $dateResultats;
    }

    public function setDateDemarrage(\DateTime $dateDemarrage): void
    {
        $this->dateDemarrage = $dateDemarrage;
    }

    public function setStatutAmi(StatutAmi $statutAmi): void
    {
        $this->statutAmi = $statutAmi;
    }
}