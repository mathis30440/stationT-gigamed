<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\AmiServiceInterface;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurAmi extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private AmiServiceInterface $amiService,
        private BesoinServiceInterface $besoinService,
        private CandidatureServiceInterface $candidatureService,
        private AffecterServiceInterface $affecterService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    #[Route(path: '/ami', name: 'listeAmi', methods: ['GET'])]
    public function listeAmi(): Response
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

        $amisCandidates = [];
        $hasBesoin      = false;

        if ($peutCandidater) {
            foreach ($this->candidatureService->recupererParIdUtilisateur($idUtilisateur) as $c) {
                if ($c->getIdAmi() !== null && $c->getStatutCandidature()->value !== 'brouillon') {
                    $amisCandidates[] = $c->getIdAmi();
                }
            }
            if ($role === RoleUtilisateur::ENTREPRISE) {
                $hasBesoin = $this->besoinService->existeParIdUtilisateur($idUtilisateur);
            }
        }

        return $this->afficherTwig('ami/liste.html.twig', [
            'amis'           => $this->amiService->recupererAmisOuverts(),
            'utilisateur'    => $utilisateur,
            'role'           => $role->value,
            'peutCandidater' => $peutCandidater,
            'hasBesoin'      => $hasBesoin,
            'amisCandidates' => $amisCandidates,
        ]);
    }

    #[Route(path: '/ami/{id}', name: 'detailAmi', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailAmi(int $id): Response
    {
        $erreur = $this->verifierConnecte();
        if ($erreur !== null) return $erreur;

        $ami = $this->amiService->recupererParId($id);
        if ($ami === null || $ami->getStatutAmi()->value !== 'ouvert') {
            MessageFlash::ajouter('danger', "Cet AMI n'existe pas ou n'est plus ouvert.");
            return $this->rediriger('listeAmi');
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
            $dejaCandidate = $this->candidatureService->existeParUtilisateurEtAmi($idUtilisateur, $id);
            if ($role === RoleUtilisateur::ENTREPRISE) {
                $hasBesoin = $this->besoinService->existeParIdUtilisateur($idUtilisateur);
            }
        }

        $toutesLesCandidatures = $this->candidatureService->recupererParIdAmi($id);
        $rolesAdmin = [RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN];
        if (in_array($role, $rolesAdmin, true)) {
            $candidatures = $toutesLesCandidatures;
        } elseif ($role === RoleUtilisateur::PARTENAIRE) {
            $idsAssignes  = $this->affecterService->recupererIdCandidaturesParIdUtilisateur($idUtilisateur);
            $candidatures = array_values(array_filter($toutesLesCandidatures, fn($c) => in_array($c->getIdCandidature(), $idsAssignes)));
        } else {
            $candidatures = array_values(array_filter($toutesLesCandidatures, fn($c) => $c->getIdUtilisateur() === $idUtilisateur));
        }

        return $this->afficherTwig('ami/detail.html.twig', [
            'ami'            => $ami,
            'utilisateur'    => $utilisateur,
            'role'           => $role->value,
            'peutCandidater' => $peutCandidater,
            'hasBesoin'      => $hasBesoin,
            'dejaCandidate'  => $dejaCandidate,
            'candidatures'   => $candidatures,
        ]);
    }
}