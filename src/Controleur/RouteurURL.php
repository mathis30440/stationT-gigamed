<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\AttributeRouteControllerLoader;
use App\Gigamed\Lib\ConnexionUtilisateurSession;
use App\Gigamed\Lib\MessageFlash;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\TwigFunction;

class RouteurURL
{
    public static function traiterRequete(Request $requete): Response
    {
        $conteneur = new ContainerBuilder();
        $conteneur->setParameter('project_root', __DIR__ . '/../..');
        $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__ . '/../Configuration'));
        $loader->load('conteneur.yaml');

        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = new AttributeDirectoryLoader($fileLocator, $attrClassLoader)->load(__DIR__);

        $contexteRequete = new RequestContext()->fromRequest($requete);
        $generateurUrl = new UrlGenerator($routes, $contexteRequete);
        $conteneur->set(UrlGenerator::class, $generateurUrl);

        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);

        
        $twig = $conteneur->get('Twig\Environment');
        $controleurGenerique = $conteneur->get('App\Gigamed\Controleur\ControleurGenerique');

        $twig->addFunction(new TwigFunction('route', [$generateurUrl, 'generate']));
        $twig->addFunction(new TwigFunction('asset', [$assistantUrl, 'getAbsoluteUrl']));
        $connexionSession = $conteneur->get(ConnexionUtilisateurSession::class);
        $twig->addGlobal('connexionUtilisateur', $connexionSession);
        $twig->addGlobal('messagesFlash', new MessageFlash());

        $utilisateurConnecte = null;
        if ($connexionSession->estConnecte()) {
            $utilisateurService = $conteneur->get('App\Gigamed\Service\UtilisateurServiceInterface');
            $utilisateurConnecte = $utilisateurService->recupererParEmail($connexionSession->getLoginUtilisateurConnecte());
        }
        $twig->addGlobal('utilisateurConnecte', $utilisateurConnecte);

        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $reponse = $controleur(...$arguments);
        } catch (MethodNotAllowedException $exception) {
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage(), 405);
        } catch (ResourceNotFoundException $exception) {
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage());
        }
        return $reponse;
    }
}