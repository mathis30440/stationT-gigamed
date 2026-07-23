<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Document;
use App\Gigamed\Modele\DataObject\TypeDocument;
use App\Gigamed\Modele\Repository\DocumentRepository;
use App\Gigamed\Service\Exception\ServiceException;

class DocumentService implements DocumentServiceInterface
{
    private const EXTENSIONS_AUTORISEES = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt'];
    private const TAILLE_MAX = 10 * 1024 * 1024; 

    public function __construct(private DocumentRepository $documentRepository)
    {
    }

    public function sauvegarderFichiers(int $idCandidature, array $fichiers, string $uploadDir): void
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        foreach ($fichiers as $nomChamp => $fichier) {
            if ($fichier['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($fichier['error'] !== UPLOAD_ERR_OK) {
                throw new ServiceException("Erreur lors de l'upload du fichier « {$nomChamp} ».");
            }

            if ($fichier['size'] > self::TAILLE_MAX) {
                throw new ServiceException("Le fichier « {$nomChamp} » dépasse la taille maximale autorisée (10 Mo).");
            }

            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, self::EXTENSIONS_AUTORISEES, true)) {
                throw new ServiceException("Le format du fichier « {$nomChamp} » n'est pas accepté.");
            }

            $typeDocument = TypeDocument::tryFrom($extension);
            if ($typeDocument === null) {
                throw new ServiceException("Type de fichier non reconnu : {$extension}.");
            }

            
            $ancien = $this->documentRepository->recupererParIdCandidatureEtNom($idCandidature, $nomChamp);
            if ($ancien !== null) {
                $ancienChemin = $uploadDir . DIRECTORY_SEPARATOR . basename($ancien->getCheminDocument());
                if (file_exists($ancienChemin)) {
                    @unlink($ancienChemin);
                }
                $this->documentRepository->supprimer($ancien->getIdDocument());
            }

            $nomFichier = uniqid("doc_{$idCandidature}_", true) . '.' . $extension;
            $chemin = $uploadDir . DIRECTORY_SEPARATOR . $nomFichier;

            if (!move_uploaded_file($fichier['tmp_name'], $chemin)) {
                throw new ServiceException("Impossible de sauvegarder le fichier « {$nomChamp} ».");
            }

            $document = new Document(
                $nomChamp,
                $typeDocument,
                'ressources/uploads/candidatures/' . $nomFichier,
                new \DateTime(),
                $idCandidature,
            );

            $this->documentRepository->ajouter($document);
        }
    }

    public function recupererParIdCandidature(int $idCandidature): array
    {
        return $this->documentRepository->recupererParIdCandidature($idCandidature);
    }

    public function recupererParId(int $idDocument): ?Document
    {
        return $this->documentRepository->recupererParClePrimaire($idDocument);
    }

    public function supprimer(int $idDocument, string $uploadDir): void
    {
        $doc = $this->documentRepository->recupererParClePrimaire($idDocument);
        if ($doc === null) {
            throw new ServiceException("Document introuvable.");
        }
        $chemin = $uploadDir . DIRECTORY_SEPARATOR . basename($doc->getCheminDocument());
        if (file_exists($chemin)) {
            @unlink($chemin);
        }
        $this->documentRepository->supprimer($idDocument);
    }
}