<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Ami;

interface AmiServiceInterface
{
    public function recupererAmisOuverts(): array;
    public function recupererParId(int $id): ?Ami;
    public function recupererTous(): array;
    public function recupererSoumis(): array;
    public function recupererPourAdmin(int $idAdmin): array;
    public function archiver(int $idAmi): void;
    public function creer(array $donnees, int $idUtilisateur): Ami;
    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idBrouillonCharge = null): Ami;
    public function recupererBrouillon(int $idUtilisateur): ?Ami;
    public function publier(int $idAmi): void;
    public function supprimer(int $id): void;
    public function modifier(int $id, array $donnees): void;
}