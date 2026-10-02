<?php

namespace App\Gigamed\Configuration;

use PDO;

class ConfigurationBDDMySQL implements ConfigurationBDDInterface
{
    public function getLogin(): string
    {
        return getenv('DB_USER');
    }

    public function getMotDePasse(): string
    {
        return getenv('DB_PASSWORD');
    }

    public function getDSN(): string
    {
        $hostname = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $nomBDD = getenv('DB_NAME');

        return "mysql:host={$hostname};port={$port};dbname={$nomBDD};charset=utf8mb4";
    }

    public function getOptions(): array
    {
        return [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
    }
}