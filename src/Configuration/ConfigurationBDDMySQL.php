<?php

namespace App\Gigamed\Configuration;
use PDO;

class ConfigurationBDDMySQL implements ConfigurationBDDInterface
{
    private string $login = "root";
    private string $motDePasse = "root";
    private string $nomBDD = "gigamed";
    private string $hostname = "gigamed-mysql";
    private string  $port = '3306';

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function getDSN() : string{
        return "mysql:host={$this->hostname};port={$this->port};dbname={$this->nomBDD};charset=utf8mb4";
    }
    public function getOptions() : array {
        
        
        return array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );
    }
}