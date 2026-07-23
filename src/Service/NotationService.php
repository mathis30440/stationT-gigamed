<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\Repository\CritereRepository;
use App\Gigamed\Modele\Repository\NoterRepository;

class NotationService implements NotationServiceInterface
{
    public function __construct(
        private CritereRepository $critereRepository,
        private NoterRepository   $noterRepository,
    ) {}

    public function recupererCriteres(?int $idAmi, ?int $idChallenge): array
    {
        return $this->critereRepository->recuperer();
    }

    public function recupererNotes(int $idUtilisateur, int $idCandidature): array
    {
        return $this->noterRepository->recupererParUtilisateurEtCandidature($idUtilisateur, $idCandidature);
    }

    public function sauvegarder(int $idUtilisateur, int $idCandidature, array $notes, array $commentaires): void
    {
        foreach ($notes as $idCritere => $valeur) {
            $v = (int) $valeur;
            if ($v < 1 || $v > 10) continue;
            $commentaire = trim($commentaires[$idCritere] ?? '');
            $this->noterRepository->sauvegarder($idUtilisateur, $idCandidature, (int) $idCritere, $v, $commentaire);
        }
    }

    
    public function calculerScores(array $idsCandidatures): array
    {
        return $this->noterRepository->calculerScoresParCandidatures($idsCandidatures);
    }

    public function recupererIdsCriteresAmi(int $idAmi): array { return []; }
    public function recupererIdsCriteresChallenge(int $idChallenge): array { return []; }
    public function sauvegarderCriteresAmi(int $idAmi, array $idsCriteres): void {}
    public function sauvegarderCriteresChallenge(int $idChallenge, array $idsCriteres): void {}

    public function recupererNotesParCandidatures(array $idsCandidatures, int $idUtilisateur): array
    {
        return $this->noterRepository->recupererParCandidatures($idsCandidatures, $idUtilisateur);
    }

    public function recupererTousCriteres(): array
    {
        return $this->critereRepository->recuperer();
    }
}