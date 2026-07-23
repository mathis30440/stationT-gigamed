<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Critere;

class CritereRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string
    {
        return "Critere";
    }

    protected function getNomClePrimaire(): string
    {
        return "idCritere";
    }

    protected function getNomColonnes(): array
    {
        return ["idCritere", "libelle", "ponderation", "commentaire"];
    }

    protected function getNomColonnesExecute(): array
    {
        return [":idCritereTag", ":libelleTag", ":ponderationTag", ":commentaireTag"];
    }

    protected function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idCritereTag"    => $objet->getIdCritere(),
            "libelleTag"      => $objet->getLibelle(),
            "ponderationTag"  => $objet->getPonderation(),
            "commentaireTag"  => $objet->getCommentaire(),
        ];
    }

    protected function construireDepuisTableauSQL(array $t): Critere
    {
        return new Critere(
            $t['libelle'],
            (float) $t['ponderation'],
            (int) $t['idCritere'],
            $t['commentaire'] ?? null,
        );
    }

    protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void
    {
        
        $object->setIdCritere((int) $pdo->lastInsertId());
    }

    
    public function recupererParIdAmi(int $idAmi): array
    {
        $sql = "SELECT c.* FROM Critere c
                JOIN AppartenirAmi ac ON ac.idCritere = c.idCritere
                WHERE ac.idAmi = :idAmi";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idAmi' => $idAmi]);
        return array_map([$this, 'construireDepuisTableauSQL'], $pdoStatement->fetchAll(\PDO::FETCH_ASSOC));
    }

    
    public function recupererParIdChallenge(int $idChallenge): array
    {
        $sql = "SELECT c.* FROM Critere c
                JOIN AppartenirChallenge cc ON cc.idCritere = c.idCritere
                WHERE cc.idChallenge = :idChallenge";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idChallenge' => $idChallenge]);
        return array_map([$this, 'construireDepuisTableauSQL'], $pdoStatement->fetchAll(\PDO::FETCH_ASSOC));
    }
}