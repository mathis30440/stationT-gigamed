<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (strpos($uri, '/ressources/') === 0) {
    $file = __DIR__ . $uri;
    if (file_exists($file) && is_file($file)) {
        $types = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'pdf'   => 'application/pdf',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

$webFile = __DIR__ . '/web' . $uri;
if ($uri !== '/' && file_exists($webFile) && is_file($webFile)) {
    return false;
}

require_once __DIR__ . '/web/controleurFrontal.php';