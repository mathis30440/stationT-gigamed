<?php

namespace App\Gigamed\Modele\DataObject;

class Structure extends AbstractDataObject
{
    private ?int $idStructure;
    private string $nomStructure;
    private string $formeJuridique;
    private string $numSIRET_RNA;
    private \DateTime $dateCreation;
    private string $adresseSiege;
    private string $commune;
    private ?string $siteWeb;
    private ?int $effectif;
    private ?float $chiffreAffaires;
    private ?bool $implantationTerritoire;
    private ?bool $implantationEnvisagee;
    private ?int $nombreAssocies;
    private ?int $nombreSalaries;
    private ?StatutJuridiqueS $statutJuridiqueS;
    private ?StatutJuridiqueE $statutJuridiqueE;
    private TypeStructure $typeStructure;
    private string $referentNom;
    private string $referentFonction;
    private string $referentEmail;
    private string $referentTelephone;

    public function __construct(
        string $nomStructure,
        string $formeJuridique,
        string $numSIRET_RNA,
        \DateTime $dateCreation,
        string $adresseSiege,
        string $commune,
        ?string $siteWeb,
        ?int $effectif,
        ?float $chiffreAffaires,
        ?bool $implantationTerritoire,
        ?bool $implantationEnvisagee,
        ?int $nombreAssocies,
        ?int $nombreSalaries,
        ?StatutJuridiqueS $statutJuridiqueS,
        ?StatutJuridiqueE $statutJuridiqueE,
        TypeStructure $typeStructure,
        string $referentNom,
        string $referentFonction,
        string $referentEmail,
        string $referentTelephone,
        ?int $idStructure = null
    ) {
        $this->idStructure = $idStructure;
        $this->nomStructure = $nomStructure;
        $this->formeJuridique = $formeJuridique;
        $this->numSIRET_RNA = $numSIRET_RNA;
        $this->dateCreation = $dateCreation;
        $this->adresseSiege = $adresseSiege;
        $this->commune = $commune;
        $this->siteWeb = $siteWeb;
        $this->effectif = $effectif;
        $this->chiffreAffaires = $chiffreAffaires;
        $this->implantationTerritoire = $implantationTerritoire;
        $this->implantationEnvisagee = $implantationEnvisagee;
        $this->nombreAssocies = $nombreAssocies;
        $this->nombreSalaries = $nombreSalaries;
        $this->statutJuridiqueS = $statutJuridiqueS;
        $this->statutJuridiqueE = $statutJuridiqueE;
        $this->typeStructure = $typeStructure;
        $this->referentNom = $referentNom;
        $this->referentFonction = $referentFonction;
        $this->referentEmail = $referentEmail;
        $this->referentTelephone = $referentTelephone;
    }

    public function getIdStructure(): ?int
    {
        return $this->idStructure;
    }

    public function getNomStructure(): string
    {
        return $this->nomStructure;
    }

    public function getFormeJuridique(): string
    {
        return $this->formeJuridique;
    }

    public function getNumSIRET_RNA(): string
    {
        return $this->numSIRET_RNA;
    }

    public function getDateCreation(): \DateTime
    {
        return $this->dateCreation;
    }

    public function getAdresseSiege(): string
    {
        return $this->adresseSiege;
    }

    public function getCommune(): string
    {
        return $this->commune;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function getEffectif(): ?int
    {
        return $this->effectif;
    }

    public function getChiffreAffaires(): ?float
    {
        return $this->chiffreAffaires;
    }

    public function isImplantationTerritoire(): ?bool
    {
        return $this->implantationTerritoire;
    }

    public function isImplantationEnvisagee(): ?bool
    {
        return $this->implantationEnvisagee;
    }

    public function getNombreAssocies(): ?int
    {
        return $this->nombreAssocies;
    }

    public function getNombreSalaries(): ?int
    {
        return $this->nombreSalaries;
    }

    public function getStatutJuridiqueS(): ?StatutJuridiqueS
    {
        return $this->statutJuridiqueS;
    }

    public function getStatutJuridiqueE(): ?StatutJuridiqueE
    {
        return $this->statutJuridiqueE;
    }

    public function getTypeStructure(): TypeStructure
    {
        return $this->typeStructure;
    }

    public function getReferentNom(): string
    {
        return $this->referentNom;
    }

    public function getReferentFonction(): string
    {
        return $this->referentFonction;
    }

    public function getReferentEmail(): string
    {
        return $this->referentEmail;
    }

    public function getReferentTelephone(): string
    {
        return $this->referentTelephone;
    }

    public function setIdStructure(?int $idStructure): void
    {
        $this->idStructure = $idStructure;
    }

    public function setNomStructure(string $nomStructure): void
    {
        $this->nomStructure = $nomStructure;
    }

    public function setFormeJuridique(string $formeJuridique): void
    {
        $this->formeJuridique = $formeJuridique;
    }

    public function setNumSIRET_RNA(string $numSIRET_RNA): void
    {
        $this->numSIRET_RNA = $numSIRET_RNA;
    }

    public function setDateCreation(\DateTime $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    public function setAdresseSiege(string $adresseSiege): void
    {
        $this->adresseSiege = $adresseSiege;
    }

    public function setCommune(string $commune): void
    {
        $this->commune = $commune;
    }

    public function setSiteWeb(?string $siteWeb): void
    {
        $this->siteWeb = $siteWeb;
    }

    public function setEffectif(?int $effectif): void
    {
        $this->effectif = $effectif;
    }

    public function setChiffreAffaires(?float $chiffreAffaires): void
    {
        $this->chiffreAffaires = $chiffreAffaires;
    }

    public function setImplantationTerritoire(?bool $implantationTerritoire): void
    {
        $this->implantationTerritoire = $implantationTerritoire;
    }

    public function setImplantationEnvisagee(?bool $implantationEnvisagee): void
    {
        $this->implantationEnvisagee = $implantationEnvisagee;
    }

    public function setNombreAssocies(?int $nombreAssocies): void
    {
        $this->nombreAssocies = $nombreAssocies;
    }

    public function setNombreSalaries(?int $nombreSalaries): void
    {
        $this->nombreSalaries = $nombreSalaries;
    }

    public function setStatutJuridiqueS(?StatutJuridiqueS $statutJuridiqueS): void
    {
        $this->statutJuridiqueS = $statutJuridiqueS;
    }

    public function setStatutJuridiqueE(?StatutJuridiqueE $statutJuridiqueE): void
    {
        $this->statutJuridiqueE = $statutJuridiqueE;
    }

    public function setTypeStructure(TypeStructure $typeStructure): void
    {
        $this->typeStructure = $typeStructure;
    }

    public function setReferentNom(string $referentNom): void
    {
        $this->referentNom = $referentNom;
    }

    public function setReferentFonction(string $referentFonction): void
    {
        $this->referentFonction = $referentFonction;
    }

    public function setReferentEmail(string $referentEmail): void
    {
        $this->referentEmail = $referentEmail;
    }

    public function setReferentTelephone(string $referentTelephone): void
    {
        $this->referentTelephone = $referentTelephone;
    }
}