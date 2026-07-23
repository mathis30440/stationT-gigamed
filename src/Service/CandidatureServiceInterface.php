<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Candidature;

interface CandidatureServiceInterface
{
    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge): \App\Gigamed\Modele\DataObject\Candidature;
    public function recupererBrouillonParUtilisateurEtAmi(int $idUtilisateur, int $idAmi): ?\App\Gigamed\Modele\DataObject\Candidature;
    public function recupererBrouillonParUtilisateurEtChallenge(int $idUtilisateur, int $idChallenge): ?\App\Gigamed\Modele\DataObject\Candidature;
    public function deposer(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge): \App\Gigamed\Modele\DataObject\Candidature;
    public function recupererParId(int $id): ?Candidature;
    public function recupererParIdUtilisateur(int $idUtilisateur): array;
    public function existeParIdUtilisateur(int $idUtilisateur): bool;
    public function recupererTous(): array;
    public function recupererSoumis(): array;
    public function getStatutsAdmin(): array;
    public function passerEnInstructionParAmi(int $idAmi): void;
    public function passerEnInstructionParChallenge(int $idChallenge): void;
    public function changerStatut(int $idCandidature, string $statut, ?string $messageIncomplet = null, ?string $messageRefus = null): void;
    public function existeParUtilisateurEtAmi(int $idUtilisateur, int $idAmi): bool;
    public function existeParUtilisateurEtChallenge(int $idUtilisateur, int $idChallenge): bool;
    public function deposerParAdmin(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge, int $idAdmin = 0, ?int $idBrouillonCharge = null): \App\Gigamed\Modele\DataObject\Candidature;
    public function sauvegarderBrouillonParAdmin(array $donnees, int $idAdmin, ?int $idAmi, ?int $idChallenge, ?int $idBrouillonCharge = null): \App\Gigamed\Modele\DataObject\Candidature;
    public function recupererBrouillonParAdmin(int $idAdmin): ?\App\Gigamed\Modele\DataObject\Candidature;
    public function recupererBrouillonsParAdmin(int $idAdmin): array;
    public function recupererPourAdmin(int $idAdmin): array;
    public function supprimer(int $id): void;
    public function modifier(int $id, array $donnees): void;
    public function recupererParIdAmi(int $idAmi): array;
    public function recupererParIdChallenge(int $idChallenge): array;
}