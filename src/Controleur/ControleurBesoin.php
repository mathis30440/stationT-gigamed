<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\Exception\ServiceException;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurBesoin extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private BesoinServiceInterface $besoinService,
        private CandidatureServiceInterface $candidatureService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    #[Route(path: '/besoin/soumettre', name: 'afficherFormulaireBesoin', methods: ['GET'])]
    public function afficherFormulaireBesoin(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::ENTREPRISE, RoleUtilisateur::PARTENAIRE);
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        if ($utilisateur->getRoleUtilisateur() !== RoleUtilisateur::PARTENAIRE
            && $this->candidatureService->existeParIdUtilisateur($utilisateur->getIdUtilisateur())) {
            MessageFlash::ajouter("danger", "Vous avez déjà déposé une candidature. Vous ne pouvez pas soumettre un besoin.");
            return $this->rediriger("monEspace");
        }

        $idBesoin  = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $brouillon = $idBesoin !== null
            ? $this->besoinService->recupererParId($idBesoin)
            : null;

        
        if ($brouillon !== null && ($brouillon->getIdUtilisateur() !== $utilisateur->getIdUtilisateur() || $brouillon->getStatutBesoin()->value !== 'brouillon')) {
            $brouillon = null;
        }

        return $this->afficherTwig('besoin/formulaire.html.twig', [
            'utilisateur' => $utilisateur,
            'brouillon'   => $brouillon,
        ]);
    }

    #[Route(path: '/besoin/soumettre', name: 'soumettresBesoin', methods: ['POST'])]
    public function soumettresBesoin(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::ENTREPRISE, RoleUtilisateur::PARTENAIRE);
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $idBesoin    = isset($_POST['idBesoin']) && $_POST['idBesoin'] !== '' ? (int) $_POST['idBesoin'] : null;

        $donnees = [
            'titreBesoin'          => $_POST['titreBesoin'] ?? null,
            'filiere'              => $_POST['filiere'] ?? null,
            'objectifBesoin'       => $_POST['objectifBesoin'] ?? null,
            'publicVise'           => $_POST['publicVise'] ?? null,
            'perimetrePrioritaire' => $_POST['perimetrePrioritaire'] ?? null,
            'vision'               => $_POST['vision'] ?? null,
            'enjeuxMajeurs'        => $_POST['enjeuxMajeurs'] ?? null,
            'besoinsPrioritaires'  => $_POST['besoinsPrioritaires'] ?? null,
            'exemplesInnovations'  => $_POST['exemplesInnovations'] ?? null,
            'partenaires'          => $_POST['partenaires'] ?? null,
        ];

        $action = $_POST['action'] ?? 'soumettre';

        if ($action === 'brouillon') {
            try {
                $this->besoinService->sauvegarderBrouillon($donnees, $utilisateur->getIdUtilisateur(), $idBesoin);
            } catch (ServiceException $e) {
                MessageFlash::ajouter("danger", $e->getMessage());
                return $this->afficherTwig('besoin/formulaire.html.twig', [
                    'utilisateur' => $utilisateur,
                    'brouillon'   => null,
                    'ancien'      => $donnees,
                ]);
            }
            MessageFlash::ajouter("success", "Brouillon sauvegardé. Vous pouvez le compléter et le soumettre plus tard.");
            return $this->rediriger("monEspace");
        }

        try {
            $this->besoinService->soumettre($donnees, $utilisateur->getIdUtilisateur(), $idBesoin);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->afficherTwig('besoin/formulaire.html.twig', [
                'utilisateur' => $utilisateur,
                'brouillon'   => null,
                'ancien'      => $donnees,
            ]);
        }

        MessageFlash::ajouter("success", "Votre besoin a été soumis avec succès. Notre équipe l'examinera prochainement.");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/besoin/{id}/supprimer', name: 'supprimerBrouillonBesoin', methods: ['POST'])]
    public function supprimerBrouillon(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::ENTREPRISE, RoleUtilisateur::PARTENAIRE, RoleUtilisateur::STARTUP);
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $besoin = $this->besoinService->recupererParId($id);

        if ($besoin === null || $besoin->getIdUtilisateur() !== $utilisateur->getIdUtilisateur() || $besoin->getStatutBesoin()->value !== 'brouillon') {
            MessageFlash::ajouter("danger", "Ce brouillon n'existe pas ou ne peut pas être supprimé.");
            return $this->rediriger("monEspace");
        }

        $this->besoinService->supprimer($id);
        MessageFlash::ajouter("success", "Brouillon supprimé.");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/besoin/{id}', name: 'detailBesoin', methods: ['GET'])]
    public function detailBesoin(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::ENTREPRISE, RoleUtilisateur::PARTENAIRE);
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $besoin = $this->besoinService->recupererParId($id);

        if ($besoin === null || $besoin->getIdUtilisateur() !== $utilisateur->getIdUtilisateur()) {
            MessageFlash::ajouter("danger", "Ce besoin n'existe pas ou ne vous appartient pas.");
            return $this->rediriger("monEspace");
        }

        return $this->afficherTwig('besoin/detail.html.twig', [
            'utilisateur' => $utilisateur,
            'besoin'      => $besoin,
        ]);
    }
}