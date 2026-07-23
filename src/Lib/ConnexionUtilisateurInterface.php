<?php

namespace App\Gigamed\Lib;

interface ConnexionUtilisateurInterface
{
    public function connecter(string $idUtilisateur): void;

    public function estConnecte(): bool;

    public function deconnecter();

    public function getLoginUtilisateurConnecte(): ?string;

    public function estUtilisateur($login): bool;
}