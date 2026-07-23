<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Critere;
use App\Gigamed\Modele\DataObject\Noter;

interface NotationServiceInterface
{
    
    public function recupererCriteres(?int $idAmi, ?int $idChallenge): array;

    
    public function recupererNotes(int $idUtilisateur, int $idCandidature): array;

    public function sauvegarder(int $idUtilisateur, int $idCandidature, array $notes, array $commentaires): void;

    
    public function calculerScores(array $idsCandidatures): array;

    
    public function recupererIdsCriteresAmi(int $idAmi): array;

    
    public function recupererIdsCriteresChallenge(int $idChallenge): array;

    public function sauvegarderCriteresAmi(int $idAmi, array $idsCriteres): void;

    public function sauvegarderCriteresChallenge(int $idChallenge, array $idsCriteres): void;

    
    public function recupererNotesParCandidatures(array $idsCandidatures, int $idUtilisateur): array;

    
    public function recupererTousCriteres(): array;
}