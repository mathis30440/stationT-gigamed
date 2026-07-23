<?php

namespace App\Gigamed\Configuration;
class ConfigurationSite
{
    public static string $baseUrl = '';

    public static function getBaseUrl(): string
    {
        return getenv('APP_URL') ?: self::$baseUrl ?: 'http://localhost/gigamed/web';
    }

    static public function getDureeExpirationSession() : int {
        return 86400;
    }
}