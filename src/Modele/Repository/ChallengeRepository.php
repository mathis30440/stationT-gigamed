<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Challenge;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutChallenge;
use App\Gigamed\Modele\DataObject\TypeChallenge;

class ChallengeRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Challenge";
    }

    public function getNomClePrimaire(): string
    {
        return "idChallenge";
    }

    public function getNomColonnes(): array
    {
        return [
            "idChallenge", "titreChallenge", "objectifChallenge", "publicVise",
            "perimetrePrioritaire", "exemplesInnovations", "casUsagePilote",
            "perimetreCasUsage", "dureeIndicative", "sortieAttendue", "partenaires",
            "filliere", "typeChallenge", "dateDepot", "statutChallenge", "idBesoin",
        ];
    }

    public function getNomColonnesExecute(): array
    {
        return [
            ":idChallengeTag", ":titreChallengeTag", ":objectifChallengeTag", ":publicViseTag",
            ":perimetrePrioritaireTag", ":exemplesInnovationsTag", ":casUsagePiloteTag",
            ":perimetreCasUsageTag", ":dureeIndicativeTag", ":sortieAttendueTag", ":partenairesTag",
            ":filliereTag", ":typeChallengeTag", ":dateDepotTag", ":statutChallengeTag", ":idBesoinTag",
        ];
    }

    public function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idChallengeTag"          => $objet->getIdChallenge(),
            "titreChallengeTag"       => $objet->getTitreChallenge(),
            "objectifChallengeTag"    => $objet->getObjectifChallenge(),
            "publicViseTag"           => $objet->getPublicVise(),
            "perimetrePrioritaireTag" => $objet->getPerimetrePrioritaire(),
            "exemplesInnovationsTag"  => $objet->getExemplesInnovations(),
            "casUsagePiloteTag"       => $objet->getCasUsagePilote(),
            "perimetreCasUsageTag"    => $objet->getPerimetreCasUsage(),
            "dureeIndicativeTag"      => $objet->getDureeIndicative(),
            "sortieAttendueTag"       => $objet->getSortieAttendue(),
            "partenairesTag"          => $objet->getPartenaires(),
            "filliereTag"             => $objet->getFiliere()->value,
            "typeChallengeTag"        => $objet->getTypeChallenge()->value,
            "dateDepotTag"            => $objet->getDateDepot()->format('Y-m-d H:i:s'),
            "statutChallengeTag"      => $objet->getStatutChallenge()->value,
            "idBesoinTag"             => $objet->getIdBesoin(),
        ];
    }

    public function construireDepuisTableauSQL(array $t): Challenge
    {
        return new Challenge(
            $t['titreChallenge'],
            $t['objectifChallenge'],
            $t['publicVise'],
            $t['perimetrePrioritaire'],
            $t['exemplesInnovations'],
            $t['casUsagePilote'],
            $t['perimetreCasUsage'],
            $t['dureeIndicative'],
            $t['sortieAttendue'],
            $t['partenaires'],
            Filiere::from($t['filliere']),
            TypeChallenge::from($t['typeChallenge']),
            new \DateTime($t['dateDepot']),
            StatutChallenge::from($t['statutChallenge']),
            (int) $t['idBesoin'],
            isset($t['idChallenge']) ? (int) $t['idChallenge'] : null
        );
    }

    public function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $objet): void
    {
        
        $objet->setIdChallenge((int) $pdo->lastInsertId());
    }

    public function recupererChallengesOuverts(): array
    {
        $sql = "SELECT * FROM Challenge WHERE statutChallenge = 'ouvert' ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $challenges = [];
        foreach ($pdoStatement as $t) {
            $challenges[] = $this->construireDepuisTableauSQL($t);
        }
        return $challenges;
    }

    public function recupererSoumis(): array
    {
        $sql = "SELECT * FROM Challenge WHERE statutChallenge != 'brouillon' ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $challenges = [];
        foreach ($pdoStatement as $t) {
            $challenges[] = $this->construireDepuisTableauSQL($t);
        }
        return $challenges;
    }
}