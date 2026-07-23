<?php

namespace App\Gigamed\Controleur;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Twig\Environment;

class ControleurGenerique
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    protected function rediriger(string $nomRoute, array $parametres = []): RedirectResponse
    {
        
        $generateurUrl = $this->container->get('Symfony\Component\Routing\Generator\UrlGenerator');
        if ($parametres === []) {
            $url = $generateurUrl->generate($nomRoute);
        } else {
            $url = $generateurUrl->generate($nomRoute, $parametres);
        }
        $response = new RedirectResponse($url);
        return $response;
    }

    public function afficherErreur($messageErreur = "", $statusCode = 400): Response
    {
        $reponse = $this->afficherTwig('erreur.html.twig', [
            "messageErreur" => $messageErreur
        ]);

        $reponse->setStatusCode($statusCode);
        return $reponse;
    }

    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        
        $twig = $this->container->get('Twig\Environment');
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

    #[Route(path: '/', name: 'accueil', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->afficherTwig('accueil.html.twig');
    }
}