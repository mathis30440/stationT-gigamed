<?php

namespace App\Gigamed\Configuration;
class ConfigurationSite
{
    public static string $baseUrl = 'https://stationt.cahm.net';

    public static function getBaseUrl(): string
    {
        return self::$baseUrl;
    }

    static public function getDureeExpirationSession() : int {
        return 86400;
    }
}