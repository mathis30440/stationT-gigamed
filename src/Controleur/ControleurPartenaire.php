<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\AmiServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\ChallengeServiceInterface;
use App\Gigamed\Service\DocumentServiceInterface;
use App\Gigamed\Service\NotationServiceInterface;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurPartenaire extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private AffecterServiceInterface $affecterService,
        private CandidatureServiceInterface $candidatureService,
        private AmiServiceInterface $amiService,
        private ChallengeServiceInterface $challengeService,
        private NotationServiceInterface $notationService,
        private DocumentServiceInterface $documentService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    private function verifierPartenaire(): ?Response
    {
        return $this->verifierAcces(RoleUtilisateur::PARTENAIRE);
    }

    

    #[Route(path: '/partenaire/candidature/{id}', name: 'partenaireDetailCandidature', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailCandidature(int $id): Response
    {
        $erreur = $this->verifierPartenaire();
        if ($erreur !== null) return $erreur;

        $partenaire   = $this->getUtilisateurConnecte();
        $idPartenaire = $partenaire->getIdUtilisateur();

        if (!$this->affecterService->estAffecte($idPartenaire, $id)) {
            MessageFlash::ajouter('danger', "Vous n'êtes pas affecté à cette candidature.");
            return $this->rediriger('monEspace');
        }

        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null) {
            MessageFlash::ajouter('danger', "Candidature introuvable.");
            return $this->rediriger('monEspace');
        }

        $ami       = $candidature->getIdAmi() !== null ? $this->amiService->recupererParId($candidature->getIdAmi()) : null;
        $challenge = $candidature->getIdChallenge() !== null ? $this->challengeService->recupererParId($candidature->getIdChallenge()) : null;

        $besoinsPrioritairesDecodes = [];
        if ($candidature->getBesoinsPrioritaires()) {
            $besoinsPrioritairesDecodes = json_decode($candidature->getBesoinsPrioritaires(), true) ?? [];
        }

        $soumetteur = $candidature->getIdUtilisateur() !== null
            ? $this->utilisateurService->recupererUtilisateurOuNullParId($candidature->getIdUtilisateur())
            : null;

        $notes = $this->notationService->recupererNotes($idPartenaire, $candidature->getIdCandidature());

        return $this->afficherTwig('candidature/detail.html.twig', [
            'utilisateur'               => $partenaire,
            'candidature'               => $candidature,
            'ami'                       => $ami,
            'challenge'                 => $challenge,
            'soumetteur'                => $soumetteur,
            'statuts'                   => [],
            'besoinsPrioritairesDecodes' => $besoinsPrioritairesDecodes,
            'documents'                 => $this->documentService->recupererParIdCandidature($candidature->getIdCandidature()),
            'isAdmin'                   => true,
            'isPartenaire'              => true,
            'partenairesAffecter'       => [],
            'tousPartenaires'           => [],
            'idsPartenairesAffecter'    => [],
            'aDejaNote'                 => count($notes) > 0,
        ]);
    }

    

    #[Route(path: '/partenaire/candidature/{id}/noter', name: 'partenaireAfficherFormulaireNoter', methods: ['GET'])]
    public function afficherFormulaireNoter(int $id): Response
    {
        $erreur = $this->verifierPartenaire();
        if ($erreur !== null) return $erreur;

        $partenaire   = $this->getUtilisateurConnecte();
        $idPartenaire = $partenaire->getIdUtilisateur();

        if (!$this->affecterService->estAffecte($idPartenaire, $id)) {
            MessageFlash::ajouter('danger', "Vous n'êtes pas affecté à cette candidature.");
            return $this->rediriger('monEspace');
        }

        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null) {
            MessageFlash::ajouter('danger', "Candidature introuvable.");
            return $this->rediriger('monEspace');
        }

        if ($candidature->getStatutCandidature() !== \App\Gigamed\Modele\DataObject\StatutCandidature::ELIGIBLE) {
            MessageFlash::ajouter('danger', "La notation n'est possible que pour les candidatures éligibles.");
            return $this->rediriger('monEspace');
        }

        $ami       = $candidature->getIdAmi() !== null ? $this->amiService->recupererParId($candidature->getIdAmi()) : null;
        $challenge = $candidature->getIdChallenge() !== null ? $this->challengeService->recupererParId($candidature->getIdChallenge()) : null;

        $criteres = $this->notationService->recupererCriteres(
            $ami?->getIdAmi(),
            $challenge?->getIdChallenge()
        );

        $notes = $this->notationService->recupererNotes($idPartenaire, $id);

        return $this->afficherTwig('candidature/noter.html.twig', [
            'utilisateur' => $partenaire,
            'candidature' => $candidature,
            'ami'         => $ami,
            'challenge'   => $challenge,
            'criteres'    => $criteres,
            'notes'       => $notes,
        ]);
    }

    #[Route(path: '/partenaire/candidature/{id}/noter', name: 'partenaireNoterCandidature', methods: ['POST'])]
    public function noterCandidature(int $id): Response
    {
        $erreur = $this->verifierPartenaire();
        if ($erreur !== null) return $erreur;

        $partenaire   = $this->getUtilisateurConnecte();
        $idPartenaire = $partenaire->getIdUtilisateur();

        if (!$this->affecterService->estAffecte($idPartenaire, $id)) {
            MessageFlash::ajouter('danger', "Vous n'êtes pas affecté à cette candidature.");
            return $this->rediriger('monEspace');
        }

        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null || $candidature->getStatutCandidature() !== \App\Gigamed\Modele\DataObject\StatutCandidature::ELIGIBLE) {
            MessageFlash::ajouter('danger', "La notation n'est possible que pour les candidatures éligibles.");
            return $this->rediriger('monEspace');
        }

        $this->notationService->sauvegarder($idPartenaire, $id, $_POST['note'] ?? [], $_POST['commentaire'] ?? []);

        MessageFlash::ajouter('success', "Vos notes ont été enregistrées.");
        return $this->rediriger('partenaireDetailCandidature', ['id' => $id]);
    }
}