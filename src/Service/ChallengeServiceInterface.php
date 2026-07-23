<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Challenge;

interface ChallengeServiceInterface
{
    public function recupererChallengesOuverts(): array;
    public function recupererParId(int $id): ?Challenge;
    public function recupererTous(): array;
    public function recupererSoumis(): array;
    public function cloturer(int $idChallenge): void;
    public function archiver(int $idChallenge): void;
    public function publier(int $idChallenge): int;
    public function supprimer(int $id): void;
    public function modifier(int $id, array $donnees): void;
}