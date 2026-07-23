<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\Utilisateur;

class AffecterRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees) {}

    public function affecter(int $idUtilisateur, int $idCandidature): void
    {
        if ($this->estAffecte($idUtilisateur, $idCandidature)) return;

        $sql = "INSERT INTO Affecter (idUtilisateur, idCandidature, dateAffectation) VALUES (:idUtilisateurTag, :idCandidatureTag, :dateTag)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            'idUtilisateurTag'  => $idUtilisateur,
            'idCandidatureTag'  => $idCandidature,
            'dateTag'           => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function desaffecter(int $idUtilisateur, int $idCandidature): void
    {
        $sql = "DELETE FROM Affecter WHERE idUtilisateur = :idUtilisateurTag AND idCandidature = :idCandidatureTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            'idUtilisateurTag' => $idUtilisateur,
            'idCandidatureTag' => $idCandidature,
        ]);
    }

    public function estAffecte(int $idUtilisateur, int $idCandidature): bool
    {
        $sql = "SELECT COUNT(*) FROM Affecter WHERE idUtilisateur = :idUtilisateurTag AND idCandidature = :idCandidatureTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            'idUtilisateurTag' => $idUtilisateur,
            'idCandidatureTag' => $idCandidature,
        ]);
        return (int) $pdoStatement->fetchColumn() > 0;
    }

    
    public function recupererIdUtilisateursParIdCandidature(int $idCandidature): array
    {
        $sql = "SELECT idUtilisateur FROM Affecter WHERE idCandidature = :idCandidatureTag ORDER BY dateAffectation ASC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idCandidatureTag' => $idCandidature]);
        return $pdoStatement->fetchAll(\PDO::FETCH_COLUMN);
    }

    
    public function recupererIdCandidaturesParIdUtilisateur(int $idUtilisateur): array
    {
        $sql = "SELECT idCandidature FROM Affecter WHERE idUtilisateur = :idUtilisateur ORDER BY dateAffectation ASC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(['idUtilisateur' => $idUtilisateur]);
        return $pdoStatement->fetchAll(\PDO::FETCH_COLUMN);
    }

    
    public function recupererToutesLesAffectations(): array
    {
        $sql = "SELECT idCandidature, idUtilisateur FROM Affecter ORDER BY dateAffectation ASC";
        $rows = $this->connexionBaseDeDonnees->getPdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['idCandidature']][] = (int)$row['idUtilisateur'];
        }
        return $result;
    }
}