<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Candidature;
use App\Gigamed\Modele\DataObject\EntreeStationT;
use App\Gigamed\Modele\DataObject\NiveauAccompagnement;
use App\Gigamed\Modele\DataObject\ProgrammeGigamed;
use App\Gigamed\Modele\DataObject\StageMaturite;
use App\Gigamed\Modele\DataObject\StatutCandidature;

class CandidatureRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string { return "Candidature"; }
    protected function getNomClePrimaire(): string { return "idCandidature"; }

    protected function getNomColonnes(): array
    {
        return [
            "idCandidature", "nomProjet", "entreeStationT", "filiereCandidat",
            "besoinTraite", "descriptionProjet", "valeurAjoutee", "innovationDifferentiation",
            "maturite", "references_", "objectifPilote", "perimetreGeographique",
            "dureeExperimentation", "publicsCibles", "budgetMobiliser", "moyensMobiliser",
            "conditionsReussite", "derouteOperationnel", "partenairesRecherches", "appuisStationT",
            "modelEconomique", "conditionsDeploiement", "impactAttendu", "conformite",
            "mesuresSecurisation", "engagements", "pitchCourt", "problemeIdentifie",
            "concurrence", "clientsCibles", "preuvesBesoin", "etatAvancement",
            "besoinsFinanciers", "equipe", "forcesEquipe", "programmeGigamed",
            "niveauAccompagnement", "besoinsPrioritaires", "objectifAccompagnement",
            "dateDepot", "statutCandidature", "idUtilisateur", "idChallenge", "idAmi",
        ];
    }

    protected function getNomColonnesExecute(): array
    {
        return [
            ":idCandidatureTag", ":nomProjetTag", ":entreeStationTTag", ":filiereCandidatTag",
            ":besoinTraiteTag", ":descriptionProjetTag", ":valeurAjouteeTag", ":innovationDifferentiationTag",
            ":maturiteTag", ":references_Tag", ":objectifPiloteTag", ":perimetreGeographiqueTag",
            ":dureeExperimentationTag", ":publicsCiblesTag", ":budgetMobiliserTag", ":moyensMobiliserTag",
            ":conditionsReussiteTag", ":derouteOperationnelTag", ":partenairesRecherchesTag", ":appuisStationTTag",
            ":modelEconomiqueTag", ":conditionsDeploiementTag", ":impactAttenduTag", ":conformiteTag",
            ":mesuresSecurisationTag", ":engagementsTag", ":pitchCourtTag", ":problemeIdentifieTag",
            ":concurrenceTag", ":clientsCiblesTag", ":preuvesBesoinTag", ":etatAvancementTag",
            ":besoinsFinanciersTag", ":equipeTag", ":forcesEquipeTag", ":programmeGigamedTag",
            ":niveauAccompagnementTag", ":besoinsPrioritairesTag", ":objectifAccompagnementTag",
            ":dateDepotTag", ":statutCandidatureTag", ":idUtilisateurTag", ":idChallengeTag", ":idAmiTag",
        ];
    }

    protected function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idCandidatureTag"             => $objet->getIdCandidature(),
            "nomProjetTag"                 => $objet->getNomProjet(),
            "entreeStationTTag"            => $objet->getEntreeStationT()->value,
            "filiereCandidatTag"           => $objet->getFiliereCandidat(),
            "besoinTraiteTag"              => $objet->getBesoinTraite(),
            "descriptionProjetTag"         => $objet->getDescriptionProjet(),
            "valeurAjouteeTag"             => $objet->getValeurAjoutee(),
            "innovationDifferentiationTag" => $objet->getInnovationDifferentiation(),
            "maturiteTag"                  => $objet->getMaturite()->value,
            "references_Tag"               => $objet->getReferences_(),
            "objectifPiloteTag"            => $objet->getObjectifPilote(),
            "perimetreGeographiqueTag"     => $objet->getPerimetreGeographique(),
            "dureeExperimentationTag"      => $objet->getDureeExperimentation(),
            "publicsCiblesTag"             => $objet->getPublicsCibles(),
            "budgetMobiliserTag"           => $objet->getBudgetMobiliser(),
            "moyensMobiliserTag"           => $objet->getMoyensMobiliser(),
            "conditionsReussiteTag"        => $objet->getConditionsReussite(),
            "derouteOperationnelTag"       => $objet->getDerouteOperationnel(),
            "partenairesRecherchesTag"     => $objet->getPartenairesRecherches(),
            "appuisStationTTag"            => $objet->getAppuisStationT(),
            "modelEconomiqueTag"           => $objet->getModelEconomique(),
            "conditionsDeploiementTag"     => $objet->getConditionsDeploiement(),
            "impactAttenduTag"             => $objet->getImpactAttendu(),
            "conformiteTag"                => $objet->getConformite(),
            "mesuresSecurisationTag"       => $objet->getMesuresSecurisation(),
            "engagementsTag"               => $objet->isEngagements() ? 1 : 0,
            "pitchCourtTag"                => $objet->getPitchCourt(),
            "problemeIdentifieTag"         => $objet->getProblemeIdentifie(),
            "concurrenceTag"               => $objet->getConcurrence(),
            "clientsCiblesTag"             => $objet->getClientsCibles(),
            "preuvesBesoinTag"             => $objet->getPreuvesBesoin(),
            "etatAvancementTag"            => $objet->getEtatAvancement(),
            "besoinsFinanciersTag"         => $objet->getBesoinsFinanciers(),
            "equipeTag"                    => $objet->getEquipe(),
            "forcesEquipeTag"              => $objet->getForcesEquipe(),
            "programmeGigamedTag"          => $objet->getProgrammeGigamed()?->value,
            "niveauAccompagnementTag"      => $objet->getNiveauAccompagnement()?->value,
            "besoinsPrioritairesTag"       => $objet->getBesoinsPrioritaires(),
            "objectifAccompagnementTag"    => $objet->getObjectifAccompagnement(),
            "dateDepotTag"                 => $objet->getDateDepot()->format('Y-m-d'),
            "statutCandidatureTag"         => $objet->getStatutCandidature()->value,
            "idUtilisateurTag"             => $objet->getIdUtilisateur(),
            "idChallengeTag"               => $objet->getIdChallenge(),
            "idAmiTag"                     => $objet->getIdAmi(),
        ];
    }

    protected function construireDepuisTableauSQL(array $t): Candidature
    {
        return new Candidature(
            $t['nomProjet'],
            EntreeStationT::from($t['entreeStationT']),
            $t['filiereCandidat'],
            $t['besoinTraite'],
            $t['descriptionProjet'],
            $t['valeurAjoutee'] ?? null,
            $t['innovationDifferentiation'],
            StageMaturite::from($t['maturite']),
            $t['references_'] ?? null,
            $t['objectifPilote'] ?? null,
            $t['perimetreGeographique'] ?? null,
            $t['dureeExperimentation'] ?? null,
            $t['publicsCibles'],
            $t['budgetMobiliser'] ?? null,
            $t['moyensMobiliser'] ?? null,
            $t['conditionsReussite'] ?? null,
            $t['derouteOperationnel'] ?? null,
            $t['partenairesRecherches'] ?? null,
            $t['appuisStationT'] ?? null,
            $t['modelEconomique'],
            $t['conditionsDeploiement'] ?? null,
            $t['impactAttendu'] ?? null,
            $t['conformite'] ?? null,
            $t['mesuresSecurisation'] ?? null,
            (bool) $t['engagements'],
            $t['pitchCourt'] ?? null,
            $t['problemeIdentifie'] ?? null,
            $t['concurrence'] ?? null,
            $t['clientsCibles'] ?? null,
            $t['preuvesBesoin'] ?? null,
            $t['etatAvancement'] ?? null,
            $t['besoinsFinanciers'] ?? null,
            $t['equipe'] ?? null,
            $t['forcesEquipe'] ?? null,
            !empty($t['programmeGigamed']) ? ProgrammeGigamed::from($t['programmeGigamed']) : null,
            !empty($t['niveauAccompagnement']) ? NiveauAccompagnement::from($t['niveauAccompagnement']) : null,
            $t['besoinsPrioritaires'] ?? null,
            $t['objectifAccompagnement'] ?? null,
            new \DateTime($t['dateDepot']),
            StatutCandidature::from($t['statutCandidature']),
            (int) $t['idUtilisateur'],
            !empty($t['idChallenge']) ? (int) $t['idChallenge'] : null,
            !empty($t['idAmi']) ? (int) $t['idAmi'] : null,
            isset($t['idCandidature']) ? (int) $t['idCandidature'] : null,
        );
    }

    protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void
    {
        
        $object->setIdCandidature((int) $pdo->lastInsertId());
    }

    public function recupererParIdAmi(int $idAmi): array
    {
        $sql = "SELECT * FROM Candidature WHERE idAmi = :idAmiTag AND statutCandidature != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idAmiTag" => $idAmi]);
        $candidatures = [];
        foreach ($pdoStatement as $t) {
            $candidatures[] = $this->construireDepuisTableauSQL($t);
        }
        return $candidatures;
    }

    public function recupererParIdChallenge(int $idChallenge): array
    {
        $sql = "SELECT * FROM Candidature WHERE idChallenge = :idChallengeTag AND statutCandidature != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idChallengeTag" => $idChallenge]);
        $candidatures = [];
        foreach ($pdoStatement as $t) {
            $candidatures[] = $this->construireDepuisTableauSQL($t);
        }
        return $candidatures;
    }

    public function recupererParIdUtilisateur(int $idUtilisateur): array
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idUtilisateurTag ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        $candidatures = [];
        foreach ($pdoStatement as $t) {
            $candidatures[] = $this->construireDepuisTableauSQL($t);
        }
        return $candidatures;
    }

    public function existeParIdUtilisateur(int $idUtilisateur): bool
    {
        $sql = "SELECT COUNT(*) FROM Candidature WHERE idUtilisateur = :idUtilisateurTag AND statutCandidature != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        return (int) $pdoStatement->fetchColumn() > 0;
    }

    public function existeParIdUtilisateurEtIdAmi(int $idUtilisateur, int $idAmi): bool
    {
        $sql = "SELECT COUNT(*) FROM Candidature WHERE idUtilisateur = :idUtilisateurTag AND idAmi = :idAmiTag AND statutCandidature != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur, "idAmiTag" => $idAmi]);
        return (int) $pdoStatement->fetchColumn() > 0;
    }

    public function existeParIdUtilisateurEtIdChallenge(int $idUtilisateur, int $idChallenge): bool
    {
        $sql = "SELECT COUNT(*) FROM Candidature WHERE idUtilisateur = :idUtilisateurTag AND idChallenge = :idChallengeTag AND statutCandidature != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur, "idChallengeTag" => $idChallenge]);
        return (int) $pdoStatement->fetchColumn() > 0;
    }

    public function recupererBrouillonParIdUtilisateurEtIdAmi(int $idUtilisateur, int $idAmi): ?Candidature
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idUtilisateurTag AND idAmi = :idAmiTag AND statutCandidature = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur, "idAmiTag" => $idAmi]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererBrouillonParIdUtilisateurEtIdChallenge(int $idUtilisateur, int $idChallenge): ?Candidature
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idUtilisateurTag AND idChallenge = :idChallengeTag AND statutCandidature = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur, "idChallengeTag" => $idChallenge]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererSoumis(): array
    {
        $sql = "SELECT * FROM Candidature WHERE statutCandidature != 'brouillon' ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $candidatures = [];
        foreach ($pdoStatement as $t) {
            $candidatures[] = $this->construireDepuisTableauSQL($t);
        }
        return $candidatures;
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        $sql = "SELECT * FROM Candidature WHERE statutCandidature != 'brouillon' OR (statutCandidature = 'brouillon' AND idUtilisateur = :idAdminTag) ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idAdminTag' => $idAdmin]);
        $candidatures = [];
        foreach ($pdoStatement as $t) {
            $candidatures[] = $this->construireDepuisTableauSQL($t);
        }
        return $candidatures;
    }

    public function recupererBrouillonsParAdmin(int $idAdmin): array
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idAdmin AND statutCandidature = 'brouillon' ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idAdmin" => $idAdmin]);
        $brouillons = [];
        foreach ($pdoStatement as $t) {
            $brouillons[] = $this->construireDepuisTableauSQL($t);
        }
        return $brouillons;
    }

    public function recupererBrouillonParAdmin(int $idAdmin): ?Candidature
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idAdminTag AND statutCandidature = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idAdminTag" => $idAdmin]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererBrouillonParAdminEtAmi(int $idAdmin, int $idAmi): ?Candidature
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idAdmin AND idAmi = :idAmi AND statutCandidature = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idAdmin" => $idAdmin, "idAmi" => $idAmi]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererBrouillonParAdminEtChallenge(int $idAdmin, int $idChallenge): ?Candidature
    {
        $sql = "SELECT * FROM Candidature WHERE idUtilisateur = :idAdmin AND idChallenge = :idChallenge AND statutCandidature = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idAdmin" => $idAdmin, "idChallenge" => $idChallenge]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }
}