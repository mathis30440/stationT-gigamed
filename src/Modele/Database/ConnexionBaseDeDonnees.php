<?php

namespace App\Gigamed\Modele\Database;

use App\Gigamed\Configuration\ConfigurationBDDInterface;
use PDO;

class ConnexionBaseDeDonnees
{
    private PDO $pdo;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function __construct(ConfigurationBDDInterface $config)
    {
        $configurationBDD = $config;

        
        $this->pdo = new PDO(
            $configurationBDD->getDSN(),
            $configurationBDD->getLogin(),
            $configurationBDD->getMotDePasse(),
            $configurationBDD->getOptions()
        );

        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}