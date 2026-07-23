<?php

namespace App\Gigamed\Controleur;

use App\Gigamed\Lib\ConnexionUtilisateurInterface;
use App\Gigamed\Lib\MessageFlash;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Service\AffecterServiceInterface;
use App\Gigamed\Service\BesoinServiceInterface;
use App\Gigamed\Service\NotationServiceInterface;
use App\Gigamed\Service\CandidatureServiceInterface;
use App\Gigamed\Service\Exception\ServiceException;
use App\Gigamed\Service\UtilisateurServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ControleurUtilisateur extends ControleurConnecte
{
    public function __construct(
        ContainerInterface $container,
        ConnexionUtilisateurInterface $connexionUtilisateur,
        UtilisateurServiceInterface $utilisateurService,
        private ConnexionUtilisateurInterface $connexionUtilisateurJWT,
        private BesoinServiceInterface $besoinService,
        private CandidatureServiceInterface $candidatureService,
        private AffecterServiceInterface $affecterService,
        private NotationServiceInterface $notationService,
    ) {
        parent::__construct($container, $connexionUtilisateur, $utilisateurService);
    }

    #[Route(path: '/connexion', name: 'afficherFormulaireConnexion', methods: ['GET'])]
    public function afficherFormulaireConnexion(): Response
    {
        return $this->afficherTwig('utilisateur/connexion.html.twig');
    }

    #[Route(path: '/connexion', name: 'connecter', methods: ['POST'])]
    public function connecter(): Response
    {
        $email = $_POST['email'] ?? null;
        $mdp = $_POST['mot-de-passe'] ?? null;

        try {
            $utilisateur = $this->utilisateurService->authentifier($email, $mdp);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->rediriger("afficherFormulaireConnexion");
        }

        $this->connexionUtilisateur->connecter($utilisateur->getEmailUtilisateur());
        $this->connexionUtilisateurJWT->connecter((string) $utilisateur->getIdUtilisateur());
        MessageFlash::ajouter("success", "Connexion réussie. Bienvenue !");
        return $this->rediriger("accueil");
    }

    #[Route(path: '/inscription', name: 'afficherFormulaireInscription', methods: ['GET'])]
    public function afficherFormulaireInscription(): Response
    {
        return $this->afficherTwig('utilisateur/inscription.html.twig');
    }

    #[Route(path: '/inscription', name: 'inscrire', methods: ['POST'])]
    public function inscrire(): Response
    {
        $motDePasse        = $_POST['mot-de-passe'] ?? null;
        $motDePasseConfirm = $_POST['mot-de-passe-confirm'] ?? null;

        $donneesUtilisateur = [
            'nom'        => $_POST['nom'] ?? null,
            'prenom'     => $_POST['prenom'] ?? null,
            'email'      => $_POST['email'] ?? null,
            'telephone'  => $_POST['telephone'] ?? null,
            'statut'     => $_POST['statut'] ?? null,
            'motDePasse' => $motDePasse,
        ];

        $donneesStructure = [
            'nomStructure'           => $_POST['nomStructure'] ?? null,
            'formeJuridique'         => $_POST['formeJuridique'] ?? null,
            'numSIRET_RNA'           => $_POST['numSIRET_RNA'] ?? null,
            'dateCreation'           => $_POST['dateCreation'] ?? null,
            'adresseSiege'           => $_POST['adresseSiege'] ?? null,
            'commune'                => $_POST['commune'] ?? null,
            'typeStructure'          => $_POST['typeStructure'] ?? null,
            'siteWeb'                => $_POST['siteWeb'] ?? null,
            'effectif'               => $_POST['effectif'] ?? null,
            'chiffreAffaires'        => $_POST['chiffreAffaires'] ?? null,
            'implantationTerritoire' => $_POST['implantationTerritoire'] ?? null,
            'implantationEnvisagee'  => $_POST['implantationEnvisagee'] ?? null,
            'nombreAssocies'         => $_POST['nombreAssocies'] ?? null,
            'nombreSalaries'         => $_POST['nombreSalaries'] ?? null,
            'statutJuridiqueS'       => $_POST['statutJuridiqueS'] ?? null,
            'statutJuridiqueE'       => $_POST['statutJuridiqueE'] ?? null,
            'referentNom'            => $_POST['referentNom'] ?? null,
            'referentFonction'       => $_POST['referentFonction'] ?? null,
            'referentEmail'          => $_POST['referentEmail'] ?? null,
            'referentTelephone'      => $_POST['referentTelephone'] ?? null,
        ];

        if ($motDePasse !== $motDePasseConfirm) {
            MessageFlash::ajouter("danger", "Les deux mots de passe ne correspondent pas.");
            return $this->afficherTwig('utilisateur/inscription.html.twig', [
                'ancien' => array_merge($donneesUtilisateur, $donneesStructure),
            ]);
        }

        try {
            $this->utilisateurService->inscrire($donneesUtilisateur, $donneesStructure);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->afficherTwig('utilisateur/inscription.html.twig', [
                'ancien' => array_merge($donneesUtilisateur, $donneesStructure),
            ]);
        }

        MessageFlash::ajouter("success", "Compte créé avec succès !");
        return $this->rediriger("afficherFormulaireConnexion");
    }

    #[Route(path: '/choisir-mot-de-passe/{token}', name: 'afficherFormulaireChoixMotDePasse', methods: ['GET'])]
    public function afficherFormulaireChoixMotDePasse(string $token): Response
    {
        return $this->afficherTwig('utilisateur/choisir_mot_de_passe.html.twig', ['token' => $token]);
    }

    #[Route(path: '/choisir-mot-de-passe/{token}', name: 'choisirMotDePasse', methods: ['POST'])]
    public function choisirMotDePasse(string $token): Response
    {
        $motDePasse   = $_POST['motDePasse'] ?? '';
        $confirmation = $_POST['motDePasseConfirm'] ?? '';
        if ($motDePasse !== $confirmation) {
            MessageFlash::ajouter('danger', "Les mots de passe ne correspondent pas.");
            return $this->afficherTwig('utilisateur/choisir_mot_de_passe.html.twig', ['token' => $token]);
        }
        try {
            $this->utilisateurService->choisirMotDePasse($token, $motDePasse);
            MessageFlash::ajouter('success', "Votre mot de passe a été défini. Vous pouvez maintenant vous connecter.");
            return $this->rediriger('afficherFormulaireConnexion');
        } catch (ServiceException $e) {
            MessageFlash::ajouter('danger', $e->getMessage());
            return $this->afficherTwig('utilisateur/choisir_mot_de_passe.html.twig', ['token' => $token]);
        }
    }

    #[Route(path: '/verifier-email/{token}', name: 'verifierEmail', methods: ['GET'])]
    public function verifierEmail(string $token): Response
    {
        try {
            $this->utilisateurService->verifierEmail($token);
            MessageFlash::ajouter("success", "Votre adresse email a été vérifiée. Vous pouvez maintenant vous connecter.");
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
        }
        return $this->rediriger("afficherFormulaireConnexion");
    }

    #[Route(path: '/mon-espace', name: 'monEspace', methods: ['GET'])]
    public function monEspace(): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET,
            RoleUtilisateur::SUPER_ADMIN,
            RoleUtilisateur::ADMIN,
            RoleUtilisateur::PARTENAIRE
        );
        if ($erreur !== null) return $erreur;

        $utilisateur   = $this->getUtilisateurConnecte();
        $idUtilisateur = $utilisateur->getIdUtilisateur();

        if ($utilisateur->getRoleUtilisateur() === RoleUtilisateur::PARTENAIRE) {
            $idsCandidatures = $this->affecterService->recupererIdCandidaturesParIdUtilisateur($idUtilisateur);

            $candidaturesAffectees = [];
            foreach ($idsCandidatures as $idC) {
                $c = $this->candidatureService->recupererParId((int) $idC);
                if ($c !== null) $candidaturesAffectees[] = $c;
            }

            $notesParCandidature = $this->notationService->recupererNotesParCandidatures($idsCandidatures, $idUtilisateur);

            $totalCriteres = count($this->notationService->recupererTousCriteres());
            $criteresCounts = [];
            foreach ($candidaturesAffectees as $c) {
                $criteresCounts[$c->getIdCandidature()] = $totalCriteres;
            }

            $besoins = $this->besoinService->recupererParIdUtilisateur($idUtilisateur);

            return $this->afficherTwig('utilisateur/monEspace.html.twig', [
                'utilisateur'           => $utilisateur,
                'structure'             => null,
                'hasBesoin'             => count($besoins) > 0,
                'hasCandidature'        => false,
                'besoins'               => $besoins,
                'candidatures'          => [],
                'candidaturesAffectees' => $candidaturesAffectees,
                'notesParCandidature'   => $notesParCandidature,
                'criteresCounts'        => $criteresCounts,
            ]);
        }

        $structure = $utilisateur->getIdStructure() !== null
            ? $this->utilisateurService->recupererStructure($utilisateur->getIdStructure())
            : null;

        $besoins        = $this->besoinService->recupererParIdUtilisateur($idUtilisateur);
        $candidatures   = $this->candidatureService->recupererParIdUtilisateur($idUtilisateur);
        $hasBesoin      = $this->besoinService->existeParIdUtilisateur($idUtilisateur);
        $hasCandidature = count($candidatures) > 0;

        return $this->afficherTwig('utilisateur/monEspace.html.twig', [
            'utilisateur'    => $utilisateur,
            'structure'      => $structure,
            'hasBesoin'      => $hasBesoin,
            'hasCandidature' => $hasCandidature,
            'besoins'        => $besoins,
            'candidatures'   => $candidatures,
        ]);
    }

    #[Route(path: '/mon-espace/modifier', name: 'afficherFormulaireModification', methods: ['GET'])]
    public function afficherFormulaireModification(): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET,
            RoleUtilisateur::SUPER_ADMIN,
            RoleUtilisateur::ADMIN,
            RoleUtilisateur::PARTENAIRE
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $structure = $utilisateur->getIdStructure() !== null
            ? $this->utilisateurService->recupererStructure($utilisateur->getIdStructure())
            : null;

        return $this->afficherTwig('utilisateur/formulaire.html.twig', [
            'utilisateur' => $utilisateur,
            'structure'   => $structure,
        ]);
    }

    #[Route(path: '/mon-espace/modifier', name: 'modifierProfil', methods: ['POST'])]
    public function modifierProfil(): Response
    {
        $erreur = $this->verifierAcces(
            RoleUtilisateur::STARTUP,
            RoleUtilisateur::ENTREPRISE,
            RoleUtilisateur::PORTEUR_DE_PROJET,
            RoleUtilisateur::SUPER_ADMIN,
            RoleUtilisateur::ADMIN,
            RoleUtilisateur::PARTENAIRE
        );
        if ($erreur !== null) return $erreur;

        $utilisateur = $this->getUtilisateurConnecte();
        $structure = $utilisateur->getIdStructure() !== null
            ? $this->utilisateurService->recupererStructure($utilisateur->getIdStructure())
            : null;

        $donneesUtilisateur = [
            'nom'       => $_POST['nom'] ?? null,
            'prenom'    => $_POST['prenom'] ?? null,
            'telephone' => $_POST['telephone'] ?? null,
            'statut'    => $_POST['statut'] ?? null,
        ];

        $donneesStructure = [
            'idStructure'            => $utilisateur->getIdStructure(),
            'nomStructure'           => $_POST['nomStructure'] ?? null,
            'formeJuridique'         => $_POST['formeJuridique'] ?? null,
            'numSIRET_RNA'           => $_POST['numSIRET_RNA'] ?? null,
            'dateCreation'           => $_POST['dateCreation'] ?? null,
            'adresseSiege'           => $_POST['adresseSiege'] ?? null,
            'commune'                => $_POST['commune'] ?? null,
            'siteWeb'                => $_POST['siteWeb'] ?? null,
            'effectif'               => $_POST['effectif'] ?? null,
            'chiffreAffaires'        => $_POST['chiffreAffaires'] ?? null,
            'implantationTerritoire' => $_POST['implantationTerritoire'] ?? null,
            'implantationEnvisagee'  => $_POST['implantationEnvisagee'] ?? null,
            'nombreAssocies'         => $_POST['nombreAssocies'] ?? null,
            'nombreSalaries'         => $_POST['nombreSalaries'] ?? null,
            'statutJuridiqueS'       => $_POST['statutJuridiqueS'] ?? null,
            'statutJuridiqueE'       => $_POST['statutJuridiqueE'] ?? null,
            'referentNom'            => $_POST['referentNom'] ?? null,
            'referentFonction'       => $_POST['referentFonction'] ?? null,
            'referentEmail'          => $_POST['referentEmail'] ?? null,
            'referentTelephone'      => $_POST['referentTelephone'] ?? null,
        ];

        try {
            $this->utilisateurService->modifierProfil($donneesUtilisateur, $donneesStructure, $utilisateur->getIdUtilisateur());
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return $this->afficherTwig('utilisateur/formulaire.html.twig', [
                'utilisateur' => $utilisateur,
                'structure'   => $structure,
                'ancien'      => array_merge($donneesUtilisateur, $donneesStructure),
            ]);
        }

        MessageFlash::ajouter("success", "Vos informations ont été mises à jour.");
        return $this->rediriger("monEspace");
    }

    #[Route(path: '/deconnexion', name: 'deconnecter', methods: ['GET'])]
    public function deconnecter(): Response
    {
        if (!$this->connexionUtilisateur->estConnecte()) {
            MessageFlash::ajouter("danger", "Vous n'êtes pas connecté.");
            return $this->rediriger("accueil");
        }

        $this->connexionUtilisateur->deconnecter();
        $this->connexionUtilisateurJWT->deconnecter();
        MessageFlash::ajouter("success", "Vous avez été déconnecté.");
        return $this->rediriger("accueil");
    }
}