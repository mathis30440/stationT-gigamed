<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;

class AppartenirAmiRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees) {}

    
    public function recupererIdsCriteres(int $idAmi): array
    {
        $sql = "SELECT idCritere FROM AppartenirAmi WHERE idAmi = :idAmi";
        $stmt = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $stmt->execute(['idAmi' => $idAmi]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'idCritere');
    }

    public function sauvegarderCriteres(int $idAmi, array $idsCriteres): void
    {
        $pdo = $this->connexionBaseDeDonnees->getPdo();
        $pdo->prepare("DELETE FROM AppartenirAmi WHERE idAmi = :idAmi")->execute(['idAmi' => $idAmi]);
        if (empty($idsCriteres)) return;
        $stmt = $pdo->prepare("INSERT INTO AppartenirAmi (idAmi, idCritere) VALUES (:idAmi, :idCritere)");
        foreach ($idsCriteres as $idCritere) {
            $stmt->execute(['idAmi' => $idAmi, 'idCritere' => (int) $idCritere]);
        }
    }
}