<?php

namespace App\Gigamed\Modele\DataObject;

class Candidature extends AbstractDataObject
{
    private ?int $idCandidature;
    private string $nomProjet;
    private EntreeStationT $entreeStationT;
    private ?string $filiereCandidat;
    private string $besoinTraite;
    private string $descriptionProjet;
    private ?string $valeurAjoutee;
    private string $innovationDifferentiation;
    private StageMaturite $maturite;
    private ?string $references_;
    private ?string $objectifPilote;
    private ?string $perimetreGeographique;
    private ?string $dureeExperimentation;
    private ?string $publicsCibles;
    private ?string $budgetMobiliser;
    private ?string $moyensMobiliser;
    private ?string $conditionsReussite;
    private ?string $derouteOperationnel;
    private ?string $partenairesRecherches;
    private ?string $appuisStationT;
    private string $modelEconomique;
    private ?string $conditionsDeploiement;
    private ?string $impactAttendu;
    private ?string $conformite;
    private ?string $mesuresSecurisation;
    private bool $engagements;
    private ?string $pitchCourt;
    private ?string $problemeIdentifie;
    private ?string $concurrence;
    private ?string $clientsCibles;
    private ?string $preuvesBesoin;
    private ?string $etatAvancement;
    private ?string $besoinsFinanciers;
    private ?string $equipe;
    private ?string $forcesEquipe;
    private ?ProgrammeGigamed $programmeGigamed;
    private ?NiveauAccompagnement $niveauAccompagnement;
    private ?string $besoinsPrioritaires;
    private ?string $objectifAccompagnement;
    private \DateTime $dateDepot;
    private StatutCandidature $statutCandidature;
    private int $idUtilisateur;
    private ?int $idChallenge;
    private ?int $idAmi;

    public function __construct(
        string $nomProjet,
        EntreeStationT $entreeStationT,
        ?string $filiereCandidat,
        string $besoinTraite,
        string $descriptionProjet,
        ?string $valeurAjoutee,
        string $innovationDifferentiation,
        StageMaturite $maturite,
        ?string $references_,
        ?string $objectifPilote,
        ?string $perimetreGeographique,
        ?string $dureeExperimentation,
        ?string $publicsCibles,
        ?string $budgetMobiliser,
        ?string $moyensMobiliser,
        ?string $conditionsReussite,
        ?string $derouteOperationnel,
        ?string $partenairesRecherches,
        ?string $appuisStationT,
        string $modelEconomique,
        ?string $conditionsDeploiement,
        ?string $impactAttendu,
        ?string $conformite,
        ?string $mesuresSecurisation,
        bool $engagements,
        ?string $pitchCourt,
        ?string $problemeIdentifie,
        ?string $concurrence,
        ?string $clientsCibles,
        ?string $preuvesBesoin,
        ?string $etatAvancement,
        ?string $besoinsFinanciers,
        ?string $equipe,
        ?string $forcesEquipe,
        ?ProgrammeGigamed $programmeGigamed,
        ?NiveauAccompagnement $niveauAccompagnement,
        ?string $besoinsPrioritaires,
        ?string $objectifAccompagnement,
        \DateTime $dateDepot,
        StatutCandidature $statutCandidature,
        int $idUtilisateur,
        ?int $idChallenge,
        ?int $idAmi,
        ?int $idCandidature = null
    ) {
        $this->idCandidature = $idCandidature;
        $this->nomProjet = $nomProjet;
        $this->entreeStationT = $entreeStationT;
        $this->filiereCandidat = $filiereCandidat;
        $this->besoinTraite = $besoinTraite;
        $this->descriptionProjet = $descriptionProjet;
        $this->valeurAjoutee = $valeurAjoutee;
        $this->innovationDifferentiation = $innovationDifferentiation;
        $this->maturite = $maturite;
        $this->references_ = $references_;
        $this->objectifPilote = $objectifPilote;
        $this->perimetreGeographique = $perimetreGeographique;
        $this->dureeExperimentation = $dureeExperimentation;
        $this->publicsCibles = $publicsCibles;
        $this->budgetMobiliser = $budgetMobiliser;
        $this->moyensMobiliser = $moyensMobiliser;
        $this->conditionsReussite = $conditionsReussite;
        $this->derouteOperationnel = $derouteOperationnel;
        $this->partenairesRecherches = $partenairesRecherches;
        $this->appuisStationT = $appuisStationT;
        $this->modelEconomique = $modelEconomique;
        $this->conditionsDeploiement = $conditionsDeploiement;
        $this->impactAttendu = $impactAttendu;
        $this->conformite = $conformite;
        $this->mesuresSecurisation = $mesuresSecurisation;
        $this->engagements = $engagements;
        $this->pitchCourt = $pitchCourt;
        $this->problemeIdentifie = $problemeIdentifie;
        $this->concurrence = $concurrence;
        $this->clientsCibles = $clientsCibles;
        $this->preuvesBesoin = $preuvesBesoin;
        $this->etatAvancement = $etatAvancement;
        $this->besoinsFinanciers = $besoinsFinanciers;
        $this->equipe = $equipe;
        $this->forcesEquipe = $forcesEquipe;
        $this->programmeGigamed = $programmeGigamed;
        $this->niveauAccompagnement = $niveauAccompagnement;
        $this->besoinsPrioritaires = $besoinsPrioritaires;
        $this->objectifAccompagnement = $objectifAccompagnement;
        $this->dateDepot = $dateDepot;
        $this->statutCandidature = $statutCandidature;
        $this->idUtilisateur = $idUtilisateur;
        $this->idChallenge = $idChallenge;
        $this->idAmi = $idAmi;
    }

