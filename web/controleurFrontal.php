<?php

////////////////////
// Initialisation //
////////////////////
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

Request::setTrustedProxies(
    ['REMOTE_ADDR'],
    Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
);

$response = App\Gigamed\Controleur\RouteurURL::traiterRequete(Request::createFromGlobals());
$response->send();