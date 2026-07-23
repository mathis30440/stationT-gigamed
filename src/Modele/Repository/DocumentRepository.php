<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\Document;
use App\Gigamed\Modele\DataObject\TypeDocument;

class DocumentRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    protected function getNomTable(): string { return "Document"; }
    protected function getNomClePrimaire(): string { return "idDocument"; }

    protected function getNomColonnes(): array
    {
        return ["idDocument", "nomDocument", "typeDocument", "cheminDocument", "dateUpload", "idCandidature"];
    }

    protected function getNomColonnesExecute(): array
    {
        return [":idDocumentTag", ":nomDocumentTag", ":typeDocumentTag", ":cheminDocumentTag", ":dateUploadTag", ":idCandidatureTag"];
    }

    protected function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        return [
            "idDocumentTag"    => $objet->getIdDocument(),
            "nomDocumentTag"   => $objet->getNomDocument(),
            "typeDocumentTag"  => $objet->getTypeDocument()->value,
            "cheminDocumentTag"=> $objet->getCheminDocument(),
            "dateUploadTag"    => $objet->getDateUpload()->format('Y-m-d H:i:s'),
            "idCandidatureTag" => $objet->getIdCandidature(),
        ];
    }

    protected function construireDepuisTableauSQL(array $t): Document
    {
        return new Document(
            $t['nomDocument'],
            TypeDocument::from($t['typeDocument']),
            $t['cheminDocument'],
            new \DateTime($t['dateUpload']),
            (int) $t['idCandidature'],
            isset($t['idDocument']) ? (int) $t['idDocument'] : null,
        );
    }

    protected function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $object): void
    {
        
        $object->setIdDocument((int) $pdo->lastInsertId());
    }

    public function recupererParIdCandidature(int $idCandidature): array
    {
        $sql = "SELECT * FROM Document WHERE idCandidature = :idCandidatureTag ORDER BY dateUpload ASC";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["idCandidatureTag" => $idCandidature]);
        $docs = [];
        foreach ($pdoStatement as $t) {
            $docs[] = $this->construireDepuisTableauSQL($t);
        }
        return $docs;
    }

    public function mettreAJourIdCandidature(int $ancienId, int $nouvelId): void
    {
        $sql = "UPDATE Document SET idCandidature = :nouvelId WHERE idCandidature = :ancienId";
        $stmt = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $stmt->execute(["nouvelId" => $nouvelId, "ancienId" => $ancienId]);
    }

    public function recupererParIdCandidatureEtNom(int $idCandidature, string $nom): ?Document
    {
        $sql = "SELECT * FROM Document WHERE idCandidature = :idCandidatureTag AND nomDocument = :nomTag LIMIT 1";
        $stmt = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $stmt->execute(["idCandidatureTag" => $idCandidature, "nomTag" => $nom]);
        $t = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $t ? $this->construireDepuisTableauSQL($t) : null;
    }
}