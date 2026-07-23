<?php

namespace App\Gigamed\Modele\DataObject;

class Besoin extends AbstractDataObject
{
    private ?int $idBesoin;
    private string $titreBesoin;
    private Filiere $filiere;
    private string $objectifBesoin;
    private string $publicVise;
    private string $perimetrePrioritaire;
    private string $vision;
    private string $enjeuxMajeurs;
    private string $besoinsPrioritaires;
    private string $exemplesInnovations;
    private string $partenaires;
    private \DateTime $dateDepot;
    private StatutBesoin $statutBesoin;
    private ?string $raisonRefus;
    private int $idUtilisateur;

    public function __construct(
        string $titreBesoin,
        Filiere $filiere,
        string $objectifBesoin,
        string $publicVise,
        string $perimetrePrioritaire,
        string $vision,
        string $enjeuxMajeurs,
        string $besoinsPrioritaires,
        string $exemplesInnovations,
        string $partenaires,
        \DateTime $dateDepot,
        StatutBesoin $statutBesoin,
        int $idUtilisateur,
        ?string $raisonRefus = null,
        ?int $idBesoin = null
    ) {
        $this->idBesoin = $idBesoin;
        $this->titreBesoin = $titreBesoin;
        $this->filiere = $filiere;
        $this->objectifBesoin = $objectifBesoin;
        $this->publicVise = $publicVise;
        $this->perimetrePrioritaire = $perimetrePrioritaire;
        $this->vision = $vision;
        $this->enjeuxMajeurs = $enjeuxMajeurs;
        $this->besoinsPrioritaires = $besoinsPrioritaires;
        $this->exemplesInnovations = $exemplesInnovations;
        $this->partenaires = $partenaires;
        $this->dateDepot = $dateDepot;
        $this->statutBesoin = $statutBesoin;
        $this->raisonRefus = $raisonRefus;
        $this->idUtilisateur = $idUtilisateur;
    }

    public function getIdBesoin(): ?int
    {
        return $this->idBesoin;
    }

    public function getTitreBesoin(): string
    {
        return $this->titreBesoin;
    }

    public function getFiliere(): Filiere
    {
        return $this->filiere;
    }

    public function getObjectifBesoin(): string
    {
        return $this->objectifBesoin;
    }

    public function getPublicVise(): string
    {
        return $this->publicVise;
    }

    public function getPerimetrePrioritaire(): string
    {
        return $this->perimetrePrioritaire;
    }

    public function getVision(): string
    {
        return $this->vision;
    }

    public function getEnjeuxMajeurs(): string
    {
        return $this->enjeuxMajeurs;
    }

    public function getBesoinsPrioritaires(): string
    {
        return $this->besoinsPrioritaires;
    }

    public function getExemplesInnovations(): string
    {
        return $this->exemplesInnovations;
    }

    public function getPartenaires(): string
    {
        return $this->partenaires;
    }

    public function getDateDepot(): \DateTime
    {
        return $this->dateDepot;
    }

    public function getStatutBesoin(): StatutBesoin
    {
        return $this->statutBesoin;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function setIdBesoin(?int $idBesoin): void
    {
        $this->idBesoin = $idBesoin;
    }

    public function setTitreBesoin(string $titreBesoin): void
    {
        $this->titreBesoin = $titreBesoin;
    }

    public function setFiliere(Filiere $filiere): void
    {
        $this->filiere = $filiere;
    }

    public function setObjectifBesoin(string $objectifBesoin): void
    {
        $this->objectifBesoin = $objectifBesoin;
    }

    public function setPublicVise(string $publicVise): void
    {
        $this->publicVise = $publicVise;
    }

    public function setPerimetrePrioritaire(string $perimetrePrioritaire): void
    {
        $this->perimetrePrioritaire = $perimetrePrioritaire;
    }

    public function setVision(string $vision): void
    {
        $this->vision = $vision;
    }

    public function setEnjeuxMajeurs(string $enjeuxMajeurs): void
    {
        $this->enjeuxMajeurs = $enjeuxMajeurs;
    }

    public function setBesoinsPrioritaires(string $besoinsPrioritaires): void
    {
        $this->besoinsPrioritaires = $besoinsPrioritaires;
    }

    public function setExemplesInnovations(string $exemplesInnovations): void
    {
        $this->exemplesInnovations = $exemplesInnovations;
    }

    public function setPartenaires(string $partenaires): void
    {
        $this->partenaires = $partenaires;
    }

    public function setDateDepot(\DateTime $dateDepot): void
    {
        $this->dateDepot = $dateDepot;
    }

    public function setStatutBesoin(StatutBesoin $statutBesoin): void
    {
        $this->statutBesoin = $statutBesoin;
    }

    public function getRaisonRefus(): ?string
    {
        return $this->raisonRefus;
    }

    public function setRaisonRefus(?string $raisonRefus): void
    {
        $this->raisonRefus = $raisonRefus;
    }

    public function setIdUtilisateur(int $idUtilisateur): void
    {
        $this->idUtilisateur = $idUtilisateur;
    }
}