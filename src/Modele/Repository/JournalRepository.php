<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Journal;

class JournalRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string { return "Journal"; }
    protected function getNomClePrimaire(): string { return "idJournal"; }

    protected function getNomColonnes(): array
    {
        return ["idJournal", "action", "objet", "horodatage", "idUtilisateur"];
    }

    protected function getNomColonnesExecute(): array
    {
        return [":idJournalTag", ":actionTag", ":objetTag", ":horodatageTag", ":idUtilisateurTag"];
    }

    protected function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idJournalTag"     => $objet->getIdJournal(),
            "actionTag"        => $objet->getAction(),
            "objetTag"         => $objet->getObjet(),
            "horodatageTag"    => $objet->getHorodatage()->format('Y-m-d H:i:s'),
            "idUtilisateurTag" => $objet->getIdUtilisateur(),
        ];
    }

    protected function construireDepuisTableauSQL(array $t): Journal
    {
        return new Journal(
            $t['action'],
            $t['objet'],
            new \DateTime($t['horodatage']),
            (int) $t['idUtilisateur'],
            (int) $t['idJournal'],
        );
    }

    protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void
    {
        
        $object->setIdJournal((int) $pdo->lastInsertId());
    }

    public function recupererTousAvecNom(int $limite = 500): array
    {
        $sql = "SELECT j.*, u.nomUtilisateur, u.prenomUtilisateur
                FROM Journal j
                LEFT JOIN Utilisateur u ON j.idUtilisateur = u.idUtilisateur
                ORDER BY j.horodatage DESC
                LIMIT :limite";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $pdoStatement->execute();
        $result = [];
        foreach ($pdoStatement as $t) {
            $result[] = [
                'journal'    => $this->construireDepuisTableauSQL($t),
                'nomComplet' => trim(($t['prenomUtilisateur'] ?? '') . ' ' . ($t['nomUtilisateur'] ?? '')),
            ];
        }
        return $result;
    }
}