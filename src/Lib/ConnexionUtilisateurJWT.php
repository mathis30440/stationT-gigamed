<?php

namespace App\Gigamed\Lib;

use App\Gigamed\Modele\HTTP\Cookie;

class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface
{
    public function connecter(string $idUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["idUtilisateur" => $idUtilisateur]));
    }

    public function estConnecte(): bool
    {
        return !is_null($this->getLoginUtilisateurConnecte());
    }

    public function deconnecter(): void
    {
        if (Cookie::contient("auth_token"))
            Cookie::supprimer("auth_token");
    }

    public function getLoginUtilisateurConnecte(): ?string
    {
        if (Cookie::contient("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnees = JsonWebToken::decoder($jwt);
            return $donnees["idUtilisateur"] ?? null;
        } else
            return null;
    }

    public function estUtilisateur($login): bool
    {
        return (ConnexionUtilisateurJWT::estConnecte() &&
            ConnexionUtilisateurJWT::getLoginUtilisateurConnecte() == $login
        );
    }
}