<?php

namespace App\Gigamed\Modele\HTTP;

class Cookie
{
    public static function contient(string $cle) : bool {
        return isset($_COOKIE[$cle]);
    }

    public static function enregistrer(string $cle, mixed $valeur, ?int $dureeExpiration = null): void
    {
        $valeurJSON = json_encode($valeur);
        if ($dureeExpiration === null)
            setcookie($cle, $valeurJSON, 0);
        else
            setcookie($cle, $valeurJSON, time() + $dureeExpiration);
    }

    public static function lire(string $cle): mixed
    {
        return json_decode($_COOKIE[$cle], true);
    }

    public static function supprimer(string $cle) : void
    {
        unset($_COOKIE[$cle]);
        setcookie($cle, "", 1);
    }
}