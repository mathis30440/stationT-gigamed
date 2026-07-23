<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Besoin;

interface BesoinServiceInterface
{
    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idBesoin = null): void;
    public function recupererBrouillonParIdUtilisateur(int $idUtilisateur): ?\App\Gigamed\Modele\DataObject\Besoin;
    public function soumettre(array $donnees, int $idUtilisateur, ?int $idBesoin = null): void;
    public function marquerTransformeEnChallenge(int $idBesoin): void;
    public function marquerTransformeEnAmi(int $idBesoin): void;
    public function soumettreParAdmin(array $donnees, int $idUtilisateur, int $idAdmin = 0, ?int $idBesoin = null): void;
    public function sauvegarderBrouillonParAdmin(array $donnees, int $idAdmin, ?int $idBesoin = null): void;
    public function recupererBrouillonParAdmin(int $idAdmin): ?Besoin;
    public function recupererPourAdmin(int $idAdmin): array;
    public function recupererParId(int $id): ?Besoin;
    public function recupererParIdUtilisateur(int $idUtilisateur): array;
    public function existeParIdUtilisateur(int $idUtilisateur): bool;
    public function recupererTous(): array;
    public function recupererSoumis(): array;
    public function refuser(int $idBesoin, ?string $raison = null): void;
    public function transformerEnChallenge(int $idBesoin, array $donnees): int;
    public function transformerEnAmi(int $idBesoin, array $donnees, int $idUtilisateur): int;
    public function supprimer(int $id): void;
    public function modifier(int $id, array $donnees): void;
}