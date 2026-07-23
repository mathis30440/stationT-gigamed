<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;

class AppartenirChallengeRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees) {}

    
    public function recupererIdsCriteres(int $idChallenge): array
    {
        $sql = "SELECT idCritere FROM AppartenirChallenge WHERE idChallenge = :idChallenge";
        $stmt = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $stmt->execute(['idChallenge' => $idChallenge]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'idCritere');
    }

    public function sauvegarderCriteres(int $idChallenge, array $idsCriteres): void
    {
        $pdo = $this->connexionBaseDeDonnees->getPdo();
        $pdo->prepare("DELETE FROM AppartenirChallenge WHERE idChallenge = :idChallenge")->execute(['idChallenge' => $idChallenge]);
        if (empty($idsCriteres)) return;
        $stmt = $pdo->prepare("INSERT INTO AppartenirChallenge (idChallenge, idCritere) VALUES (:idChallenge, :idCritere)");
        foreach ($idsCriteres as $idCritere) {
            $stmt->execute(['idChallenge' => $idChallenge, 'idCritere' => (int) $idCritere]);
        }
    }
}