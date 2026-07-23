<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\ChallengeServiceInterface;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurChallenge extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private ChallengeServiceInterface $challengeService,
        private BesoinServiceInterface $besoinService,
        private CandidatureServiceInterface $candidatureService,
        private AffecterServiceInterface $affecterService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    #[Route(path: '/challenges', name: 'listeChallenges', methods: ['GET'])]
    public function listeChallenges(): Response
    {
        $erreur = $this->verifierConnecte();
        if ($erreur !== null) return $erreur;

        $utilisateur   = $this->getUtilisateurConnecte();
        $role          = $utilisateur->getRoleUtilisateur();
        $idUtilisateur = $utilisateur->getIdUtilisateur();

        $rolesPouvantCandidater = [
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::PORTEUR_DE_PROJET,
            RoleUtilisateur::ENTREPRISE,
        ];
        $peutCandidater = in_array($role, $rolesPouvantCandidater, true);

        $challengesCandidates = [];
        $hasBesoin            = false;

        if ($peutCandidater) {
            foreach ($this->candidatureService->recupererParIdUtilisateur($idUtilisateur) as $c) {
                if ($c->getIdChallenge() !== null && $c->getStatutCandidature()->value !== 'brouillon') {
                    $challengesCandidates[] = $c->getIdChallenge();
                }
            }
            if ($role === RoleUtilisateur::ENTREPRISE) {
                $hasBesoin = $this->besoinService->existeParIdUtilisateur($idUtilisateur);
            }
        }

        return $this->afficherTwig('challenge/liste.html.twig', [
            'challenges'           => $this->challengeService->recupererChallengesOuverts(),
            'utilisateur'          => $utilisateur,
            'role'                 => $role->value,
            'peutCandidater'       => $peutCandidater,
            'hasBesoin'            => $hasBesoin,
            'challengesCandidates' => $challengesCandidates,
        ]);
    }

    #[Route(path: '/challenges/{id}', name: 'detailChallenge', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailChallenge(int $id): Response
    {
        $erreur = $this->verifierConnecte();
        if ($erreur !== null) return $erreur;

        $challenge = $this->challengeService->recupererParId($id);
        if ($challenge === null || $challenge->getStatutChallenge()->value !== 'ouvert') {
            MessageFlash::ajouter('danger', "Ce challenge n'existe pas ou n'est plus ouvert.");
            return $this->rediriger('listeChallenges');
        }

        $utilisateur   = $this->getUtilisateurConnecte();
        $role          = $utilisateur->getRoleUtilisateur();
        $idUtilisateur = $utilisateur->getIdUtilisateur();

        $rolesPouvantCandidater = [
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::PORTEUR_DE_PROJET,
            RoleUtilisateur::ENTREPRISE,
        ];
        $peutCandidater = in_array($role, $rolesPouvantCandidater, true);

        $dejaCandidate = false;
        $hasBesoin     = false;

        if ($peutCandidater) {
            $dejaCandidate = $this->candidatureService->existeParUtilisateurEtChallenge($idUtilisateur, $id);
            if ($role === RoleUtilisateur::ENTREPRISE) {
                $hasBesoin = $this->besoinService->existeParIdUtilisateur($idUtilisateur);
            }
        }

        $toutesLesCandidatures = $this->candidatureService->recupererParIdChallenge($id);
        $rolesAdmin = [RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN];
        if (in_array($role, $rolesAdmin, true)) {
            $candidatures = $toutesLesCandidatures;
        } elseif ($role === RoleUtilisateur::PARTENAIRE) {
            $idsAssignes  = $this->affecterService->recupererIdCandidaturesParIdUtilisateur($idUtilisateur);
            $candidatures = array_values(array_filter($toutesLesCandidatures, fn($c) => in_array($c->getIdCandidature(), $idsAssignes)));
        } else {
            $candidatures = array_values(array_filter($toutesLesCandidatures, fn($c) => $c->getIdUtilisateur() === $idUtilisateur));
        }

        return $this->afficherTwig('challenge/detail.html.twig', [
            'challenge'            => $challenge,
            'utilisateur'          => $utilisateur,
            'role'                 => $role->value,
            'peutCandidater'       => $peutCandidater,
            'hasBesoin'            => $hasBesoin,
            'dejaCandidate'        => $dejaCandidate,
            'candidatures'         => $candidatures,
        ]);
    }
}