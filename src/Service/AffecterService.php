<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\Repository\AffecterRepository;

class AffecterService implements AffecterServiceInterface
{
    public function __construct(private AffecterRepository $affecterRepository) {}

    public function affecter(int $idUtilisateur, int $idCandidature): void
    {
        $this->affecterRepository->affecter($idUtilisateur, $idCandidature);
    }

    public function desaffecter(int $idUtilisateur, int $idCandidature): void
    {
        $this->affecterRepository->desaffecter($idUtilisateur, $idCandidature);
    }

    public function estAffecte(int $idUtilisateur, int $idCandidature): bool
    {
        return $this->affecterRepository->estAffecte($idUtilisateur, $idCandidature);
    }

    
    public function recupererIdUtilisateursParIdCandidature(int $idCandidature): array
    {
        return $this->affecterRepository->recupererIdUtilisateursParIdCandidature($idCandidature);
    }

    
    public function recupererIdCandidaturesParIdUtilisateur(int $idUtilisateur): array
    {
        return $this->affecterRepository->recupererIdCandidaturesParIdUtilisateur($idUtilisateur);
    }

    
    public function recupererToutesLesAffectations(): array
    {
        return $this->affecterRepository->recupererToutesLesAffectations();
    }
}