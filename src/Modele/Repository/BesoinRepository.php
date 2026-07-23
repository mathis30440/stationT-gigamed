<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Besoin;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutBesoin;

class BesoinRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string
    {
        return "Besoin";
    }

    protected function getNomClePrimaire(): string
    {
        return "idBesoin";
    }

    protected function getNomColonnes(): array
    {
        return [
            "idBesoin", "titreBesoin", "filiere", "objectifBesoin",
            "publicVise", "perimetrePrioritaire", "vision", "enjeuxMajeurs",
            "besoinsPrioritaires", "exemplesInnovations", "partenaires",
            "dateDepot", "statutBesoin", "raisonRefus", "idUtilisateur",
        ];
    }

    protected function getNomColonnesExecute(): array
    {
        return [
            ":idBesoinTag", ":titreBesoinTag", ":filiereTag", ":objectifBesoinTag",
            ":publicViseTag", ":perimetrePrioritaireTag", ":visionTag", ":enjeuxMajeursTag",
            ":besoinsPrioritairesTag", ":exemplesInnovationsTag", ":partenairesTag",
            ":dateDepotTag", ":statutBesoinTag", ":raisonRefusTag", ":idUtilisateurTag",
        ];
    }

    protected function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idBesoinTag"               => $objet->getIdBesoin(),
            "titreBesoinTag"            => $objet->getTitreBesoin(),
            "filiereTag"                => $objet->getFiliere()->value,
            "objectifBesoinTag"         => $objet->getObjectifBesoin(),
            "publicViseTag"             => $objet->getPublicVise(),
            "perimetrePrioritaireTag"   => $objet->getPerimetrePrioritaire(),
            "visionTag"                 => $objet->getVision(),
            "enjeuxMajeursTag"          => $objet->getEnjeuxMajeurs(),
            "besoinsPrioritairesTag"    => $objet->getBesoinsPrioritaires(),
            "exemplesInnovationsTag"    => $objet->getExemplesInnovations(),
            "partenairesTag"            => $objet->getPartenaires(),
            "dateDepotTag"              => $objet->getDateDepot()->format('Y-m-d'),
            "statutBesoinTag"           => $objet->getStatutBesoin()->value,
            "raisonRefusTag"            => $objet->getRaisonRefus(),
            "idUtilisateurTag"          => $objet->getIdUtilisateur(),
        ];
    }

    protected function construireDepuisTableauSQL(array $t): Besoin
    {
        return new Besoin(
            $t['titreBesoin'],
            Filiere::from($t['filiere']),
            $t['objectifBesoin'],
            $t['publicVise'],
            $t['perimetrePrioritaire'],
            $t['vision'],
            $t['enjeuxMajeurs'],
            $t['besoinsPrioritaires'],
            $t['exemplesInnovations'],
            $t['partenaires'],
            new \DateTime($t['dateDepot']),
            StatutBesoin::from($t['statutBesoin']),
            (int) $t['idUtilisateur'],
            $t['raisonRefus'] ?? null,
            isset($t['idBesoin']) ? (int) $t['idBesoin'] : null,
        );
    }

    protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void
    {
        
        $object->setIdBesoin((int) $pdo->lastInsertId());
    }

    public function recupererParIdUtilisateur(int $idUtilisateur): array
    {
        $sql = "SELECT * FROM Besoin WHERE idUtilisateur = :idUtilisateurTag ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        $besoins = [];
        foreach ($pdoStatement as $t) {
            $besoins[] = $this->construireDepuisTableauSQL($t);
        }
        return $besoins;
    }

    public function existeParIdUtilisateur(int $idUtilisateur): bool
    {
        $sql = "SELECT COUNT(*) FROM Besoin WHERE idUtilisateur = :idUtilisateurTag AND statutBesoin != 'brouillon'";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        return (int) $pdoStatement->fetchColumn() > 0;
    }

    public function recupererBrouillonParIdUtilisateur(int $idUtilisateur): ?Besoin
    {
        $sql = "SELECT * FROM Besoin WHERE idUtilisateur = :idUtilisateurTag AND statutBesoin = 'brouillon' LIMIT 1";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idUtilisateurTag" => $idUtilisateur]);
        $t = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }

    public function recupererSoumis(): array
    {
        $sql = "SELECT * FROM Besoin WHERE statutBesoin != 'brouillon' ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query($sql);
        $besoins = [];
        foreach ($pdoStatement as $t) {
            $besoins[] = $this->construireDepuisTableauSQL($t);
        }
        return $besoins;
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        $sql = "SELECT * FROM Besoin WHERE statutBesoin != 'brouillon' OR (statutBesoin = 'brouillon' AND idUtilisateur = :idAdminTag) ORDER BY dateDepot DESC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idAdminTag' => $idAdmin]);
        $besoins = [];
        foreach ($pdoStatement as $t) {
            $besoins[] = $this->construireDepuisTableauSQL($t);
        }
        return $besoins;
    }
}