    public function getIdCandidature(): ?int
    {
        return $this->idCandidature;
    }

    public function getNomProjet(): string
    {
        return $this->nomProjet;
    }

    public function getEntreeStationT(): EntreeStationT
    {
        return $this->entreeStationT;
    }

    public function getFiliereCandidat(): ?string
    {
        return $this->filiereCandidat;
    }

    public function getBesoinTraite(): string
    {
        return $this->besoinTraite;
    }

    public function getDescriptionProjet(): string
    {
        return $this->descriptionProjet;
    }

    public function getValeurAjoutee(): ?string
    {
        return $this->valeurAjoutee;
    }

    public function getInnovationDifferentiation(): string
    {
        return $this->innovationDifferentiation;
    }

    public function getMaturite(): StageMaturite
    {
        return $this->maturite;
    }

    public function getReferences_(): ?string
    {
        return $this->references_;
    }

    public function getReferences(): ?string
    {
        return $this->references_;
    }

    public function getObjectifPilote(): ?string
    {
        return $this->objectifPilote;
    }

    public function getPerimetreGeographique(): ?string
    {
        return $this->perimetreGeographique;
    }

    public function getDureeExperimentation(): ?string
    {
        return $this->dureeExperimentation;
    }

    public function getPublicsCibles(): ?string
    {
        return $this->publicsCibles;
    }

    public function getBudgetMobiliser(): ?string
    {
        return $this->budgetMobiliser;
    }

    public function getMoyensMobiliser(): ?string
    {
        return $this->moyensMobiliser;
    }

    public function getConditionsReussite(): ?string
    {
        return $this->conditionsReussite;
    }

    public function getDerouteOperationnel(): ?string
    {
        return $this->derouteOperationnel;
    }

    public function getPartenairesRecherches(): ?string
    {
        return $this->partenairesRecherches;
    }

    public function getAppuisStationT(): ?string
    {
        return $this->appuisStationT;
    }

    public function getModelEconomique(): string
    {
        return $this->modelEconomique;
    }

    public function getConditionsDeploiement(): ?string
    {
        return $this->conditionsDeploiement;
    }

