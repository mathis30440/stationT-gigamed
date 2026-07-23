<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Ami;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutAmi;

class AmiRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Ami";
    }

    public function getNomClePrimaire(): string
    {
        return "idAmi";
    }

    public function getNomColonnes(): array
    {
        return [
            "idAmi", "titreAmi", "objectifAmi", "publicVise", "perimetrePrioritaire",
            "filiere", "exemplesInnovations", "casUsagePilote", "perimetreCasUsage",
            "dureeIndicative", "sortieAttendue", "partenaires",
            "dateOuverture", "dateCloture", "dateAuditions", "dateResultats", "dateDemarrage",
            "statutAmi", "idUtilisateur", "idBesoin",
        ];
    }

    public function getNomColonnesExecute(): array
    {
        return [
            ":idAmiTag", ":titreAmiTag", ":objectifAmiTag", ":publicViseTag", ":perimetrePrioritaireTag",
            ":filiereTag", ":exemplesInnovationsTag", ":casUsagePiloteTag", ":perimetreCasUsageTag",
            ":dureeIndicativeTag", ":sortieAttendueTag", ":partenairesTag",
            ":dateOuvertureTag", ":dateClotureTag", ":dateAuditionsTag", ":dateResultatsTag", ":dateDemarrageTag",
            ":statutAmiTag", ":idUtilisateurTag", ":idBesoinTag",
        ];
    }

    public function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idAmiTag"                => $objet->getIdAmi(),
            "titreAmiTag"             => $objet->getTitreAmi(),
            "objectifAmiTag"          => $objet->getObjectifAmi(),
            "publicViseTag"           => $objet->getPublicVise(),
            "perimetrePrioritaireTag" => $objet->getPerimetrePrioritaire(),
            "filiereTag"              => $objet->getFiliere()->value,
            "exemplesInnovationsTag"  => $objet->getExemplesInnovations(),
            "casUsagePiloteTag"       => $objet->getCasUsagePilote(),
            "perimetreCasUsageTag"    => $objet->getPerimetreCasUsage(),
            "dureeIndicativeTag"      => $objet->getDureeIndicative(),
            "sortieAttendueTag"       => $objet->getSortieAttendue(),
            "partenairesTag"          => $objet->getPartenaires(),
            "dateOuvertureTag"        => $objet->getDateOuverture()->format('Y-m-d'),
            "dateClotureTag"          => $objet->getDateCloture()->format('Y-m-d'),
            "dateAuditionsTag"        => $objet->getDateAuditions()->format('Y-m-d'),
            "dateResultatsTag"        => $objet->getDateResultats()->format('Y-m-d'),
            "dateDemarrageTag"        => $objet->getDateDemarrage()->format('Y-m-d'),
            "statutAmiTag"            => $objet->getStatutAmi()->value,
            "idUtilisateurTag"        => $objet->getIdUtilisateur(),
            "idBesoinTag"             => $objet->getIdBesoin(),
        ];
    }

    public function construireDepuisTableauSQL(array $t): Ami
    {
        return new Ami(
            $t['titreAmi'],
            $t['objectifAmi'],
            $t['publicVise'],
            $t['perimetrePrioritaire'],
            Filiere::from($t['filiere']),
            $t['exemplesInnovations'],
            $t['casUsagePilote'],
            $t['perimetreCasUsage'],
            $t['dureeIndicative'],
            $t['sortieAttendue'],
            $t['partenaires'],
            new \DateTime($t['dateOuverture']),
            new \DateTime($t['dateCloture']),
            new \DateTime($t['dateAuditions']),
            new \DateTime($t['dateResultats']),
            new \DateTime($t['dateDemarrage']),
            StatutAmi::from($t['statutAmi']),
            isset($t['idUtilisateur']) ? (int) $t['idUtilisateur'] : null,
            isset($t['idBesoin']) ? (int) $t['idBesoin'] : null,
            isset($t['idAmi']) ? (int) $t['idAmi'] : null
        );
    }

    public function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $objet): void
    {
        
        $objet->setIdAmi((int) $pdo->lastInsertId());
    }

    public function recupererAmisOuverts(): array
    {
        $sql = "SELECT * FROM Ami WHERE statutAmi = 'ouvert' ORDER BY dateCloture ASC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $amis = [];
        foreach ($pdoStatement as $t) {
            $amis[] = $this->construireDepuisTableauSQL($t);
        }
        return $amis;
    }

    public function recupererBrouillonParIdUtilisateur(int $idUtilisateur): ?Ami
    {
        $sql = "SELECT * FROM Ami WHERE idUtilisateur = :idUtilisateurTag AND statutAmi = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererSoumis(): array
    {
        $sql = "SELECT * FROM Ami WHERE statutAmi != 'brouillon' ORDER BY dateOuverture DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $amis = [];
        foreach ($pdoStatement as $t) {
            $amis[] = $this->construireDepuisTableauSQL($t);
        }
        return $amis;
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        $sql = "SELECT * FROM Ami WHERE statutAmi != 'brouillon' OR (statutAmi = 'brouillon' AND idUtilisateur = :idAdminTag) ORDER BY dateOuverture DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idAdminTag' => $idAdmin]);
        $amis = [];
        foreach ($pdoStatement as $t) {
            $amis[] = $this->construireDepuisTableauSQL($t);
        }
        return $amis;
    }
}