<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (strpos($uri, '/ressources/') === 0) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && is_file($file)) {
        return false;
    }
}

if (strpos($uri, '/web/') === 0) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/web/controleurFrontal.php';