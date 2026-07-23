<?php

namespace App\Gigamed\Modele\HTTP;

use App\Gigamed\Configuration\ConfigurationSite;
use Exception;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    public function verifierDerniereActivite(int $dureeExpiration) : void
    {
        if ($dureeExpiration == 0)
            return;

        if (isset($_SESSION['derniereActivite']) && (time() - $_SESSION['derniereActivite'] > ($dureeExpiration)))
            session_unset();

        $_SESSION['derniereActivite'] = time();

    }

    public static function getInstance(): Session
    {
        if (is_null(Session::$instance)) {
            Session::$instance = new Session(); 

            
            $dureeExpiration = ConfigurationSite::getDureeExpirationSession();
            Session::$instance->verifierDerniereActivite($dureeExpiration);
        }
        return Session::$instance;
    }

    public function contient(string $nom): bool
    {
        return isset($_SESSION[$nom]);
    }

    public function enregistrer(string $nom, mixed $valeur): void
    {
        $_SESSION[$nom] = $valeur;
    }

    public function lire(string $nom): mixed
    {
        return $_SESSION[$nom];
    }

    public function supprimer(string $nom): void
    {
        unset($_SESSION[$nom]);
    }

    public function detruire() : void
    {
        session_unset();
        session_destroy();
        Cookie::supprimer(session_name());
        Session::$instance = null;
    }
}