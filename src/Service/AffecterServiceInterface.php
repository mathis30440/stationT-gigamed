<?php

namespace App\Gigamed\Service;

interface AffecterServiceInterface
{
    public function affecter(int $idUtilisateur, int $idCandidature): void;

    public function desaffecter(int $idUtilisateur, int $idCandidature): void;

    public function estAffecte(int $idUtilisateur, int $idCandidature): bool;

    
    public function recupererIdUtilisateursParIdCandidature(int $idCandidature): array;

    
    public function recupererIdCandidaturesParIdUtilisateur(int $idUtilisateur): array;

    
    public function recupererToutesLesAffectations(): array;
}