    public function getImpactAttendu(): ?string
    {
        return $this->impactAttendu;
    }

    public function getConformite(): ?string
    {
        return $this->conformite;
    }

    public function getMesuresSecurisation(): ?string
    {
        return $this->mesuresSecurisation;
    }

    public function isEngagements(): bool
    {
        return $this->engagements;
    }

    public function getPitchCourt(): ?string
    {
        return $this->pitchCourt;
    }

    public function getProblemeIdentifie(): ?string
    {
        return $this->problemeIdentifie;
    }

    public function getConcurrence(): ?string
    {
        return $this->concurrence;
    }

    public function getClientsCibles(): ?string
    {
        return $this->clientsCibles;
    }

    public function getPreuvesBesoin(): ?string
    {
        return $this->preuvesBesoin;
    }

    public function getEtatAvancement(): ?string
    {
        return $this->etatAvancement;
    }

    public function getBesoinsFinanciers(): ?string
    {
        return $this->besoinsFinanciers;
    }

    public function getEquipe(): ?string
    {
        return $this->equipe;
    }

    public function getForcesEquipe(): ?string
    {
        return $this->forcesEquipe;
    }

    public function getProgrammeGigamed(): ?ProgrammeGigamed
    {
        return $this->programmeGigamed;
    }

    public function getNiveauAccompagnement(): ?NiveauAccompagnement
    {
        return $this->niveauAccompagnement;
    }

    public function getBesoinsPrioritaires(): ?string
    {
        return $this->besoinsPrioritaires;
    }

    public function getObjectifAccompagnement(): ?string
    {
        return $this->objectifAccompagnement;
    }

    public function getDateDepot(): \DateTime
    {
        return $this->dateDepot;
    }

