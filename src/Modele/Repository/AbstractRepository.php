<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;

abstract class AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
    }

    public function ajouter(AbstractDataObject $object): bool
    {
        $separator = ",";
        $sql = "INSERT INTO " . $this->getNomTable() . " (" . join($separator, $this->getNomColonnes()) . ") VALUES (" . join($separator, $this->getNomColonnesExecute()) . ")";
        $pdo = $this->connexionBaseDeDonnees->getPdo();
        $pdoStatement = $pdo->prepare($sql);
        $values = $this->formatTableauSQL($object);
        $ok = $pdoStatement->execute($values);
        $this->recupererIdLastInsert($pdo, $object);
        return $ok;
    }

    public function mettreAJour(AbstractDataObject $objet): bool
    {
        $nomTable = $this->getNomTable();
        $nomClePrimaire = $this->getNomClePrimaire();
        $nomColonnesFini = "";

        foreach ($this->getNomColonnes() as $colonne) {
            if ($colonne === $nomClePrimaire) continue;
            $nomColonnesFini .= $colonne . " = :" . $colonne . "Tag,";
        }

        $nomColonnesFini = rtrim($nomColonnesFini, ',');

        $sql = "UPDATE " . $nomTable . " SET " . $nomColonnesFini . " WHERE " . $nomClePrimaire . " = :" . $nomClePrimaire . "Tag";

        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $values = $this->formatTableauSQL($objet);

        return $pdoStatement->execute($values);
    }

    public function supprimer(mixed $valeurClePrimaire): bool
    {
        $nomTable = $this->getNomTable();
        $clePrimaire = $this->getNomClePrimaire();

        $sql = "DELETE FROM " . $nomTable . " WHERE " . $clePrimaire . " = :valeurClePrimaire";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $values = ["valeurClePrimaire" => $valeurClePrimaire];

        return $pdoStatement->execute($values);
    }

    public function recupererParClePrimaire(mixed $valeurClePrimaire): ?AbstractDataObject
    {
        $sql = "SELECT * FROM " . $this->getNomTable() . " WHERE " . $this->getNomClePrimaire() . " = :valeurClePrimaireTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);

        $values = ["valeurClePrimaireTag" => $valeurClePrimaire];
        $pdoStatement->execute($values);

        $objectFormatTableau = $pdoStatement->fetch();
        if ($objectFormatTableau === false) {
            return null;
        }

        return $this->construireDepuisTableauSQL($objectFormatTableau);
    }

    public function recuperer(): array
    {
        $tab = [];
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->query("SELECT * FROM " . $this->getNomTable());
        foreach ($pdoStatement as $objectFormatTableau) {
            $tab[] = $this->construireDepuisTableauSQL($objectFormatTableau);
        }
        return $tab;
    }

    abstract protected function getNomTable(): string;

    abstract protected function construireDepuisTableauSQL(array $objectFormatTableau): AbstractDataObject;

    abstract protected function getNomClePrimaire(): string|array;

    abstract protected function getNomColonnes(): array;

    abstract protected function getNomColonnesExecute(): array;

    abstract protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void;

    abstract protected function formatTableauSQL(AbstractDataObject $objet): array;
}