<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\Noter;

class NoterRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees) {}

    public function sauvegarder(int $idUtilisateur, int $idCandidature, int $idCritere, int $valeur, string $commentaire): void
    {
        $sql = "INSERT INTO Noter (idUtilisateur, idCandidature, idCritere, valeur, commentaire, dateNote)
                VALUES (:idUtilisateur, :idCandidature, :idCritere, :valeur, :commentaire, :dateNote)
                ON DUPLICATE KEY UPDATE
                    valeur      = VALUES(valeur),
                    commentaire = VALUES(commentaire),
                    dateNote    = VALUES(dateNote)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            'idUtilisateur' => $idUtilisateur,
            'idCandidature' => $idCandidature,
            'idCritere'     => $idCritere,
            'valeur'        => $valeur,
            'commentaire'   => $commentaire,
            'dateNote'      => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    
    public function recupererParUtilisateurEtCandidature(int $idUtilisateur, int $idCandidature): array
    {
        $sql = "SELECT * FROM Noter WHERE idUtilisateur = :idUtilisateur AND idCandidature = :idCandidature";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute([
            'idUtilisateur' => $idUtilisateur,
            'idCandidature' => $idCandidature,
        ]);
        $result = [];
        foreach ($pdoStatement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['idCritere']] = new Noter(
                (int) $row['idUtilisateur'],
                (int) $row['idCandidature'],
                (int) $row['idCritere'],
                (int) $row['valeur'],
                $row['commentaire'],
                new \DateTime($row['dateNote'])
            );
        }
        return $result;
    }

    

    public function calculerScoresParCandidatures(array $idsCandidatures): array
    {
        if (empty($idsCandidatures)) return [];
        $placeholders = implode(',', array_fill(0, count($idsCandidatures), '?'));
        $sql = "SELECT avg_par_critere.idCandidature,
                       ROUND(SUM(avg_par_critere.moy * c.ponderation) / SUM(c.ponderation) * 10, 1) AS score
                FROM (
                    SELECT idCandidature, idCritere, AVG(valeur) AS moy
                    FROM Noter
                    WHERE idCandidature IN ($placeholders)
                    GROUP BY idCandidature, idCritere
                ) avg_par_critere
                JOIN Critere c ON c.idCritere = avg_par_critere.idCritere
                GROUP BY avg_par_critere.idCandidature";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(array_values($idsCandidatures));
        $result = [];
        foreach ($pdoStatement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['idCandidature']] = (float) $row['score'];
        }
        return $result;
    }

    
    public function recupererParCandidatures(array $idsCandidatures, int $idUtilisateur): array
    {
        if (empty($idsCandidatures)) return [];
        $placeholders = implode(',', array_fill(0, count($idsCandidatures), '?'));
        $sql = "SELECT * FROM Noter WHERE idUtilisateur = ? AND idCandidature IN ($placeholders)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(array_merge([$idUtilisateur], array_values($idsCandidatures)));
        $result = [];
        foreach ($pdoStatement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['idCandidature']][(int) $row['idCritere']] = new Noter(
                (int) $row['idUtilisateur'],
                (int) $row['idCandidature'],
                (int) $row['idCritere'],
                (int) $row['valeur'],
                $row['commentaire'],
                new \DateTime($row['dateNote'])
            );
        }
        return $result;
    }
}