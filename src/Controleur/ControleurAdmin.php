<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutAmi;
use App\Gigamed\Modele\DataObject\StatutBesoin;
use App\Gigamed\Modele\DataObject\StatutCandidature;
use App\Gigamed\Modele\DataObject\StatutChallenge;
use App\Gigamed\Modele\DataObject\StatutJuridiqueE;
use App\Gigamed\Modele\DataObject\StatutJuridiqueS;
use App\Gigamed\Modele\DataObject\TypeChallenge;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\AmiServiceInterface;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\ChallengeServiceInterface;
use App\Gigamed\Service\DocumentServiceInterface;
use App\Gigamed\Service\Exception\ServiceException;
use App\Gigamed\Service\JournalServiceInterface;
use App\Gigamed\Service\MailService;
use App\Gigamed\Service\NotationServiceInterface;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class
ControleurAdmin extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private BesoinServiceInterface $besoinService,
        private CandidatureServiceInterface $candidatureService,
        private AmiServiceInterface $amiService,
        private ChallengeServiceInterface $challengeService,
        private JournalServiceInterface $journalService,
        private DocumentServiceInterface $documentService,
        private MailService $mailService,
        private AffecterServiceInterface $affecterService,
        private NotationServiceInterface $notationService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    private const UPLOAD_DIR = __DIR__ . '/../../ressources/uploads/candidatures';

    private function verifierAdmin(): ?Response
    {
        return $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN);
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

    private static function niveauRole(RoleUtilisateur $role): int
    {
        return match($role) {
            RoleUtilisateur::SUPER_ADMIN     => 4,
            RoleUtilisateur::ADMIN           => 3,
            RoleUtilisateur::PARTENAIRE      => 2,
            default                          => 1,
        };
    }

    private function peutAgirSur(?\App\Gigamed\Modele\DataObject\Utilisateur $moi, ?\App\Gigamed\Modele\DataObject\Utilisateur $cible): bool
    {
        if ($moi === null || $cible === null) return false;
        if ($moi->getIdUtilisateur() === $cible->getIdUtilisateur()) return false;
        if ($moi->getRoleUtilisateur() === RoleUtilisateur::SUPER_ADMIN) return true;
        return self::niveauRole($moi->getRoleUtilisateur()) > self::niveauRole($cible->getRoleUtilisateur());
    }

    private function journaliser(string $action, string $objet): void
    {
        $utilisateur = $this->getUtilisateurConnecte();
        if ($utilisateur !== null) {
            $this->journalService->ajouter($action, $objet, $utilisateur->getIdUtilisateur());
        }
    }

    

    #[Route(path: '/admin', name: 'adminDashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $besoins      = $this->besoinService->recupererSoumis();
        $candidatures = $this->candidatureService->recupererSoumis();
        $amis         = $this->amiService->recupererSoumis();
        $utilisateurs = $this->utilisateurService->recupererTous();

        $nbBesoinsEnAttente    = count(array_filter($besoins, fn($b) => $b->getStatutBesoin()->value === 'en attente'));
        $nbCandidaturesDeposees = count(array_filter($candidatures, fn($c) => $c->getStatutCandidature()->value === 'déposé'));
        $nbAmiOuverts          = count(array_filter($amis, fn($a) => $a->getStatutAmi()->value === 'ouvert'));

        return $this->afficherTwig('admin/dashboard.html.twig', [
            'utilisateur'             => $this->getUtilisateurConnecte(),
            'nbUtilisateurs'          => count($utilisateurs),
            'nbBesoinsEnAttente'      => $nbBesoinsEnAttente,
            'nbCandidaturesDeposees'  => $nbCandidaturesDeposees,
            'nbAmiOuverts'            => $nbAmiOuverts,
            'entrees'                 => $this->journalService->recupererTousAvecNom(10),
        ]);
    }

    

    #[Route(path: '/admin/utilisateurs', name: 'adminUtilisateurs', methods: ['GET'])]
    public function listeUtilisateurs(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        return $this->afficherTwig('admin/utilisateurs.html.twig', [
            'utilisateur'  => $this->getUtilisateurConnecte(),
            'utilisateurs' => $this->utilisateurService->recupererTous(),
            'roles'        => RoleUtilisateur::cases(),
        ]);
    }

    #[Route(path: '/admin/utilisateurs/creer', name: 'adminAfficherFormulaireCreerUtilisateur', methods: ['GET'])]
    public function afficherFormulaireCreerUtilisateur(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        return $this->afficherTwig('utilisateur/formulaire.html.twig', [
            'utilisateur'        => $this->getUtilisateurConnecte(),
            'roles'              => RoleUtilisateur::cases(),
            'statutsJuridiquesS' => StatutJuridiqueS::cases(),
            'statutsJuridiquesE' => StatutJuridiqueE::cases(),
            'isAdmin'            => true,
        ]);
    }

    #[Route(path: '/admin/utilisateurs/creer', name: 'adminCreerUtilisateur', methods: ['POST'])]
    public function creerUtilisateur(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $donnees = [
            'nom'        => $_POST['nom'] ?? null,
            'prenom'     => $_POST['prenom'] ?? null,
            'email'      => $_POST['email'] ?? null,
            'telephone'  => $_POST['telephone'] ?? null,
            'statut'     => $_POST['statut'] ?? null,
            'motDePasse' => $_POST['motDePasse'] ?? null,
            'role'       => $_POST['role'] ?? null,
        ];

        $donneesStructure = [
            'nomStructure'           => $_POST['nomStructure'] ?? null,
            'formeJuridique'         => $_POST['formeJuridique'] ?? null,
            'numSIRET_RNA'           => $_POST['numSIRET_RNA'] ?? null,
            'dateCreation'           => $_POST['dateCreation'] ?? null,
            'adresseSiege'           => $_POST['adresseSiege'] ?? null,
            'commune'                => $_POST['commune'] ?? null,
            'siteWeb'                => $_POST['siteWeb'] ?? null,
            'referentNom'            => $_POST['referentNom'] ?? null,
            'referentFonction'       => $_POST['referentFonction'] ?? null,
            'referentEmail'          => $_POST['referentEmail'] ?? null,
            'referentTelephone'      => $_POST['referentTelephone'] ?? null,
            'statutJuridiqueS'       => $_POST['statutJuridiqueS'] ?? null,
            'nombreAssocies'         => $_POST['nombreAssocies'] ?? null,
            'nombreSalaries'         => $_POST['nombreSalaries'] ?? null,
            'statutJuridiqueE'       => $_POST['statutJuridiqueE'] ?? null,
            'effectif'               => $_POST['effectif'] ?? null,
            'chiffreAffaires'        => $_POST['chiffreAffaires'] ?? null,
            'implantationTerritoire' => $_POST['implantationTerritoire'] ?? null,
            'implantationEnvisagee'  => $_POST['implantationEnvisagee'] ?? null,
        ];

        try {
            $this->utilisateurService->creerParAdmin($donnees, $donneesStructure);
            $this->journaliser('Création utilisateur', "Utilisateur {$donnees['email']} créé par l'admin");
            MessageFlash::ajouter('success', "Utilisateur créé avec succès.");
            return $this->rediriger('adminUtilisateurs');
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('utilisateur/formulaire.html.twig', [
                'utilisateur'        => $this->getUtilisateurConnecte(),
                'roles'              => RoleUtilisateur::cases(),
                'statutsJuridiquesS' => StatutJuridiqueS::cases(),
                'statutsJuridiquesE' => StatutJuridiqueE::cases(),
                'ancien'             => $donnees,
                'ancienneStructure'  => $donneesStructure,
                'isAdmin'            => true,
            ]);
        }
    }

    #[Route(path: '/admin/utilisateurs/{id}', name: 'adminDetailUtilisateur', methods: ['GET'])]
    public function detailUtilisateur(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $cible = null;
        foreach ($this->utilisateurService->recupererTous() as $u) {
            if ($u->getIdUtilisateur() === $id) { $cible = $u; break; }
        }

        if ($cible === null) {
            MessageFlash::ajouter('danger', "Utilisateur introuvable.");
            return $this->rediriger('adminUtilisateurs');
        }

        $besoins      = array_values(array_filter($this->besoinService->recupererSoumis(), fn($b) => $b->getIdUtilisateur() === $id));
        $candidatures = array_values(array_filter($this->candidatureService->recupererSoumis(), fn($c) => $c->getIdUtilisateur() === $id));

        $structure = $cible->getIdStructure() !== null
            ? $this->utilisateurService->recupererStructure($cible->getIdStructure())
            : null;

        $moi = $this->getUtilisateurConnecte();
        return $this->afficherTwig('utilisateur/detail.html.twig', [
            'utilisateur'  => $moi,
            'cible'        => $cible,
            'structure'    => $structure,
            'besoins'      => $besoins,
            'candidatures' => $candidatures,
            'roles'        => RoleUtilisateur::cases(),
            'peutAgir'     => $this->peutAgirSur($moi, $cible),
        ]);
    }

    #[Route(path: '/admin/utilisateurs/{id}/modifier', name: 'adminAfficherFormulaireModifierUtilisateur', methods: ['GET'])]
    public function afficherFormulaireModifierUtilisateur(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $cible = null;
        foreach ($this->utilisateurService->recupererTous() as $u) {
            if ($u->getIdUtilisateur() === $id) { $cible = $u; break; }
        }
        if ($cible === null) {
            MessageFlash::ajouter('danger', "Utilisateur introuvable.");
            return $this->rediriger('adminUtilisateurs');
        }

        $structure = $cible->getIdStructure() !== null
            ? $this->utilisateurService->recupererStructure($cible->getIdStructure())
            : null;

        return $this->afficherTwig('utilisateur/formulaire.html.twig', [
            'utilisateur'        => $this->getUtilisateurConnecte(),
            'cible'              => $cible,
            'structure'          => $structure,
            'roles'              => RoleUtilisateur::cases(),
            'statutsJuridiquesS' => StatutJuridiqueS::cases(),
            'statutsJuridiquesE' => StatutJuridiqueE::cases(),
            'isAdmin'            => true,
        ]);
    }

    #[Route(path: '/admin/utilisateurs/{id}/modifier', name: 'adminModifierUtilisateur', methods: ['POST'])]
    public function modifierUtilisateur(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $cible = null;
        foreach ($this->utilisateurService->recupererTous() as $u) {
            if ($u->getIdUtilisateur() === $id) { $cible = $u; break; }
        }
        if ($cible === null) {
            MessageFlash::ajouter('danger', "Utilisateur introuvable.");
            return $this->rediriger('adminUtilisateurs');
        }

        $moi = $this->getUtilisateurConnecte();
        if (!$this->peutAgirSur($moi, $cible)) {
            MessageFlash::ajouter('danger', "Vous ne pouvez modifier que des utilisateurs ayant un rôle inférieur au vôtre.");
            return $this->rediriger('adminDetailUtilisateur', ['id' => $id]);
        }

        $donnees = [
            'nom'       => $_POST['nom'] ?? null,
            'prenom'    => $_POST['prenom'] ?? null,
            'telephone' => $_POST['telephone'] ?? null,
            'statut'    => $_POST['statut'] ?? null,
            'role'      => $_POST['role'] ?? null,
        ];

        $donneesStructure = [
            'nomStructure'           => $_POST['nomStructure'] ?? null,
            'formeJuridique'         => $_POST['formeJuridique'] ?? null,
            'numSIRET_RNA'           => $_POST['numSIRET_RNA'] ?? null,
            'dateCreation'           => $_POST['dateCreation'] ?? null,
            'adresseSiege'           => $_POST['adresseSiege'] ?? null,
            'commune'                => $_POST['commune'] ?? null,
            'siteWeb'                => $_POST['siteWeb'] ?? null,
            'referentNom'            => $_POST['referentNom'] ?? null,
            'referentFonction'       => $_POST['referentFonction'] ?? null,
            'referentEmail'          => $_POST['referentEmail'] ?? null,
            'referentTelephone'      => $_POST['referentTelephone'] ?? null,
            'statutJuridiqueS'       => $_POST['statutJuridiqueS'] ?? null,
            'nombreAssocies'         => $_POST['nombreAssocies'] ?? null,
            'nombreSalaries'         => $_POST['nombreSalaries'] ?? null,
            'statutJuridiqueE'       => $_POST['statutJuridiqueE'] ?? null,
            'effectif'               => $_POST['effectif'] ?? null,
            'chiffreAffaires'        => $_POST['chiffreAffaires'] ?? null,
            'implantationTerritoire' => $_POST['implantationTerritoire'] ?? null,
            'implantationEnvisagee'  => $_POST['implantationEnvisagee'] ?? null,
        ];

        try {
            $this->utilisateurService->modifierParAdmin($donnees, $donneesStructure, $id);
            $this->journaliser('Modification utilisateur', "Utilisateur #$id modifié par l'admin");
            try {
                $this->mailService->envoyerMailModificationCompte(
                    $cible->getEmailUtilisateur(),
                    $cible->getPrenomUtilisateur()
                );
            } catch (\Exception) {}
            MessageFlash::ajouter('success', "Utilisateur mis à jour.");
            return $this->rediriger('adminDetailUtilisateur', ['id' => $id]);
        } catch (ServiceException $e) {
            $structure = $cible->getIdStructure() !== null
                ? $this->utilisateurService->recupererStructure($cible->getIdStructure())
                : null;
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('utilisateur/formulaire.html.twig', [
                'utilisateur'        => $this->getUtilisateurConnecte(),
                'cible'              => $cible,
                'structure'          => $structure,
                'roles'              => RoleUtilisateur::cases(),
                'statutsJuridiquesS' => StatutJuridiqueS::cases(),
                'statutsJuridiquesE' => StatutJuridiqueE::cases(),
                'ancien'             => $donnees,
                'ancienneStructure'  => $donneesStructure,
                'isAdmin'            => true,
            ]);
        }
    }

    #[Route(path: '/admin/utilisateurs/{id}/role', name: 'adminChangerRole', methods: ['POST'])]
    public function changerRole(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $role = $_POST['role'] ?? null;
        try {
            $this->utilisateurService->changerRole($id, $role);
            $this->journaliser('Changement de rôle', "Utilisateur #$id → $role");
            MessageFlash::ajouter('success', "Rôle mis à jour.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailUtilisateur', ['id' => $id]);
    }

    #[Route(path: '/admin/utilisateurs/{id}/supprimer', name: 'adminSupprimerUtilisateur', methods: ['POST'])]
    public function supprimerUtilisateur(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $moi = $this->getUtilisateurConnecte();

        if ($moi !== null && $moi->getIdUtilisateur() === $id) {
            MessageFlash::ajouter('danger', "Vous ne pouvez pas supprimer votre propre compte.");
            return $this->rediriger('adminUtilisateurs');
        }

        $cible = $this->utilisateurService->recupererUtilisateurOuNullParId($id);
        if (!$this->peutAgirSur($moi, $cible)) {
            MessageFlash::ajouter('danger', "Vous ne pouvez supprimer que des utilisateurs ayant un rôle inférieur au vôtre.");
            return $this->rediriger('adminDetailUtilisateur', ['id' => $id]);
        }

        $raison = trim($_POST['raisonSuppression'] ?? '');
        if ($raison === '') {
            MessageFlash::ajouter('danger', "Vous devez préciser la raison de la suppression.");
            return $this->rediriger('adminDetailUtilisateur', ['id' => $id]);
        }

        
        $emailCible  = $cible?->getEmailUtilisateur() ?? '';
        $prenomCible = $cible?->getPrenomUtilisateur() ?? '';

        try {
            $this->utilisateurService->supprimer($id);
            if ($emailCible !== '') {
                try {
                    $this->mailService->envoyerMailSuppressionCompte($emailCible, $prenomCible, $raison);
                } catch (\Exception) {}
            }
            $this->journaliser('Suppression utilisateur', "Utilisateur #$id supprimé");
            MessageFlash::ajouter('success', "Utilisateur supprimé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminUtilisateurs');
    }

    

    #[Route(path: '/admin/besoins', name: 'adminBesoins', methods: ['GET'])]
    public function listeBesoins(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin = $this->getUtilisateurConnecte();
        return $this->afficherTwig('admin/besoins.html.twig', [
            'utilisateur' => $admin,
            'besoins'     => $this->besoinService->recupererPourAdmin($admin->getIdUtilisateur()),
        ]);
    }

    #[Route(path: '/admin/besoins/{id}', name: 'adminDetailBesoin', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailBesoin(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin  = $this->getUtilisateurConnecte();
        $besoin = $this->besoinService->recupererParId($id);
        if ($besoin === null || ($besoin->getStatutBesoin() === StatutBesoin::BROUILLON && $besoin->getIdUtilisateur() !== $admin->getIdUtilisateur())) {
            MessageFlash::ajouter('danger', "Besoin introuvable.");
            return $this->rediriger('adminBesoins');
        }

        $idSoumetteur = $besoin->getIdUtilisateur();
        if ($besoin->getStatutBesoin() === StatutBesoin::BROUILLON) {
            $session = \App\Gigamed\Modele\HTTP\Session::getInstance();
            if ($session->contient('adminBrouillonBesoinIdUtilisateur')) {
                $idSoumetteur = (int) $session->lire('adminBrouillonBesoinIdUtilisateur');
            }
        }
        $soumetteur = $this->utilisateurService->recupererUtilisateurOuNullParId($idSoumetteur);

        return $this->afficherTwig('besoin/detail.html.twig', [
            'utilisateur'    => $this->getUtilisateurConnecte(),
            'besoin'         => $besoin,
            'soumetteur'     => $soumetteur,
            'isAdmin'        => true,
            'typesChallenge' => TypeChallenge::cases(),
        ]);
    }

    #[Route(path: '/admin/besoins/{id}/refuser', name: 'adminRefuserBesoin', methods: ['POST'])]
    public function refuserBesoin(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $raison = trim($_POST['raisonRefus'] ?? '');
        try {
            $this->besoinService->refuser($id, $raison !== '' ? $raison : null);
            $this->journaliser('Refus besoin', "Besoin #$id refusé");
            MessageFlash::ajouter('success', "Besoin refusé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminBesoins');
    }

    #[Route(path: '/admin/besoins/{id}/vers-challenge', name: 'adminBesoinVersChallenge', methods: ['GET'])]
    public function afficherFormulaireChallenge(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $besoin = $this->besoinService->recupererParId($id);
        if ($besoin === null || $besoin->getStatutBesoin() === StatutBesoin::BROUILLON) {
            MessageFlash::ajouter('danger', "Besoin introuvable.");
            return $this->rediriger('adminBesoins');
        }

        return $this->afficherTwig('admin/besoin_vers_challenge.html.twig', [
            'utilisateur'    => $this->getUtilisateurConnecte(),
            'besoin'         => $besoin,
            'typesChallenge' => TypeChallenge::cases(),
        ]);
    }

    #[Route(path: '/admin/besoins/{id}/vers-challenge', name: 'adminTransformerEnChallenge', methods: ['POST'])]
    public function transformerEnChallenge(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $donnees = [
            'casUsagePilote'    => $_POST['casUsagePilote'] ?? null,
            'perimetreCasUsage' => $_POST['perimetreCasUsage'] ?? null,
            'dureeIndicative'   => $_POST['dureeIndicative'] ?? null,
            'sortieAttendue'    => $_POST['sortieAttendue'] ?? null,
            'typeChallenge'     => $_POST['typeChallenge'] ?? null,
        ];

        try {
            $this->besoinService->transformerEnChallenge($id, $donnees);
            $this->journaliser('Transformation challenge', "Besoin #$id → Challenge");
            MessageFlash::ajouter('success', "Challenge créé avec succès.");
            return $this->rediriger('adminChallenges');
        } catch (ServiceException $e) {
            $besoin = $this->besoinService->recupererParId($id);
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('admin/besoin_vers_challenge.html.twig', [
                'utilisateur'    => $this->getUtilisateurConnecte(),
                'besoin'         => $besoin,
                'typesChallenge' => TypeChallenge::cases(),
                'ancien'         => $donnees,
            ]);
        }
    }

    #[Route(path: '/admin/challenges/{id}/publier', name: 'adminPublierChallenge', methods: ['POST'])]
    public function publierChallenge(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        try {
            $idBesoin = $this->challengeService->publier($id);
            $this->journaliser('Publication challenge', "Challenge #$id publié");
            if ($idBesoin > 0) {
                $this->besoinService->marquerTransformeEnChallenge($idBesoin);
            }
            MessageFlash::ajouter('success', "Challenge publié avec succès.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailChallenge', ['id' => $id]);
    }

    #[Route(path: '/admin/besoins/{id}/vers-ami', name: 'adminBesoinVersAmi', methods: ['GET'])]
    public function afficherFormulaireAmi(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $besoin = $this->besoinService->recupererParId($id);
        if ($besoin === null || $besoin->getStatutBesoin() === StatutBesoin::BROUILLON) {
            MessageFlash::ajouter('danger', "Besoin introuvable.");
            return $this->rediriger('adminBesoins');
        }

        return $this->afficherTwig('admin/besoin_vers_ami.html.twig', [
            'utilisateur' => $this->getUtilisateurConnecte(),
            'besoin'      => $besoin,
        ]);
    }

    #[Route(path: '/admin/besoins/{id}/vers-ami', name: 'adminTransformerEnAmi', methods: ['POST'])]
    public function transformerEnAmi(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin  = $this->getUtilisateurConnecte();
        $donnees = [
            'casUsagePilote'    => $_POST['casUsagePilote'] ?? null,
            'perimetreCasUsage' => $_POST['perimetreCasUsage'] ?? null,
            'dureeIndicative'   => $_POST['dureeIndicative'] ?? null,
            'sortieAttendue'    => $_POST['sortieAttendue'] ?? null,
            'dateOuverture'     => $_POST['dateOuverture'] ?? null,
            'dateCloture'       => $_POST['dateCloture'] ?? null,
            'dateAuditions'     => $_POST['dateAuditions'] ?? null,
            'dateResultats'     => $_POST['dateResultats'] ?? null,
            'dateDemarrage'     => $_POST['dateDemarrage'] ?? null,
        ];

        try {
            $this->besoinService->transformerEnAmi($id, $donnees, $admin->getIdUtilisateur());
            $this->journaliser('Transformation AMI', "Besoin #$id → AMI");
            MessageFlash::ajouter('success', "AMI créé avec succès.");
            return $this->rediriger('adminAmi');
        } catch (ServiceException $e) {
            $besoin = $this->besoinService->recupererParId($id);
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('admin/besoin_vers_ami.html.twig', [
                'utilisateur' => $admin,
                'besoin'      => $besoin,
                'ancien'      => $donnees,
            ]);
        }
    }

    #[Route(path: '/admin/besoins/{id}/modifier', name: 'adminAfficherFormulaireModifierBesoin', methods: ['GET'])]
    public function afficherFormulaireModifierBesoin(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $besoin = $this->besoinService->recupererParId($id);
        if ($besoin === null) {
            MessageFlash::ajouter('danger', "Besoin introuvable.");
            return $this->rediriger('adminBesoins');
        }

        return $this->afficherTwig('besoin/formulaire.html.twig', [
            'utilisateur' => $this->getUtilisateurConnecte(),
            'besoin'      => $besoin,
            'isAdmin'     => true,
        ]);
    }

    #[Route(path: '/admin/besoins/{id}/modifier', name: 'adminModifierBesoin', methods: ['POST'])]
    public function modifierBesoin(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $donnees = $_POST;
        try {
            $this->besoinService->modifier($id, $donnees);
            $this->journaliser('Modification besoin', "Besoin #$id modifié par superAdmin");
            MessageFlash::ajouter('success', "Besoin mis à jour.");
            return $this->rediriger('adminDetailBesoin', ['id' => $id]);
        } catch (ServiceException $e) {
            $besoin = $this->besoinService->recupererParId($id);
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('besoin/formulaire.html.twig', [
                'utilisateur' => $this->getUtilisateurConnecte(),
                'besoin'      => $besoin,
                'ancien'      => $donnees,
                'isAdmin'     => true,
            ]);
        }
    }

    #[Route(path: '/admin/besoins/{id}/supprimer', name: 'adminSupprimerBesoin', methods: ['POST'])]
    public function supprimerBesoin(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $this->besoinService->supprimer($id);
            $this->journaliser('Suppression besoin', "Besoin #$id supprimé");
            MessageFlash::ajouter('success', "Besoin supprimé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminBesoins');
    }

    

    #[Route(path: '/admin/candidatures', name: 'adminCandidatures', methods: ['GET'])]
    public function listeCandidatures(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $amis = $this->amiService->recupererSoumis(); 
        $challenges = $this->challengeService->recupererSoumis();

        $amisFermes = [];
        foreach ($amis as $ami) {
            if (in_array($ami->getStatutAmi(), [StatutAmi::CLOTURE, StatutAmi::ARCHIVE], true)) {
                $amisFermes[] = $ami->getIdAmi();
            }
        }
        $challengesFermes = [];
        foreach ($challenges as $challenge) {
            if (in_array($challenge->getStatutChallenge(), [StatutChallenge::CLOTURE, StatutChallenge::ARCHIVE], true)) {
                $challengesFermes[] = $challenge->getIdChallenge();
            }
        }

        $admin = $this->getUtilisateurConnecte();
        return $this->afficherTwig('admin/candidatures.html.twig', [
            'utilisateur'      => $admin,
            'candidatures'     => $this->candidatureService->recupererPourAdmin($admin->getIdUtilisateur()),
            'statuts'          => $this->candidatureService->getStatutsAdmin(),
            'amisFermes'       => $amisFermes,
            'challengesFermes' => $challengesFermes,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}', name: 'adminDetailCandidature', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailCandidature(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $adminCourant = $this->getUtilisateurConnecte();
        $candidature  = $this->candidatureService->recupererParId($id);
        if ($candidature === null || ($candidature->getStatutCandidature() === StatutCandidature::BROUILLON && $candidature->getIdUtilisateur() !== $adminCourant->getIdUtilisateur())) {
            MessageFlash::ajouter('danger', "Candidature introuvable.");
            return $this->rediriger('adminCandidatures');
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

        $idSoumetteur = $candidature->getIdUtilisateur();
        if ($candidature->getStatutCandidature() === StatutCandidature::BROUILLON) {
            $session = \App\Gigamed\Modele\HTTP\Session::getInstance();
            if ($session->contient('adminBrouillonCandidatureIdUtilisateur')) {
                $idSoumetteur = (int) $session->lire('adminBrouillonCandidatureIdUtilisateur');
            }
        }
        $soumetteur = $this->utilisateurService->recupererUtilisateurOuNullParId($idSoumetteur);

        $notesAdmin = $this->notationService->recupererNotes($adminCourant->getIdUtilisateur(), $candidature->getIdCandidature());

        return $this->afficherTwig('candidature/detail.html.twig', [
            'utilisateur'               => $this->getUtilisateurConnecte(),
            'candidature'               => $candidature,
            'ami'                       => $ami,
            'challenge'                 => $challenge,
            'soumetteur'                => $soumetteur,
            'statuts'                   => $this->candidatureService->getStatutsAdmin(),
            'besoinsPrioritairesDecodes' => $besoinsPrioritairesDecodes,
            'documents'                 => $this->documentService->recupererParIdCandidature($candidature->getIdCandidature()),
            'isAdmin'                   => true,
            'aDejaNote'                 => count($notesAdmin) > 0,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}/statut', name: 'adminChangerStatutCandidature', methods: ['POST'])]
    public function changerStatutCandidature(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $statut = $_POST['statut'] ?? null;
        $messageIncomplet = $_POST['messageIncomplet'] ?? null;
        $messageRefus = $_POST['messageRefus'] ?? null;
        try {
            $this->candidatureService->changerStatut($id, $statut, $messageIncomplet, $messageRefus);
            $this->journaliser('Changement statut candidature', "Candidature #$id → $statut");
            MessageFlash::ajouter('success', "Statut mis à jour.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailCandidature', ['id' => $id]);
    }

    #[Route(path: '/admin/candidatures/{id}/modifier', name: 'adminAfficherFormulaireModifierCandidature', methods: ['GET'])]
    public function afficherFormulaireModifierCandidature(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null) {
            MessageFlash::ajouter('danger', "Candidature introuvable.");
            return $this->rediriger('adminCandidatures');
        }

        $besoinsPrioritairesDecodes = [];
        if ($candidature->getBesoinsPrioritaires()) {
            $besoinsPrioritairesDecodes = json_decode($candidature->getBesoinsPrioritaires(), true) ?? [];
        }

        $appuisStationTDecodes = [];
        if ($candidature->getAppuisStationT()) {
            $appuisStationTDecodes = array_map('trim', explode(',', $candidature->getAppuisStationT()));
        }

        $docsExistants = [];
        foreach ($this->documentService->recupererParIdCandidature($candidature->getIdCandidature()) as $doc) {
            $docsExistants[$doc->getNomDocument()] = $doc;
        }
        $utilisateurCandidat = $this->utilisateurService->recupererUtilisateurOuNullParId($candidature->getIdUtilisateur());
        $isEntrepriseCandidat = $utilisateurCandidat?->getRoleUtilisateur()->value === 'entreprise';

        return $this->afficherTwig('candidature/formulaire.html.twig', [
            'isAdmin'                   => true,
            'utilisateur'               => $this->getUtilisateurConnecte(),
            'candidature'               => $candidature,
            'statuts'                   => StatutCandidature::cases(),
            'besoinsPrioritairesDecodes' => $besoinsPrioritairesDecodes,
            'appuisStationTDecodes'      => $appuisStationTDecodes,
            'docsExistants'             => $docsExistants,
            'isEntrepriseCandidat'      => $isEntrepriseCandidat,
            'entreeStationTValue'       => $candidature->getEntreeStationT()->value,
            'maturiteValue'             => $candidature->getMaturite()->value,
            'statutCandidatureValue'    => $candidature->getStatutCandidature()->value,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}/modifier', name: 'adminModifierCandidature', methods: ['POST'])]
    public function modifierCandidature(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $donnees = $_POST;
        try {
            $this->candidatureService->modifier($id, $donnees);
            $this->documentService->sauvegarderFichiers($id, $this->extraireFichiers(), self::UPLOAD_DIR);
            $this->journaliser('Modification candidature', "Candidature #$id modifiée par superAdmin");
            MessageFlash::ajouter('success', "Candidature mise à jour.");
            return $this->rediriger('adminDetailCandidature', ['id' => $id]);
        } catch (ServiceException $e) {
            $candidature = $this->candidatureService->recupererParId($id);
            $besoinsPrioritairesDecodes = [];
            if ($candidature?->getBesoinsPrioritaires()) {
                $besoinsPrioritairesDecodes = json_decode($candidature->getBesoinsPrioritaires(), true) ?? [];
            }
            MessageFlash::ajouter('danger', $e->getMessage());
            $docsExistants = [];
            foreach ($this->documentService->recupererParIdCandidature($candidature->getIdCandidature()) as $doc) {
                $docsExistants[$doc->getNomDocument()] = $doc;
            }
            $utilisateurCandidat = $this->utilisateurService->recupererUtilisateurOuNullParId($candidature->getIdUtilisateur());
            $isEntrepriseCandidat = $utilisateurCandidat?->getRoleUtilisateur()->value === 'entreprise';
            return $this->afficherTwig('candidature/formulaire.html.twig', [
                'isAdmin'                   => true,
                'utilisateur'               => $this->getUtilisateurConnecte(),
                'candidature'               => $candidature,
                'statuts'                   => StatutCandidature::cases(),
                'besoinsPrioritairesDecodes' => $besoinsPrioritairesDecodes,
                'docsExistants'             => $docsExistants,
                'isEntrepriseCandidat'      => $isEntrepriseCandidat,
                'ancien'                    => $donnees,
                'entreeStationTValue'       => $candidature->getEntreeStationT()->value,
                'maturiteValue'             => $candidature->getMaturite()->value,
                'statutCandidatureValue'    => $candidature->getStatutCandidature()->value,
            ]);
        }
    }

    #[Route(path: '/admin/candidatures/{id}/supprimer', name: 'adminSupprimerCandidature', methods: ['POST'])]
    public function supprimerCandidature(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $this->candidatureService->supprimer($id);
            $this->journaliser('Suppression candidature', "Candidature #$id supprimée");
            MessageFlash::ajouter('success', "Candidature supprimée.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminCandidatures');
    }

    

    #[Route(path: '/admin/ami', name: 'adminAmi', methods: ['GET'])]
    public function listeAmi(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin = $this->getUtilisateurConnecte();
        return $this->afficherTwig('admin/ami.html.twig', [
            'utilisateur' => $admin,
            'amis'        => $this->amiService->recupererPourAdmin($admin->getIdUtilisateur()),
        ]);
    }

    #[Route(path: '/admin/ami/{id}/modifier', name: 'adminAfficherFormulaireModifierAmi', methods: ['GET'])]
    public function afficherFormulaireModifierAmi(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $ami = $this->amiService->recupererParId($id);
        if ($ami === null) {
            MessageFlash::ajouter('danger', "AMI introuvable.");
            return $this->rediriger('adminAmi');
        }

        return $this->afficherTwig('ami/formulaire.html.twig', [
            'utilisateur' => $this->getUtilisateurConnecte(),
            'ami'         => $ami,
            'filieres'    => Filiere::cases(),
            'statuts'     => StatutAmi::cases(),
        ]);
    }

    #[Route(path: '/admin/ami/{id}/modifier', name: 'adminModifierAmi', methods: ['POST'])]
    public function modifierAmi(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $donnees = $_POST;
        try {
            $this->amiService->modifier($id, $donnees);
            $this->journaliser('Modification AMI', "AMI #$id modifié par superAdmin");
            MessageFlash::ajouter('success', "AMI mis à jour.");
            return $this->rediriger('adminDetailAmi', ['id' => $id]);
        } catch (ServiceException $e) {
            $ami = $this->amiService->recupererParId($id);
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('ami/formulaire.html.twig', [
                'utilisateur' => $this->getUtilisateurConnecte(),
                'ami'         => $ami,
                'filieres'    => Filiere::cases(),
                'statuts'     => StatutAmi::cases(),
                'ancien'      => $donnees,
            ]);
        }
    }

    #[Route(path: '/admin/ami/{id}/supprimer', name: 'adminSupprimerAmi', methods: ['POST'])]
    public function supprimerAmi(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $this->amiService->supprimer($id);
            $this->journaliser('Suppression AMI', "AMI #$id supprimé");
            MessageFlash::ajouter('success', "AMI supprimé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminAmi');
    }

    #[Route(path: '/admin/ami/{id}/fin-notation', name: 'adminFinNotationAmi', methods: ['POST'])]
    public function finNotationAmi(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $ami = $this->amiService->recupererParId($id);
            if ($ami !== null && $ami->getStatutAmi() !== \App\Gigamed\Modele\DataObject\StatutAmi::CLOTURE) {
                $this->amiService->cloturer($id);
            }
            $this->candidatureService->passerEnInstructionParAmi($id);
            $this->journaliser('Fin de notation AMI', "AMI #$id → éligibles en instruction, déposés refusés");
            MessageFlash::ajouter('success', "Notation clôturée. Les candidatures éligibles sont en instruction.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailAmi', ['id' => $id]);
    }

    #[Route(path: '/admin/ami/{id}/archiver', name: 'adminArchiverAmi', methods: ['POST'])]
    public function archiverAmi(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        try {
            $this->amiService->archiver($id);
            $this->journaliser('Archivage AMI', "AMI #$id → archivé");
            MessageFlash::ajouter('success', "AMI archivé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailAmi', ['id' => $id]);
    }

    

    #[Route(path: '/admin/challenges', name: 'adminChallenges', methods: ['GET'])]
    public function listeChallenges(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        return $this->afficherTwig('admin/challenges.html.twig', [
            'utilisateur' => $this->getUtilisateurConnecte(),
            'challenges'  => $this->challengeService->recupererSoumis(),
        ]);
    }

    #[Route(path: '/admin/challenges/{id}', name: 'adminDetailChallenge', methods: ['GET'])]
    public function detailChallenge(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $challenge = $this->challengeService->recupererParId($id);
        if ($challenge === null) {
            MessageFlash::ajouter('danger', "Challenge introuvable.");
            return $this->rediriger('adminChallenges');
        }

        $besoin = $this->besoinService->recupererParId($challenge->getIdBesoin());

        $candidatures = array_values(array_filter(
            $this->candidatureService->recupererSoumis(),
            fn($c) => $c->getIdChallenge() === $id
        ));

        return $this->afficherTwig('challenge/detail.html.twig', [
            'utilisateur'  => $this->getUtilisateurConnecte(),
            'challenge'    => $challenge,
            'besoin'       => $besoin,
            'candidatures' => $candidatures,
            'isAdmin'      => true,
        ]);
    }

    #[Route(path: '/admin/challenges/{id}/modifier', name: 'adminAfficherFormulaireModifierChallenge', methods: ['GET'])]
    public function afficherFormulaireModifierChallenge(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $challenge = $this->challengeService->recupererParId($id);
        if ($challenge === null) {
            MessageFlash::ajouter('danger', "Challenge introuvable.");
            return $this->rediriger('adminChallenges');
        }

        return $this->afficherTwig('challenge/formulaire.html.twig', [
            'utilisateur'    => $this->getUtilisateurConnecte(),
            'challenge'      => $challenge,
            'filieres'       => Filiere::cases(),
            'typesChallenge' => TypeChallenge::cases(),
            'statuts'        => StatutChallenge::cases(),
        ]);
    }

    #[Route(path: '/admin/challenges/{id}/modifier', name: 'adminModifierChallenge', methods: ['POST'])]
    public function modifierChallenge(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $donnees = $_POST;
        try {
            $this->challengeService->modifier($id, $donnees);
            $this->journaliser('Modification challenge', "Challenge #$id modifié par superAdmin");
            MessageFlash::ajouter('success', "Challenge mis à jour.");
            return $this->rediriger('adminDetailChallenge', ['id' => $id]);
        } catch (ServiceException $e) {
            $challenge = $this->challengeService->recupererParId($id);
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('challenge/formulaire.html.twig', [
                'utilisateur'    => $this->getUtilisateurConnecte(),
                'challenge'      => $challenge,
                'filieres'       => Filiere::cases(),
                'typesChallenge' => TypeChallenge::cases(),
                'statuts'        => StatutChallenge::cases(),
                'ancien'         => $donnees,
            ]);
        }
    }

    #[Route(path: '/admin/challenges/{id}/supprimer', name: 'adminSupprimerChallenge', methods: ['POST'])]
    public function supprimerChallenge(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $this->challengeService->supprimer($id);
            $this->journaliser('Suppression challenge', "Challenge #$id supprimé");
            MessageFlash::ajouter('success', "Challenge supprimé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminChallenges');
    }

    #[Route(path: '/admin/challenges/{id}/fin-notation', name: 'adminFinNotationChallenge', methods: ['POST'])]
    public function finNotationChallenge(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        try {
            $this->challengeService->cloturer($id);
            $this->candidatureService->passerEnInstructionParChallenge($id);
            $this->journaliser('Fin de notation Challenge', "Challenge #$id → cloturé, éligibles en instruction, déposés refusés");
            MessageFlash::ajouter('success', "Notation clôturée. Les candidatures éligibles sont en instruction.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailChallenge', ['id' => $id]);
    }

    

    #[Route(path: '/admin/ami/{id}/decisions', name: 'adminDecisionsAmi', methods: ['GET'])]
    public function decisionsAmi(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin = $this->getUtilisateurConnecte();
        $ami   = $this->amiService->recupererParId($id);
        if ($ami === null) {
            MessageFlash::ajouter('danger', "AMI introuvable.");
            return $this->rediriger('adminAmi');
        }

        $candidatures = $this->candidatureService->recupererParIdAmi($id);
        $enInstruction = array_values(array_filter($candidatures, fn($c) => $c->getStatutCandidature() === StatutCandidature::INSTRUCTION));
        $ids = array_map(fn($c) => $c->getIdCandidature(), $enInstruction);
        $scores = $this->notationService->calculerScores($ids);

        usort($enInstruction, fn($a, $b) => ($scores[$b->getIdCandidature()] ?? 0) <=> ($scores[$a->getIdCandidature()] ?? 0));

        return $this->afficherTwig('admin/decisions.html.twig', [
            'utilisateur'  => $admin,
            'ami'          => $ami,
            'challenge'    => null,
            'candidatures' => $enInstruction,
            'scores'       => $scores,
        ]);
    }

    #[Route(path: '/admin/challenges/{id}/decisions', name: 'adminDecisionsChallenge', methods: ['GET'])]
    public function decisionsChallenge(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin     = $this->getUtilisateurConnecte();
        $challenge = $this->challengeService->recupererParId($id);
        if ($challenge === null) {
            MessageFlash::ajouter('danger', "Challenge introuvable.");
            return $this->rediriger('adminChallenges');
        }

        $candidatures = $this->candidatureService->recupererParIdChallenge($id);
        $enInstruction = array_values(array_filter($candidatures, fn($c) => $c->getStatutCandidature() === StatutCandidature::INSTRUCTION));
        $ids = array_map(fn($c) => $c->getIdCandidature(), $enInstruction);
        $scores = $this->notationService->calculerScores($ids);

        usort($enInstruction, fn($a, $b) => ($scores[$b->getIdCandidature()] ?? 0) <=> ($scores[$a->getIdCandidature()] ?? 0));

        return $this->afficherTwig('admin/decisions.html.twig', [
            'utilisateur'  => $admin,
            'ami'          => null,
            'challenge'    => $challenge,
            'candidatures' => $enInstruction,
            'scores'       => $scores,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}/decision-finale', name: 'adminDecisionFinale', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function decisionFinale(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $decision   = $_POST['decision'] ?? '';
        $motifRefus = $_POST['motifRefus'] ?? null;

        try {
            $this->candidatureService->decisionFinale($id, $decision, $motifRefus);
            $this->journaliser('Décision finale candidature', "Candidature #$id → $decision");
            MessageFlash::ajouter('success', "Décision enregistrée et email envoyé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }

        $idAmi       = isset($_POST['idAmi']) && $_POST['idAmi'] !== '' ? (int) $_POST['idAmi'] : null;
        $idChallenge = isset($_POST['idChallenge']) && $_POST['idChallenge'] !== '' ? (int) $_POST['idChallenge'] : null;

        if ($idAmi !== null) return $this->rediriger('adminDecisionsAmi', ['id' => $idAmi]);
        if ($idChallenge !== null) return $this->rediriger('adminDecisionsChallenge', ['id' => $idChallenge]);
        return $this->rediriger('adminDetailCandidature', ['id' => $id]);
    }

    #[Route(path: '/admin/challenges/{id}/archiver', name: 'adminArchiverChallenge', methods: ['POST'])]
    public function archiverChallenge(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        try {
            $this->challengeService->archiver($id);
            $this->journaliser('Archivage challenge', "Challenge #$id → archivé");
            MessageFlash::ajouter('success', "Challenge archivé.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailChallenge', ['id' => $id]);
    }

    #[Route(path: '/admin/ami/{id}', name: 'adminDetailAmi', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailAmi(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin = $this->getUtilisateurConnecte();
        $ami = $this->amiService->recupererParId($id);
        if ($ami === null || ($ami->getStatutAmi() === StatutAmi::BROUILLON && $ami->getIdUtilisateur() !== $admin->getIdUtilisateur())) {
            MessageFlash::ajouter('danger', "AMI introuvable.");
            return $this->rediriger('adminAmi');
        }

        $besoin = $ami->getIdBesoin() !== null
            ? $this->besoinService->recupererParId($ami->getIdBesoin())
            : null;

        $candidatures = array_values(array_filter(
            $this->candidatureService->recupererSoumis(),
            fn($c) => $c->getIdAmi() === $id
        ));

        return $this->afficherTwig('ami/detail.html.twig', [
            'utilisateur'  => $admin,
            'ami'          => $ami,
            'besoin'       => $besoin,
            'candidatures' => $candidatures,
            'statuts'      => StatutAmi::cases(),
            'isAdmin'      => true,
        ]);
    }

    

    #[Route(path: '/admin/ami/creer', name: 'adminAfficherFormulaireCreerAmi', methods: ['GET'])]
    public function afficherFormulaireCreerAmi(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin    = $this->getUtilisateurConnecte();
        $idAmi    = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $brouillon = null;
        if ($idAmi !== null) {
            $b = $this->amiService->recupererParId($idAmi);
            if ($b !== null && $b->getStatutAmi()->value === 'brouillon' && $b->getIdUtilisateur() === $admin->getIdUtilisateur()) {
                $brouillon = $b;
            }
        }

        $ancien = null;
        if ($brouillon !== null) {
            $placeholder = '2099-01-01';
            $f = fn(\DateTime $d) => $d->format('Y-m-d') !== $placeholder ? $d->format('Y-m-d') : '';
            $ancien = [
                'titre'                => $brouillon->getTitreAmi(),
                'objectif'             => $brouillon->getObjectifAmi(),
                'publicVise'           => $brouillon->getPublicVise(),
                'perimetrePrioritaire' => $brouillon->getPerimetrePrioritaire(),
                'filiere'              => $brouillon->getFiliere()->value,
                'exemplesInnovations'  => $brouillon->getExemplesInnovations(),
                'casUsagePilote'       => $brouillon->getCasUsagePilote(),
                'perimetreCasUsage'    => $brouillon->getPerimetreCasUsage(),
                'dureeIndicative'      => $brouillon->getDureeIndicative(),
                'sortieAttendue'       => $brouillon->getSortieAttendue(),
                'partenaires'          => $brouillon->getPartenaires(),
                'dateOuverture'        => $f($brouillon->getDateOuverture()),
                'dateCloture'          => $f($brouillon->getDateCloture()),
                'dateAuditions'        => $f($brouillon->getDateAuditions()),
                'dateResultats'        => $f($brouillon->getDateResultats()),
                'dateDemarrage'        => $f($brouillon->getDateDemarrage()),
            ];
        }

        return $this->afficherTwig('ami/formulaire.html.twig', [
            'utilisateur'       => $admin,
            'filieres'          => Filiere::cases(),
            'ancien'            => $ancien,
            'idBrouillonCharge' => $brouillon?->getIdAmi(),
        ]);
    }

    #[Route(path: '/admin/ami/creer', name: 'adminCreerAmi', methods: ['POST'])]
    public function creerAmi(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        $admin = $this->getUtilisateurConnecte();
        $action = $_POST['action'] ?? 'publier';
        $donnees = [
            'titre'               => $_POST['titre'] ?? null,
            'objectif'            => $_POST['objectif'] ?? null,
            'publicVise'          => $_POST['publicVise'] ?? null,
            'perimetrePrioritaire' => $_POST['perimetrePrioritaire'] ?? null,
            'filiere'             => $_POST['filiere'] ?? null,
            'exemplesInnovations' => $_POST['exemplesInnovations'] ?? null,
            'casUsagePilote'      => $_POST['casUsagePilote'] ?? null,
            'perimetreCasUsage'   => $_POST['perimetreCasUsage'] ?? null,
            'dureeIndicative'     => $_POST['dureeIndicative'] ?? null,
            'sortieAttendue'      => $_POST['sortieAttendue'] ?? null,
            'partenaires'         => $_POST['partenaires'] ?? null,
            'dateOuverture'       => $_POST['dateOuverture'] ?? null,
            'dateCloture'         => $_POST['dateCloture'] ?? null,
            'dateAuditions'       => $_POST['dateAuditions'] ?? null,
            'dateResultats'       => $_POST['dateResultats'] ?? null,
            'dateDemarrage'       => $_POST['dateDemarrage'] ?? null,
        ];

        $idBrouillonCharge = !empty($_POST['idBrouillonCharge']) ? (int) $_POST['idBrouillonCharge'] : null;

        if ($action === 'brouillon') {
            try {
                $this->amiService->sauvegarderBrouillon($donnees, $admin->getIdUtilisateur(), $idBrouillonCharge);
                $this->journaliser('Brouillon AMI', "AMI sauvegardé en brouillon par l'admin");
                MessageFlash::ajouter('success', "AMI sauvegardé en brouillon.");
                return $this->rediriger('adminAmi');
            } catch (ServiceException $e) {
                MessageFlash::ajouter('danger', $e->getMessage());
                return $this->afficherTwig('ami/formulaire.html.twig', [
                    'utilisateur' => $admin,
                    'filieres'    => Filiere::cases(),
                    'ancien'      => $donnees,
                ]);
            }
        }

        
        try {
            if ($idBrouillonCharge !== null) {
                $brouillonExistant = $this->amiService->recupererBrouillon($admin->getIdUtilisateur());
                if ($brouillonExistant !== null && $brouillonExistant->getIdAmi() === $idBrouillonCharge) {
                    $this->amiService->supprimer($brouillonExistant->getIdAmi());
                }
            }
            $this->amiService->creer($donnees, $admin->getIdUtilisateur());
            $this->journaliser('Création AMI', "AMI créé directement par l'admin");
            MessageFlash::ajouter('success', "AMI créé avec succès.");
            return $this->rediriger('adminAmi');
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('ami/formulaire.html.twig', [
                'utilisateur' => $admin,
                'filieres'    => Filiere::cases(),
                'ancien'      => $donnees,
            ]);
        }
    }

    #[Route(path: '/admin/ami/{id}/publier', name: 'adminPublierAmi', methods: ['POST'])]
    public function publierAmi(int $id): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        try {
            $ami = $this->amiService->recupererParId($id);
            $this->amiService->publier($id);
            $this->journaliser('Publication AMI', "AMI #$id publié");
            if ($ami !== null && $ami->getIdBesoin() !== null) {
                $this->besoinService->marquerTransformeEnAmi($ami->getIdBesoin());
            }
            MessageFlash::ajouter('success', "AMI publié avec succès.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
        }
        return $this->rediriger('adminDetailAmi', ['id' => $id]);
    }

    

    #[Route(path: '/admin/affectations', name: 'adminAffectations', methods: ['GET'])]
    public function affectations(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin        = $this->getUtilisateurConnecte();
        $candidatures = array_values(array_filter(
            $this->candidatureService->recupererPourAdmin($admin->getIdUtilisateur()),
            fn($c) => $c->getStatutCandidature() === StatutCandidature::ELIGIBLE
        ));

        $tousPartenaires = $this->utilisateurService->recupererParRole(RoleUtilisateur::PARTENAIRE);
        $partenairesParId = [];
        foreach ($tousPartenaires as $p) {
            $partenairesParId[$p->getIdUtilisateur()] = $p;
        }

        $toutesAffectations = $this->affecterService->recupererToutesLesAffectations();

        $amisParId = [];
        foreach ($this->amiService->recupererSoumis() as $a) {
            $amisParId[$a->getIdAmi()] = $a;
        }
        $challengesParId = [];
        foreach ($this->challengeService->recupererSoumis() as $ch) {
            $challengesParId[$ch->getIdChallenge()] = $ch;
        }

        $totalAffectations = array_sum(array_map('count', $toutesAffectations));

        $notesStatus = [];
        foreach ($toutesAffectations as $idCandidature => $idsPartenaires) {
            foreach ($idsPartenaires as $idP) {
                $notesStatus[$idCandidature][$idP] = count($this->notationService->recupererNotes($idP, $idCandidature)) > 0;
            }
        }

        return $this->afficherTwig('admin/affectations.html.twig', [
            'utilisateur'        => $admin,
            'candidatures'       => $candidatures,
            'tousPartenaires'    => $tousPartenaires,
            'partenairesParId'   => $partenairesParId,
            'toutesAffectations' => $toutesAffectations,
            'totalAffectations'  => $totalAffectations,
            'amisParId'          => $amisParId,
            'challengesParId'    => $challengesParId,
            'notesStatus'        => $notesStatus,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}/relancer/{idPartenaire}', name: 'adminRelancerPartenaire', methods: ['POST'])]
    public function relancerPartenaire(int $id, int $idPartenaire): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $partenaire  = $this->utilisateurService->recupererUtilisateurOuNullParId($idPartenaire);
        $candidature = $this->candidatureService->recupererParId($id);

        if ($partenaire !== null && $candidature !== null) {
            try {
                $this->mailService->envoyerMailRelance(
                    $partenaire->getEmailUtilisateur(),
                    $partenaire->getPrenomUtilisateur(),
                    $candidature->getNomProjet(),
                    $id
                );
                MessageFlash::ajouter('success', "Relance envoyée à {$partenaire->getPrenomUtilisateur()} {$partenaire->getNomUtilisateur()}.");
            } catch (\Exception) {
                MessageFlash::ajouter('danger', "Erreur lors de l'envoi de la relance.");
            }
        }

        return $this->rediriger('adminAffectations');
    }

    #[Route(path: '/admin/candidatures/{id}/affecter', name: 'adminAffecterPartenaire', methods: ['POST'])]
    public function affecterPartenaire(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $idPartenaire = (int) ($_POST['idPartenaire'] ?? 0);
        if ($idPartenaire > 0) {
            $candidature = $this->candidatureService->recupererParId($id);
            if ($candidature === null || $candidature->getStatutCandidature() !== StatutCandidature::ELIGIBLE) {
                MessageFlash::ajouter('danger', "L'affectation n'est possible que sur une candidature éligible.");
                return $this->rediriger('adminAffectations');
            }
            $this->affecterService->affecter($idPartenaire, $id);
            $this->journaliser('Affectation partenaire', "Partenaire #$idPartenaire → Candidature #$id");

            $partenaire = $this->utilisateurService->recupererUtilisateurOuNullParId($idPartenaire);
            if ($partenaire !== null) {
                try {
                    $this->mailService->envoyerMailAffectation(
                        $partenaire->getEmailUtilisateur(),
                        $partenaire->getPrenomUtilisateur(),
                        $candidature->getNomProjet(),
                        $id
                    );
                } catch (\Exception) {}
            }

            MessageFlash::ajouter('success', "Partenaire affecté avec succès.");
        }
        return $this->rediriger('adminAffectations');
    }

    #[Route(path: '/admin/candidatures/{id}/desaffecter/{idPartenaire}', name: 'adminDesaffecterPartenaire', methods: ['POST'])]
    public function desaffecterPartenaire(int $id, int $idPartenaire): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $this->affecterService->desaffecter($idPartenaire, $id);
        $this->journaliser('Désaffectation partenaire', "Partenaire #$idPartenaire retiré de Candidature #$id");
        MessageFlash::ajouter('success', "Partenaire retiré.");
        return $this->rediriger('adminAffectations');
    }

    

    #[Route(path: '/admin/besoins/soumettre', name: 'adminAfficherFormulaireSoumettreBesoins', methods: ['GET'])]
    public function afficherFormulaireSoumettreBesoins(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin    = $this->getUtilisateurConnecte();
        $idBesoin = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $brouillon = null;
        if ($idBesoin !== null) {
            $b = $this->besoinService->recupererParId($idBesoin);
            if ($b !== null && $b->getStatutBesoin()->value === 'brouillon' && $b->getIdUtilisateur() === $admin->getIdUtilisateur()) {
                $brouillon = $b;
            }
        }

        $ancien = null;
        if ($brouillon !== null) {
            $ancien = [
                'titreBesoin'          => $brouillon->getTitreBesoin(),
                'filiere'              => $brouillon->getFiliere()->value,
                'objectifBesoin'       => $brouillon->getObjectifBesoin(),
                'publicVise'           => $brouillon->getPublicVise(),
                'perimetrePrioritaire' => $brouillon->getPerimetrePrioritaire(),
                'vision'               => $brouillon->getVision(),
                'enjeuxMajeurs'        => $brouillon->getEnjeuxMajeurs(),
                'besoinsPrioritaires'  => $brouillon->getBesoinsPrioritaires(),
                'exemplesInnovations'  => $brouillon->getExemplesInnovations(),
                'partenaires'          => $brouillon->getPartenaires(),
            ];
            $session = \App\Gigamed\Modele\HTTP\Session::getInstance();
            if ($session->contient('adminBrouillonBesoinIdUtilisateur')) {
                $ancien['idUtilisateur'] = $session->lire('adminBrouillonBesoinIdUtilisateur');
            }
        }

        return $this->afficherTwig('besoin/formulaire.html.twig', [
            'utilisateur'  => $admin,
            'utilisateurs' => $this->utilisateurService->recupererTous(),
            'filieres'     => Filiere::cases(),
            'idBrouillon'  => $brouillon?->getIdBesoin(),
            'ancien'       => $ancien,
            'isAdmin'      => true,
        ]);
    }

    #[Route(path: '/admin/besoins/soumettre', name: 'adminSoumettreBesoins', methods: ['POST'])]
    public function soumettreBesoins(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin         = $this->getUtilisateurConnecte();
        $idUtilisateur = (int) ($_POST['idUtilisateur'] ?? 0);
        $idBesoin      = isset($_POST['idBesoin']) && $_POST['idBesoin'] !== '' ? (int) $_POST['idBesoin'] : null;
        $donnees       = $_POST;
        $action        = $_POST['action'] ?? 'soumettre';

        $session = \App\Gigamed\Modele\HTTP\Session::getInstance();
        try {
            if ($action === 'brouillon') {
                $this->besoinService->sauvegarderBrouillonParAdmin($donnees, $admin->getIdUtilisateur(), $idBesoin);
                if ($idUtilisateur > 0) {
                    $session->enregistrer('adminBrouillonBesoinIdUtilisateur', $idUtilisateur);
                }
                $this->journaliser('Brouillon besoin sauvegardé', "Brouillon sauvegardé par admin #{$admin->getIdUtilisateur()}");
                MessageFlash::ajouter('success', "Brouillon sauvegardé.");
                return $this->rediriger('adminBesoins');
            }

            $session->supprimer('adminBrouillonBesoinIdUtilisateur');
            
            $this->besoinService->soumettreParAdmin($donnees, $idUtilisateur, $admin->getIdUtilisateur(), $idBesoin);
            $this->journaliser('Besoin soumis par superAdmin', "Besoin soumis pour utilisateur #$idUtilisateur");
            MessageFlash::ajouter('success', "Besoin soumis avec succès.");
            return $this->rediriger('adminBesoins');
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('besoin/formulaire.html.twig', [
                'utilisateur'  => $admin,
                'utilisateurs' => $this->utilisateurService->recupererTous(),
                'filieres'     => Filiere::cases(),
                'ancien'       => $donnees,
                'isAdmin'      => true,
            ]);
        }
    }

    

    #[Route(path: '/admin/candidatures/soumettre', name: 'adminAfficherFormulaireSoumettreCandidature', methods: ['GET'])]
    public function afficherFormulaireSoumettreCandidature(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin    = $this->getUtilisateurConnecte();
        $idCandidature = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $brouillon = null;
        if ($idCandidature !== null) {
            $b = $this->candidatureService->recupererParId($idCandidature);
            if ($b !== null && $b->getStatutCandidature()->value === 'brouillon' && $b->getIdUtilisateur() === $admin->getIdUtilisateur()) {
                $brouillon = $b;
            }
        }

        $ancien = null;
        $docsExistants = [];
        if ($brouillon !== null) {
            $ancien = $this->candidatureEnAncien($brouillon);
            $session = \App\Gigamed\Modele\HTTP\Session::getInstance();
            if ($session->contient('adminBrouillonCandidatureIdUtilisateur')) {
                $ancien['idUtilisateur'] = $session->lire('adminBrouillonCandidatureIdUtilisateur');
            }
            foreach ($this->documentService->recupererParIdCandidature($brouillon->getIdCandidature()) as $doc) {
                $docsExistants[$doc->getNomDocument()] = $doc;
            }
        }

        return $this->afficherTwig('candidature/formulaire.html.twig', [
            'isAdmin'           => true,
            'utilisateur'       => $admin,
            'utilisateurs'      => $this->utilisateurService->recupererTous(),
            'amis'              => $this->amiService->recupererSoumis(),
            'challenges'        => $this->challengeService->recupererSoumis(),
            'ancien'            => $ancien,
            'idBrouillonCharge' => $brouillon?->getIdCandidature(),
            'docsExistants'     => $docsExistants,
        ]);
    }

    private function candidatureEnAncien(\App\Gigamed\Modele\DataObject\Candidature $c): array
    {
        $besoinsPrioritaires = [];
        if ($c->getBesoinsPrioritaires()) {
            $besoinsPrioritaires = json_decode($c->getBesoinsPrioritaires(), true) ?? [];
        }
        return [
            'idAmi'                    => $c->getIdAmi(),
            'idChallenge'              => $c->getIdChallenge(),
            'nomProjet'                => $c->getNomProjet(),
            'entreeStationT'           => $c->getEntreeStationT()->value,
            'filiereCandidat'          => $c->getFiliereCandidat(),
            'besoinTraite'             => $c->getBesoinTraite(),
            'descriptionProjet'        => $c->getDescriptionProjet(),
            'valeurAjoutee'            => $c->getValeurAjoutee(),
            'innovationDifferentiation'=> $c->getInnovationDifferentiation(),
            'maturite'                 => $c->getMaturite()->value,
            'references'               => $c->getReferences_(),
            'objectifPilote'           => $c->getObjectifPilote(),
            'perimetreGeographique'    => $c->getPerimetreGeographique(),
            'dureeExperimentation'     => $c->getDureeExperimentation(),
            'publicsCibles'            => $c->getPublicsCibles(),
            'budgetMobiliser'          => $c->getBudgetMobiliser(),
            'moyensMobiliser'          => $c->getMoyensMobiliser(),
            'conditionsReussite'       => $c->getConditionsReussite(),
            'derouteOperationnel'      => $c->getDerouteOperationnel(),
            'partenairesRecherches'    => $c->getPartenairesRecherches(),
            'appuisStationT'           => $c->getAppuisStationT(),
            'modelEconomique'          => $c->getModelEconomique(),
            'conditionsDeploiement'    => $c->getConditionsDeploiement(),
            'impactAttendu'            => $c->getImpactAttendu(),
            'pitchCourt'               => $c->getPitchCourt(),
            'problemeIdentifie'        => $c->getProblemeIdentifie(),
            'concurrence'              => $c->getConcurrence(),
            'clientsCibles'            => $c->getClientsCibles(),
            'preuvesBesoin'            => $c->getPreuvesBesoin(),
            'etatAvancement'           => $c->getEtatAvancement(),
            'besoinsFinanciers'        => $c->getBesoinsFinanciers(),
            'equipe'                   => $c->getEquipe(),
            'forcesEquipe'             => $c->getForcesEquipe(),
            'programmeGigamed'         => $c->getProgrammeGigamed()?->value,
            'niveauAccompagnement'     => $c->getNiveauAccompagnement()?->value,
            'besoinsPrioritaires'      => $besoinsPrioritaires,
            'objectifAccompagnement'   => $c->getObjectifAccompagnement(),
        ];
    }

    #[Route(path: '/admin/candidatures/soumettre', name: 'adminSoumettreCandidature', methods: ['POST'])]
    public function soumettreCandidature(): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN);
        if ($erreur !== null) return $erreur;

        $admin              = $this->getUtilisateurConnecte();
        $idUtilisateur      = (int) ($_POST['idUtilisateur'] ?? 0);
        $idAmi              = !empty($_POST['idAmi']) ? (int) $_POST['idAmi'] : null;
        $idChallenge        = !empty($_POST['idChallenge']) ? (int) $_POST['idChallenge'] : null;
        $idBrouillonCharge  = !empty($_POST['idBrouillonCharge']) ? (int) $_POST['idBrouillonCharge'] : null;
        $donnees            = $_POST;
        $action             = $_POST['action'] ?? 'soumettre';

        $session = \App\Gigamed\Modele\HTTP\Session::getInstance();

        try {
            if ($action === 'brouillon') {
                $brouillonSauvegarde = $this->candidatureService->sauvegarderBrouillonParAdmin($donnees, $admin->getIdUtilisateur(), $idAmi, $idChallenge, $idBrouillonCharge);
                $this->documentService->sauvegarderFichiers($brouillonSauvegarde->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
                if ($idUtilisateur > 0) {
                    $session->enregistrer('adminBrouillonCandidatureIdUtilisateur', $idUtilisateur);
                }
                $this->journaliser('Brouillon candidature sauvegardé', "Brouillon sauvegardé par admin #{$admin->getIdUtilisateur()}");
                MessageFlash::ajouter('success', "Brouillon sauvegardé.");
                return $this->rediriger('adminCandidatures');
            }

            $session->supprimer('adminBrouillonCandidatureIdUtilisateur');
            $candidature = $this->candidatureService->deposerParAdmin($donnees, $idUtilisateur, $idAmi, $idChallenge, $admin->getIdUtilisateur(), $idBrouillonCharge);
            $this->documentService->sauvegarderFichiers($candidature->getIdCandidature(), $this->extraireFichiers(), self::UPLOAD_DIR);
            $this->journaliser('Candidature soumise par superAdmin', "Candidature pour utilisateur #$idUtilisateur");
            MessageFlash::ajouter('success', "Candidature soumise avec succès.");
            return $this->rediriger('adminCandidatures');
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('candidature/formulaire.html.twig', [
                'isAdmin'      => true,
                'utilisateur'  => $admin,
                'utilisateurs' => $this->utilisateurService->recupererTous(),
                'amis'         => $this->amiService->recupererSoumis(),
                'challenges'   => $this->challengeService->recupererSoumis(),
                'ancien'       => $donnees,
            ]);
        }
    }

    

    #[Route(path: '/admin/candidatures/{id}/noter', name: 'adminAfficherFormulaireNoterCandidature', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function afficherFormulaireNoterCandidature(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN);
        if ($erreur !== null) return $erreur;

        $admin       = $this->getUtilisateurConnecte();
        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null || $candidature->getStatutCandidature() !== StatutCandidature::ELIGIBLE) {
            MessageFlash::ajouter('danger', "La notation n'est possible que pour les candidatures éligibles.");
            return $this->rediriger('adminCandidatures');
        }

        $ami       = $candidature->getIdAmi() !== null ? $this->amiService->recupererParId($candidature->getIdAmi()) : null;
        $challenge = $candidature->getIdChallenge() !== null ? $this->challengeService->recupererParId($candidature->getIdChallenge()) : null;

        $criteres = $this->notationService->recupererCriteres($ami?->getIdAmi(), $challenge?->getIdChallenge());
        $notes    = $this->notationService->recupererNotes($admin->getIdUtilisateur(), $id);

        return $this->afficherTwig('candidature/noter.html.twig', [
            'utilisateur' => $admin,
            'candidature' => $candidature,
            'ami'         => $ami,
            'challenge'   => $challenge,
            'criteres'    => $criteres,
            'notes'       => $notes,
            'isAdmin'     => true,
        ]);
    }

    #[Route(path: '/admin/candidatures/{id}/noter', name: 'adminNoterCandidature', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function noterCandidature(int $id): Response
    {
        $erreur = $this->verifierAcces(RoleUtilisateur::SUPER_ADMIN, RoleUtilisateur::ADMIN);
        if ($erreur !== null) return $erreur;

        $admin       = $this->getUtilisateurConnecte();
        $candidature = $this->candidatureService->recupererParId($id);
        if ($candidature === null || $candidature->getStatutCandidature() !== StatutCandidature::ELIGIBLE) {
            MessageFlash::ajouter('danger', "La notation n'est possible que pour les candidatures éligibles.");
            return $this->rediriger('adminCandidatures');
        }

        $this->notationService->sauvegarder($admin->getIdUtilisateur(), $id, $_POST['note'] ?? [], $_POST['commentaire'] ?? []);
        $this->journaliser('Notation candidature', "Candidature #$id notée par admin #{$admin->getIdUtilisateur()}");

        MessageFlash::ajouter('success', "Notes enregistrées.");
        return $this->rediriger('adminDetailCandidature', ['id' => $id]);
    }

    

    #[Route(path: '/admin/journal', name: 'adminJournal', methods: ['GET'])]
    public function journal(): Response
    {
        $erreur = $this->verifierAdmin();
        if ($erreur !== null) return $erreur;

        return $this->afficherTwig('admin/journal.html.twig', [
            'utilisateur' => $this->getUtilisateurConnecte(),
            'entrees'     => $this->journalService->recupererTousAvecNom(500),
        ]);
    }
}