    public function getStatutCandidature(): StatutCandidature
    {
        return $this->statutCandidature;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function getIdChallenge(): ?int
    {
        return $this->idChallenge;
    }

    public function getIdAmi(): ?int
    {
        return $this->idAmi;
    }

    public function setIdCandidature(?int $idCandidature): void
    {
        $this->idCandidature = $idCandidature;
    }

    public function setIdAmi(?int $idAmi): void
    {
        $this->idAmi = $idAmi;
    }

    public function setIdChallenge(?int $idChallenge): void
    {
        $this->idChallenge = $idChallenge;
    }

    public function setNomProjet(string $nomProjet): void
    {
        $this->nomProjet = $nomProjet;
    }

    public function setEntreeStationT(EntreeStationT $entreeStationT): void
    {
        $this->entreeStationT = $entreeStationT;
    }

    public function setFiliereCandidat(string $filiereCandidat): void
    {
        $this->filiereCandidat = $filiereCandidat;
    }

    public function setBesoinTraite(string $besoinTraite): void
    {
        $this->besoinTraite = $besoinTraite;
    }

    public function setDescriptionProjet(string $descriptionProjet): void
    {
        $this->descriptionProjet = $descriptionProjet;
    }

    public function setValeurAjoutee(?string $valeurAjoutee): void
    {
        $this->valeurAjoutee = $valeurAjoutee;
    }

    public function setInnovationDifferentiation(string $innovationDifferentiation): void
    {
        $this->innovationDifferentiation = $innovationDifferentiation;
    }

    public function setMaturite(StageMaturite $maturite): void
    {
        $this->maturite = $maturite;
    }

    public function setReferences_(string $references_): void
    {
        $this->references_ = $references_;
    }

    public function setObjectifPilote(?string $objectifPilote): void
    {
        $this->objectifPilote = $objectifPilote;
    }

    public function setPerimetreGeographique(?string $perimetreGeographique): void
    {
        $this->perimetreGeographique = $perimetreGeographique;
    }

    public function setDureeExperimentation(?string $dureeExperimentation): void
    {
        $this->dureeExperimentation = $dureeExperimentation;
    }

    public function setPublicsCibles(string $publicsCibles): void
    {
        $this->publicsCibles = $publicsCibles;
    }

    public function setBudgetMobiliser(?string $budgetMobiliser): void
    {
        $this->budgetMobiliser = $budgetMobiliser;
    }

    public function setMoyensMobiliser(?string $moyensMobiliser): void
    {
        $this->moyensMobiliser = $moyensMobiliser;
    }

    public function setConditionsReussite(?string $conditionsReussite): void
    {
        $this->conditionsReussite = $conditionsReussite;
    }

    public function setDerouteOperationnel(?string $derouteOperationnel): void
    {
        $this->derouteOperationnel = $derouteOperationnel;
    }

    public function setPartenairesRecherches(?string $partenairesRecherches): void
    {
        $this->partenairesRecherches = $partenairesRecherches;
    }

    public function setAppuisStationT(?string $appuisStationT): void
    {
        $this->appuisStationT = $appuisStationT;
    }

    public function setModelEconomique(string $modelEconomique): void
    {
        $this->modelEconomique = $modelEconomique;
    }

    public function setConditionsDeploiement(?string $conditionsDeploiement): void
    {
        $this->conditionsDeploiement = $conditionsDeploiement;
    }

    public function setImpactAttendu(?string $impactAttendu): void
    {
        $this->impactAttendu = $impactAttendu;
    }

    public function setConformite(?string $conformite): void
    {
        $this->conformite = $conformite;
    }

    public function setMesuresSecurisation(?string $mesuresSecurisation): void
    {
        $this->mesuresSecurisation = $mesuresSecurisation;
    }

    public function setEngagements(bool $engagements): void
    {
        $this->engagements = $engagements;
    }

    public function setPitchCourt(?string $pitchCourt): void
    {
        $this->pitchCourt = $pitchCourt;
    }

    public function setProblemeIdentifie(?string $problemeIdentifie): void
    {
        $this->problemeIdentifie = $problemeIdentifie;
    }

    public function setConcurrence(?string $concurrence): void
    {
        $this->concurrence = $concurrence;
    }

    public function setClientsCibles(?string $clientsCibles): void
    {
        $this->clientsCibles = $clientsCibles;
    }

    public function setPreuvesBesoin(?string $preuvesBesoin): void
    {
        $this->preuvesBesoin = $preuvesBesoin;
    }

    public function setEtatAvancement(?string $etatAvancement): void
    {
        $this->etatAvancement = $etatAvancement;
    }

    public function setBesoinsFinanciers(?string $besoinsFinanciers): void
    {
        $this->besoinsFinanciers = $besoinsFinanciers;
    }

    public function setEquipe(?string $equipe): void
    {
        $this->equipe = $equipe;
    }

    public function setForcesEquipe(?string $forcesEquipe): void
    {
        $this->forcesEquipe = $forcesEquipe;
    }

    public function setProgrammeGigamed(?ProgrammeGigamed $programmeGigamed): void
    {
        $this->programmeGigamed = $programmeGigamed;
    }

    public function setNiveauAccompagnement(?NiveauAccompagnement $niveauAccompagnement): void
    {
        $this->niveauAccompagnement = $niveauAccompagnement;
    }

    public function setBesoinsPrioritaires(?string $besoinsPrioritaires): void
    {
        $this->besoinsPrioritaires = $besoinsPrioritaires;
    }

    public function setObjectifAccompagnement(?string $objectifAccompagnement): void
    {
        $this->objectifAccompagnement = $objectifAccompagnement;
    }

    public function setDateDepot(\DateTime $dateDepot): void
    {
        $this->dateDepot = $dateDepot;
    }

    public function setStatutCandidature(StatutCandidature $statutCandidature): void
    {
        $this->statutCandidature = $statutCandidature;
    }

    public function setIdUtilisateur(int $idUtilisateur): void
    {
        $this->idUtilisateur = $idUtilisateur;
    }
}