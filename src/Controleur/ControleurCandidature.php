<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\AmiServiceInterface;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\ChallengeServiceInterface;
use App\Gigamed\Service\DocumentServiceInterface;
use App\Gigamed\Service\Exception\ServiceException;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurCandidature extends ControleurConnecte
{
    private const UPLOAD_DIR = __DIR__ . '/../../ressources/uploads/candidatures';

    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private CandidatureServiceInterface $candidatureService,
        private AmiServiceInterface $amiService,
        private ChallengeServiceInterface $challengeService,
        private DocumentServiceInterface $documentService,
        private BesoinServiceInterface $besoinService,
        private AffecterServiceInterface $affecterService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    #[Route(path: '/candidature/ami/{idAmi}', name: 'afficherFormulaireCandidatureAmi', methods: ['GET'])]
    public function afficherFormulaireCandidatureAmi(int $idAmi): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        if ($this->besoinService->existeParIdUtilisateur($utilisateur->getIdUtilisateur())) {
            MessageFlash::ajouter("danger", "Vous avez soumis un besoin. Vous ne pouvez pas déposer une candidature.");
            return $this->rediriger("monEspace");
        }

        $ami = $this->amiService->recupererParId($idAmi);
        if ($ami === null) {
            MessageFlash::ajouter("danger", "Cet AMI n'existe pas.");
            return $this->rediriger("listeAmi");
        }

        if ($this->candidatureService->existeParUtilisateurEtAmi($utilisateur->getIdUtilisateur(), $idAmi)) {
            MessageFlash::ajouter("warning", "Vous avez déjà soumis une candidature pour cet AMI.");
            return $this->rediriger("listeAmi");
        }

        $now = new \DateTime();
        if ($ami->getStatutAmi()->value !== 'ouvert' || $now < $ami->getDateOuverture() || $now > $ami->getDateCloture()) {
            MessageFlash::ajouter("danger", "Cet AMI n'est pas ouvert aux candidatures en ce moment.");
            return $this->rediriger("listeAmi");
        }

        $brouillon = $this->candidatureService->recupererBrouillonParUtilisateurEtAmi($utilisateur->getIdUtilisateur(), $idAmi);

        $docsExistants = [];
        if ($brouillon !== null) {
            foreach ($this->documentService->recupererParIdCandidature($brouillon->getIdCandidature()) as $doc) {
                $docsExistants[$doc->getNomDocument()] = $doc;
            }
        }

        return $this->afficherTwig('candidature/formulaire.html.twig', [
            'utilisateur'                 => $utilisateur,
            'ami'                         => $ami,
            'challenge'                   => null,
            'brouillon'                   => $brouillon,
            'docsExistants'               => $docsExistants,
            'brouillonBesoinsPrioritaires'=> $brouillon ? (json_decode($brouillon->getBesoinsPrioritaires() ?? '', true) ?? []) : [],
            'brouillonAppuisStationT'     => $brouillon && $brouillon->getAppuisStationT() ? array_map('trim', explode(',', $brouillon->getAppuisStationT())) : [],
        ]);
    }

    #[Route(path: '/candidature/ami/{idAmi}', name: 'deposerCandidatureAmi', methods: ['POST'])]
    public function deposerCandidatureAmi(int $idAmi): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $ami = $this->amiService->recupererParId($idAmi);
        if ($ami === null) {
            MessageFlash::ajouter("danger", "Cet AMI n'existe pas.");
            return $this->rediriger("listeAmi");
        }

        $donnees = $this->extraireDonnees();
        $action  = $_POST['action'] ?? 'deposer';

        if ($action === 'brouillon') {
            try {
                $this->candidatureService->sauvegarderBrouillon($donnees, $utilisateur->getIdUtilisateur(), $idAmi, null);
                $brouillon = $this->candidatureService->recupererBrouillonParUtilisateurEtAmi($utilisateur->getIdUtilisateur(), $idAmi);
                if ($brouillon !== null) {
                    $this->documentService->sauvegarderFichiers($brouillon->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
                }
            } catch (ServiceException $e) {
                MessageFlash::ajouter("danger", $e->getMessage());
                return $this->afficherTwig('candidature/formulaire.html.twig', [
                    'utilisateur'  => $utilisateur,
                    'ami'          => $ami,
                    'challenge'    => null,
                    'brouillon'    => null,
                    'docsExistants'=> [],
                    'ancien'       => $donnees,
                ]);
            }
            MessageFlash::ajouter("success", "Brouillon sauvegardé. Vous pouvez le compléter et le soumettre plus tard.");
            return $this->rediriger("monEspace");
        }

        try {
            $candidature = $this->candidatureService->deposer($donnees, $utilisateur->getIdUtilisateur(), $idAmi, null);
            $this->documentService->sauvegarderFichiers($candidature->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->afficherTwig('candidature/formulaire.html.twig', [
                'utilisateur'  => $utilisateur,
                'ami'          => $ami,
                'challenge'    => null,
                'brouillon'    => null,
                'docsExistants'=> [],
                'ancien'       => $donnees,
            ]);
        }

        MessageFlash::ajouter("success", "Votre candidature a été déposée avec succès !");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/candidature/challenge/{idChallenge}', name: 'afficherFormulaireCandidatureChallenge', methods: ['GET'])]
    public function afficherFormulaireCandidatureChallenge(int $idChallenge): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        if ($this->besoinService->existeParIdUtilisateur($utilisateur->getIdUtilisateur())) {
            MessageFlash::ajouter("danger", "Vous avez soumis un besoin. Vous ne pouvez pas déposer une candidature.");
            return $this->rediriger("monEspace");
        }

        $challenge = $this->challengeService->recupererParId($idChallenge);
        if ($challenge === null) {
            MessageFlash::ajouter("danger", "Ce challenge n'existe pas.");
            return $this->rediriger("listeChallenges");
        }

        if ($this->candidatureService->existeParUtilisateurEtChallenge($utilisateur->getIdUtilisateur(), $idChallenge)) {
            MessageFlash::ajouter("warning", "Vous avez déjà soumis une candidature pour ce challenge.");
            return $this->rediriger("listeChallenges");
        }

        if ($challenge->getStatutChallenge()->value !== 'ouvert') {
            MessageFlash::ajouter("danger", "Ce challenge n'est pas ouvert aux candidatures.");
            return $this->rediriger("listeChallenges");
        }

        $brouillon = $this->candidatureService->recupererBrouillonParUtilisateurEtChallenge($utilisateur->getIdUtilisateur(), $idChallenge);

        $docsExistants = [];
        if ($brouillon !== null) {
            foreach ($this->documentService->recupererParIdCandidature($brouillon->getIdCandidature()) as $doc) {
                $docsExistants[$doc->getNomDocument()] = $doc;
            }
        }

        return $this->afficherTwig('candidature/formulaire.html.twig', [
            'utilisateur'                 => $utilisateur,
            'ami'                         => null,
            'challenge'                   => $challenge,
            'brouillon'                   => $brouillon,
            'docsExistants'               => $docsExistants,
            'brouillonBesoinsPrioritaires'=> $brouillon ? (json_decode($brouillon->getBesoinsPrioritaires() ?? '', true) ?? []) : [],
            'brouillonAppuisStationT'     => $brouillon && $brouillon->getAppuisStationT() ? array_map('trim', explode(',', $brouillon->getAppuisStationT())) : [],
        ]);
    }

    #[Route(path: '/candidature/challenge/{idChallenge}', name: 'deposerCandidatureChallenge', methods: ['POST'])]
    public function deposerCandidatureChallenge(int $idChallenge): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $challenge = $this->challengeService->recupererParId($idChallenge);
        if ($challenge === null) {
            MessageFlash::ajouter("danger", "Ce challenge n'existe pas.");
            return $this->rediriger("listeChallenges");
        }

        $donnees = $this->extraireDonnees();
        $action  = $_POST['action'] ?? 'deposer';

        if ($action === 'brouillon') {
            try {
                $this->candidatureService->sauvegarderBrouillon($donnees, $utilisateur->getIdUtilisateur(), null, $idChallenge);
                $brouillon = $this->candidatureService->recupererBrouillonParUtilisateurEtChallenge($utilisateur->getIdUtilisateur(), $idChallenge);
                if ($brouillon !== null) {
                    $this->documentService->sauvegarderFichiers($brouillon->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
                }
            } catch (ServiceException $e) {
                MessageFlash::ajouter("danger", $e->getMessage());
                return $this->afficherTwig('candidature/formulaire.html.twig', [
                    'utilisateur'  => $utilisateur,
                    'ami'          => null,
                    'challenge'    => $challenge,
                    'brouillon'    => null,
                    'docsExistants'=> [],
                    'ancien'       => $donnees,
                ]);
            }
            MessageFlash::ajouter("success", "Brouillon sauvegardé. Vous pouvez le compléter et le soumettre plus tard.");
            return $this->rediriger("monEspace");
        }

        try {
            $candidature = $this->candidatureService->deposer($donnees, $utilisateur->getIdUtilisateur(), null, $idChallenge);
            $this->documentService->sauvegarderFichiers($candidature->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->afficherTwig('candidature/formulaire.html.twig', [
                'utilisateur'  => $utilisateur,
                'ami'          => null,
                'challenge'    => $challenge,
                'brouillon'    => null,
                'docsExistants'=> [],
                'ancien'       => $donnees,
            ]);
        }

        MessageFlash::ajouter("success", "Votre candidature a été déposée avec succès !");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/candidature/{id}/supprimer', name: 'supprimerBrouillonCandidature', methods: ['POST'])]
    public function supprimerBrouillon(int $id): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $candidature = $this->candidatureService->recupererParId($id);

        if ($candidature === null || $candidature->getIdUtilisateur() !== $utilisateur->getIdUtilisateur() || $candidature->getStatutCandidature()->value !== 'brouillon') {
            MessageFlash::ajouter("danger", "Ce brouillon n'existe pas ou ne peut pas être supprimé.");
            return $this->rediriger("monEspace");
        }

        $this->candidatureService->supprimer($id);
        MessageFlash::ajouter("success", "Brouillon supprimé.");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/document/{idDoc}/supprimer', name: 'supprimerDocument', methods: ['POST'])]
    public function supprimerDocument(int $idDoc): Response
    {
        $erreur = $this->verifierConnecte();
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $doc = $this->documentService->recupererParId($idDoc);

        if ($doc === null) {
            MessageFlash::ajouter("danger", "Document introuvable.");
            return $this->rediriger("monEspace");
        }

        $candidature = $this->candidatureService->recupererParId($doc->getIdCandidature());
        $rolesAdmin  = [RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN];
        $estAdmin    = in_array($utilisateur->getRoleUtilisateur(), $rolesAdmin, true);

        if ($candidature === null || (!$estAdmin && $candidature->getIdUtilisateur() !== $utilisateur->getIdUtilisateur())) {
            MessageFlash::ajouter("danger", "Action non autorisée.");
            return $this->rediriger("monEspace");
        }

        $this->documentService->supprimer($idDoc, self::UPLOAD_DIR);

        return new \Symfony\Component\HttpFoundation\JsonResponse(['ok' => true]);
    }

    #[Route(path: '/candidature/{id}', name: 'detailCandidature', methods: ['GET'])]
    public function detailCandidature(int $id): Response
    {
        $erreur = $this->verifierConnecte();
        if ($erreur !== null) return $erreur;

        $utilisateur     = $this->getUtilisateurConnecte();
        $role            = $utilisateur->getRoleUtilisateur();
        $idUtilisateur   = $utilisateur->getIdUtilisateur();
        $candidature     = $this->candidatureService->recupererParId($id);

        if ($candidature === null) {
            MessageFlash::ajouter("danger", "Cette candidature n'existe pas.");
            return $this->rediriger("monEspace");
        }

        $estProprietaire = $candidature->getIdUtilisateur() === $idUtilisateur;

        $rolesAdmin = [RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN];
        if (in_array($role, $rolesAdmin, true)) {
        } elseif ($role === RoleUtilisateur::PARTENAIRE) {
            if (!$this->affecterService->estAffecte($idUtilisateur, $id)) {
                MessageFlash::ajouter('danger', "Vous n'avez pas accès à cette candidature.");
                return $this->rediriger('monEspace');
            }
        } elseif (!$estProprietaire) {
            MessageFlash::ajouter('danger', "Vous n'avez pas accès à cette candidature.");
            return $this->rediriger('monEspace');
        }

        $ami = $candidature->getIdAmi() !== null
            ? $this->amiService->recupererParId($candidature->getIdAmi())
            : null;
        $challenge = $candidature->getIdChallenge() !== null
            ? $this->challengeService->recupererParId($candidature->getIdChallenge())
            : null;

        $besoinsPrioritairesDecodes = [];
        if ($candidature->getBesoinsPrioritaires()) {
            $besoinsPrioritairesDecodes = json_decode($candidature->getBesoinsPrioritaires(), true) ?? [];
        }

        $soumetteur = $this->utilisateurService->recupererUtilisateurOuNullParId($candidature->getIdUtilisateur());

        return $this->afficherTwig('candidature/detail.html.twig', [
            'utilisateur'               => $utilisateur,
            'candidature'               => $candidature,
            'ami'                       => $ami,
            'challenge'                 => $challenge,
            'soumetteur'                => $soumetteur,
            'documents'                 => $this->documentService->recupererParIdCandidature($candidature->getIdCandidature()),
            'besoinsPrioritairesDecodes' => $besoinsPrioritairesDecodes,
            'estProprietaire'           => $estProprietaire,
        ]);
    }

    #[Route(path: '/candidature/{id}/complements', name: 'ajouterComplementsCandidature', methods: ['POST'])]
    public function ajouterComplements(int $id): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $candidature = $this->candidatureService->recupererParId($id);

        if ($candidature === null || $candidature->getIdUtilisateur() !== $utilisateur->getIdUtilisateur()) {
            MessageFlash::ajouter("danger", "Cette candidature n'existe pas ou ne vous appartient pas.");
            return $this->rediriger("monEspace");
        }

        if ($candidature->getStatutCandidature()->value !== 'incomplet') {
            MessageFlash::ajouter("danger", "Vous ne pouvez transmettre des compléments que pour un dossier marqué incomplet.");
            return $this->rediriger("detailCandidature", ['id' => $id]);
        }

        $fichiers = [];
        foreach ($_FILES as $nom => $fichier) {
            if (str_starts_with($nom, 'doc_complement_') && $fichier['error'] !== UPLOAD_ERR_NO_FILE) {
                $fichiers[$nom] = $fichier;
            }
        }

        if (empty($fichiers)) {
            MessageFlash::ajouter("danger", "Veuillez sélectionner au moins un fichier.");
            return $this->rediriger("detailCandidature", ['id' => $id]);
        }

        try {
            $this->documentService->sauvegarderFichiers($candidature->getIdCandidature(), $fichiers, self::UPLOAD_DIR);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->rediriger("detailCandidature", ['id' => $id]);
        }

        MessageFlash::ajouter("success", "Votre document a bien été transmis. L'équipe Station T va l'examiner.");
        return $this->rediriger("detailCandidature", ['id' => $id]);
    }

    private function extraireDonnees(): array
    {
        return [
            'nomProjet'                 => $_POST['nomProjet'] ?? null,
            'entreeStationT'            => $_POST['entreeStationT'] ?? null,
            'filiereCandidat'           => $_POST['filiereCandidat'] ?? null,
            'besoinTraite'              => $_POST['besoinTraite'] ?? null,
            'descriptionProjet'         => $_POST['descriptionProjet'] ?? null,
            'valeurAjoutee'             => $_POST['valeurAjoutee'] ?? null,
            'innovationDifferentiation' => $_POST['innovationDifferentiation'] ?? null,
            'maturite'                  => $_POST['maturite'] ?? null,
            'references'                => $_POST['references'] ?? null,
            'objectifPilote'            => $_POST['objectifPilote'] ?? null,
            'perimetreGeographique'     => $_POST['perimetreGeographique'] ?? null,
            'dureeExperimentation'      => $_POST['dureeExperimentation'] ?? null,
            'publicsCibles'             => $_POST['publicsCibles'] ?? null,
            'budgetMobiliser'           => $_POST['budgetMobiliser'] ?? null,
            'moyensMobiliser'           => $_POST['moyensMobiliser'] ?? null,
            'conditionsReussite'        => $_POST['conditionsReussite'] ?? null,
            'derouteOperationnel'       => $_POST['derouteOperationnel'] ?? null,
            'partenairesRecherches'     => $_POST['partenairesRecherches'] ?? null,
            'appuisStationT'            => $_POST['appuisStationT'] ?? null,
            'modelEconomique'           => $_POST['modelEconomique'] ?? null,
            'conditionsDeploiement'     => $_POST['conditionsDeploiement'] ?? null,
            'impactAttendu'             => $_POST['impactAttendu'] ?? null,
            'conformite'                => $_POST['conformite'] ?? null,
            'mesuresSecurisation'       => $_POST['mesuresSecurisation'] ?? null,
            
            'eng_certifie'              => $_POST['eng_certifie'] ?? null,
            'eng_suivi'                 => $_POST['eng_suivi'] ?? null,
            'eng_instruction'           => $_POST['eng_instruction'] ?? null,
            'eng_reorientation'         => $_POST['eng_reorientation'] ?? null,
            'eng_changement'            => $_POST['eng_changement'] ?? null,
            'eng_habilite'              => $_POST['eng_habilite'] ?? null,
            'eng_financier'             => $_POST['eng_financier'] ?? null,
            'eng_cadrer'                => $_POST['eng_cadrer'] ?? null,
            'eng_justificatifs'         => $_POST['eng_justificatifs'] ?? null,
            'pitchCourt'                => $_POST['pitchCourt'] ?? null,
            'problemeIdentifie'         => $_POST['problemeIdentifie'] ?? null,
            'concurrence'               => $_POST['concurrence'] ?? null,
            'clientsCibles'             => $_POST['clientsCibles'] ?? null,
            'preuvesBesoin'             => $_POST['preuvesBesoin'] ?? null,
            'etatAvancement'            => $_POST['etatAvancement'] ?? null,
            'besoinsFinanciers'         => $_POST['besoinsFinanciers'] ?? null,
            'equipe'                    => $_POST['equipe'] ?? null,
            'forcesEquipe'              => $_POST['forcesEquipe'] ?? null,
            'programmeGigamed'          => $_POST['programmeGigamed'] ?? null,
            'niveauAccompagnement'      => $_POST['niveauAccompagnement'] ?? null,
            'besoinsPrioritaires'       => $_POST['besoinsPrioritaires'] ?? [],
            'objectifAccompagnement'    => $_POST['objectifAccompagnement'] ?? null,
        ];
    }

    

    private function extraireFichiers(): array
    {
        $champs = [
            'doc_cv', 'doc_pitch', 'doc_business_plan', 'doc_previsionnel',
            'doc_immatriculation', 'doc_preuves', 'doc_autre',
            'doc_presentation', 'doc_kbis', 'doc_assurance',
            'doc_references', 'doc_technique', 'doc_financier', 'doc_rgpd',
        ];

        $fichiers = [];
        foreach ($champs as $champ) {
            if (isset($_FILES[$champ]) && $_FILES[$champ]['error'] !== UPLOAD_ERR_NO_FILE) {
                $fichiers[$champ] = $_FILES[$champ];
            }
        }
        return $fichiers;
    }
}