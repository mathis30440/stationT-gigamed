<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Modele\DataObject\Utilisateur;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class ControleurConnecte extends ControleurGenerique
{
    public function __construct(
        ContainerInterface $container,
        protected ConnexionUtilisateurInterface $connexionUtilisateur,
        protected UtilisateurServiceInterface $utilisateurService,
    ) {
        parent::__construct($container);
    }

    protected function getUtilisateurConnecte(): ?Utilisateur
    {
        $email = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
        if ($email === null) return null;
        return $this->utilisateurService->recupererParEmail($email);
    }

    protected function verifierConnecte(): ?Response
    {
        if ($this->getUtilisateurConnecte() === null) {
            MessageFlash::ajouter("danger", "Vous devez être connecté pour accéder à cette page.");
            return $this->rediriger("afficherFormulaireConnexion");
        }
        return null;
    }

    protected function verifierAcces(RoleUtilisateur ...$rolesAutorises): ?Response
    {
        $utilisateur = $this->getUtilisateurConnecte();
        if ($utilisateur === null) {
            MessageFlash::ajouter("danger", "Vous devez être connecté pour accéder à cette page.");
            return $this->rediriger("afficherFormulaireConnexion");
        }
        foreach ($rolesAutorises as $role) {
            if ($utilisateur->getRoleUtilisateur() === $role) return null;
        }
        
        if (in_array($utilisateur->getRoleUtilisateur(), [
            RoleUtilisateur::SUPER_ADMIN,
            RoleUtilisateur::ADMIN,
        ], true)) {
            return $this->rediriger("adminDashboard");
        }
        
        if ($utilisateur->getRoleUtilisateur() === RoleUtilisateur::PARTENAIRE) {
            return $this->rediriger("monEspace");
        }
        MessageFlash::ajouter("danger", "Vous n'avez pas accès à cette page.");
        return $this->rediriger("accueil");
    }
}