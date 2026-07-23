<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Structure;
use App\Gigamed\Modele\DataObject\Utilisateur;

interface UtilisateurServiceInterface
{
    public function authentifier(string $email, string $mdp): Utilisateur;
    public function inscrire(array $donneesUtilisateur, array $donneesStructure): void;
    public function verifierEmail(string $token): void;
    public function modifierProfil(array $donneesUtilisateur, array $donneesStructure, int $idUtilisateur): void;
    public function modifierParAdmin(array $donneesUtilisateur, array $donneesStructure, int $idUtilisateur): void;
    public function recupererParEmail(string $email): ?Utilisateur;
    public function recupererStructure(int $idStructure): ?Structure;
    public function recupererTous(): array;
    public function changerRole(int $idUtilisateur, string $role): void;
    public function supprimer(int $idUtilisateur): void;
    public function creerParAdmin(array $donnees, array $donneesStructure = []): void;
    public function choisirMotDePasse(string $token, string $motDePasse): void;
    public function recupererParRole(\App\Gigamed\Modele\DataObject\RoleUtilisateur ...$roles): array;
    public function recupererUtilisateurOuNullParId(?int $id): ?\App\Gigamed\Modele\DataObject\Utilisateur;
}