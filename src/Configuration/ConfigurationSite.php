<?php

namespace App\Gigamed\Configuration;
class ConfigurationSite
{
    public static string $baseUrl = 'http://localhost/gigamed/web';

    static public function getDureeExpirationSession() : int {
        return 86400;
    }
}