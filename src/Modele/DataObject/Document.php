<?php

namespace App\Gigamed\Modele\DataObject;

class Document extends AbstractDataObject
{
    private ?int $idDocument;
    private string $nomDocument;
    private TypeDocument $typeDocument;
    private string $cheminDocument;
    private \DateTime $dateUpload;
    private int $idCandidature;

    

    public function __construct(string $nomDocument, TypeDocument $typeDocument, string $cheminDocument, \DateTime $dateUpload, int $idCandidature, ?int $idDocument = null)
    {
        $this->idDocument = $idDocument;
        $this->nomDocument = $nomDocument;
        $this->typeDocument = $typeDocument;
        $this->cheminDocument = $cheminDocument;
        $this->dateUpload = $dateUpload;
        $this->idCandidature = $idCandidature;
    }

    public function getIdDocument(): ?int
    {
        return $this->idDocument;
    }

    public function getNomDocument(): string
    {
        return $this->nomDocument;
    }

    public function getTypeDocument(): TypeDocument
    {
        return $this->typeDocument;
    }

    public function getCheminDocument(): string
    {
        return $this->cheminDocument;
    }

    public function getDateUpload(): \DateTime
    {
        return $this->dateUpload;
    }

    public function getIdCandidature(): int
    {
        return $this->idCandidature;
    }

    public function setNomDocument(string $nomDocument): void
    {
        $this->nomDocument = $nomDocument;
    }

    public function setTypeDocument(TypeDocument $typeDocument): void
    {
        $this->typeDocument = $typeDocument;
    }

    public function setCheminDocument(string $cheminDocument): void
    {
        $this->cheminDocument = $cheminDocument;
    }

    public function setDateUpload(\DateTime $dateUpload): void
    {
        $this->dateUpload = $dateUpload;
    }

    public function setIdDocument(?int $idDocument): void
    {
        $this->idDocument = $idDocument;
    }
}