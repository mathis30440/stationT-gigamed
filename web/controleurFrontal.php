<?php

////////////////////
// Initialisation //
////////////////////
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';


/////////////
// Routage //
/////////////

$response = App\Gigamed\Controleur\RouteurURL::traiterRequete(Request::createFromGlobals());
$response->send();