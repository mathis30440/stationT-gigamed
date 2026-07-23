<?php

namespace App\Gigamed\Service;

interface DocumentServiceInterface
{
    public function sauvegarderFichiers(int $idCandidature, array $fichiers, string $uploadDir): void;
    public function recupererParIdCandidature(int $idCandidature): array;
    public function supprimer(int $idDocument, string $uploadDir): void;
    public function recupererParId(int $idDocument): ?\App\Gigamed\Modele\DataObject\Document;
}