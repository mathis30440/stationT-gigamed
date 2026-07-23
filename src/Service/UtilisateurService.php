<?php

namespace App\Gigamed\Service;

use App\Gigamed\Lib\MotDePasse;
use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Modele\DataObject\StatutJuridiqueE;
use App\Gigamed\Modele\DataObject\StatutJuridiqueS;
use App\Gigamed\Modele\DataObject\Structure;
use App\Gigamed\Modele\DataObject\TypeStructure;
use App\Gigamed\Modele\DataObject\Utilisateur;
use App\Gigamed\Modele\Repository\StructureRepository;
use App\Gigamed\Modele\Repository\UtilisateurRepository;
use App\Gigamed\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class UtilisateurService implements UtilisateurServiceInterface
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private StructureRepository $structureRepository,
        private MailService $mailService,
    ) {
    }

    public function authentifier(?string $email, ?string $mdp): Utilisateur
    {
        if (is_null($email)) {
            throw new ServiceException("Email obligatoire", Response::HTTP_BAD_REQUEST);
        }
        if (is_null($mdp)) {
            throw new ServiceException("Mot de passe obligatoire", Response::HTTP_BAD_REQUEST);
        }

        
        $utilisateur = $this->utilisateurRepository->recupererParEmail($email);

        if ($utilisateur === null || !MotDePasse::verifier($mdp, $utilisateur->getMdpHache())) {
            throw new ServiceException("Identifiants invalides");
        }
        if (!$utilisateur->isEmailVerifie()) {
            throw new ServiceException("Veuillez vérifier votre adresse email avant de vous connecter. Consultez votre boîte mail.");
        }
        return $utilisateur;
    }

    public function verifierEmail(string $token): void
    {
        $utilisateur = $this->utilisateurRepository->recupererParToken($token);
        if ($utilisateur === null) {
            throw new ServiceException("Lien de vérification invalide ou expiré.", Response::HTTP_NOT_FOUND);
        }
        $utilisateur->setEmailVerifie(true);
        $utilisateur->setTokenVerification(null);
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    public function recupererUtilisateurOuNullParId(?int $idUtilisateur): ?Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($idUtilisateur);
        if ($utilisateur == null) {
            return null;
        }
        return $utilisateur;
    }

    public function recupererUtilisateurExistantParId(?int $idUtilisateur): AbstractDataObject
    {
        $utilisateur = $this->recupererUtilisateurOuNullParId($idUtilisateur);
        if ($utilisateur == null) {
            throw new ServiceException("Cet utilisateur n'existe pas!", Response::HTTP_NOT_FOUND);
        }
        return $utilisateur;
    }

    public function inscrire(array $donneesUtilisateur, array $donneesStructure): void
    {
        
        $nom        = $donneesUtilisateur['nom'] ?? null;
        $prenom     = $donneesUtilisateur['prenom'] ?? null;
        $email      = $donneesUtilisateur['email'] ?? null;
        $telephone  = $donneesUtilisateur['telephone'] ?? null;
        $statut     = $donneesUtilisateur['statut'] ?? null;
        $motDePasse = $donneesUtilisateur['motDePasse'] ?? null;

        if (is_null($nom))        throw new ServiceException("Le nom est obligatoire");
        if (is_null($prenom))     throw new ServiceException("Le prénom est obligatoire");
        if (is_null($email))      throw new ServiceException("L'adresse email est obligatoire");
        if (is_null($telephone))  throw new ServiceException("Le téléphone est obligatoire");
        if (is_null($statut))     throw new ServiceException("Le statut est obligatoire");
        if (is_null($motDePasse)) throw new ServiceException("Le mot de passe est obligatoire");

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("L'adresse email est incorrecte");
        }
        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $telephone))) {
            throw new ServiceException("Le numéro de téléphone est invalide");
        }
        if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$#", $motDePasse)) {
            throw new ServiceException("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre");
        }
        if ($this->utilisateurRepository->recupererParEmail($email) !== null) {
            throw new ServiceException("Un compte est déjà enregistré avec cette adresse email");
        }

        
        if (($donneesStructure['typeStructure'] ?? null) === 'porteur') {
            $token = bin2hex(random_bytes(32));
            $mdpHache = MotDePasse::hacher($motDePasse);
            $utilisateur = new Utilisateur(
                $nom, $prenom, $email, $telephone, $statut,
                $mdpHache, RoleUtilisateur::PORTEUR_DE_PROJET, null, false, $token
            );
            $this->utilisateurRepository->ajouter($utilisateur);
            $this->mailService->envoyerMailVerification($email, $prenom, $token);
            return;
        }

        
        $nomStructure     = $donneesStructure['nomStructure'] ?? null;
        $formeJuridique   = $donneesStructure['formeJuridique'] ?? null;
        $numSIRET_RNA     = $donneesStructure['numSIRET_RNA'] ?? null;
        $dateCreationStr  = $donneesStructure['dateCreation'] ?? null;
        $adresseSiege     = $donneesStructure['adresseSiege'] ?? null;
        $commune          = $donneesStructure['commune'] ?? null;
        $typeStructureVal = $donneesStructure['typeStructure'] ?? null;
        $referentNom      = $donneesStructure['referentNom'] ?? null;
        $referentFonction = $donneesStructure['referentFonction'] ?? null;
        $referentEmail    = $donneesStructure['referentEmail'] ?? null;
        $referentTel      = $donneesStructure['referentTelephone'] ?? null;

        
        if (is_null($nomStructure))     throw new ServiceException("Le nom de la structure est obligatoire");
        if (is_null($formeJuridique))   throw new ServiceException("La forme juridique est obligatoire");
        if (is_null($numSIRET_RNA))     throw new ServiceException("Le numéro SIRET/RNA est obligatoire");
        if (is_null($dateCreationStr))  throw new ServiceException("La date de création est obligatoire");
        if (is_null($adresseSiege))     throw new ServiceException("L'adresse du siège est obligatoire");
        if (is_null($commune))          throw new ServiceException("La commune est obligatoire");
        if (is_null($typeStructureVal)) throw new ServiceException("Le type de structure est obligatoire");
        if (is_null($referentNom))      throw new ServiceException("Le nom du référent est obligatoire");
        if (is_null($referentFonction)) throw new ServiceException("La fonction du référent est obligatoire");
        if (is_null($referentEmail))    throw new ServiceException("L'email du référent est obligatoire");
        if (is_null($referentTel))      throw new ServiceException("Le téléphone du référent est obligatoire");

        
        $siretRNA = preg_replace('/\s/', '', $numSIRET_RNA);
        if (!preg_match("#^\d{14}$#", $siretRNA) && !preg_match("#^W\w{9}$#i", $siretRNA)) {
            throw new ServiceException("Le numéro SIRET (14 chiffres) ou RNA (W + 9 caractères) est invalide");
        }

        
        if (!filter_var($referentEmail, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("L'adresse email du référent est incorrecte");
        }

        
        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $referentTel))) {
            throw new ServiceException("Le numéro de téléphone du référent est invalide");
        }

        
        $typeStructure = TypeStructure::tryFrom($typeStructureVal);
        if ($typeStructure === null) {
            throw new ServiceException("Type de structure invalide");
        }

        
        $dateCreation = \DateTime::createFromFormat('Y-m-d', $dateCreationStr);
        if ($dateCreation === false) {
            throw new ServiceException("Format de date de création invalide (attendu : AAAA-MM-JJ)");
        }
        if ($dateCreation > new \DateTime('today')) {
            throw new ServiceException("La date de création ne peut pas être dans le futur");
        }

        
        $siteWeb = $donneesStructure['siteWeb'] ?? null;
        if ($siteWeb !== null && $siteWeb !== '') {
            if (!filter_var($siteWeb, FILTER_VALIDATE_URL)) {
                throw new ServiceException("L'URL du site web est invalide");
            }
        } else {
            $siteWeb = null;
        }

        $effectif        = null;
        $chiffreAffaires = null;

        $nombreAssocies   = null;
        $nombreSalaries   = null;
        $implantationT    = null;
        $implantationE    = null;
        $statutJuridiqueS = null;
        $statutJuridiqueE = null;

        if ($typeStructure === TypeStructure::STARTUP) {
            
            $valS = $donneesStructure['statutJuridiqueS'] ?? '';
            if ($valS === '') {
                throw new ServiceException("Le statut actuel est obligatoire pour une startup");
            }
            $statutJuridiqueS = StatutJuridiqueS::tryFrom($valS);
            if ($statutJuridiqueS === null) {
                throw new ServiceException("Statut actuel invalide");
            }

            
            if (!isset($donneesStructure['nombreAssocies']) || $donneesStructure['nombreAssocies'] === '') {
                throw new ServiceException("Le nombre d'associés / fondateurs est obligatoire");
            }
            $nombreAssocies = (int) $donneesStructure['nombreAssocies'];
            if ($nombreAssocies < 0) {
                throw new ServiceException("Le nombre d'associés ne peut pas être négatif");
            }

            
            if (!isset($donneesStructure['nombreSalaries']) || $donneesStructure['nombreSalaries'] === '') {
                throw new ServiceException("Le nombre de salariés est obligatoire");
            }
            $nombreSalaries = (int) $donneesStructure['nombreSalaries'];
            if ($nombreSalaries < 0) {
                throw new ServiceException("Le nombre de salariés ne peut pas être négatif");
            }

            
            $implantationT = null;
            $implantationE = null;

        } else {
            
            $valE = $donneesStructure['statutJuridiqueE'] ?? '';
            if ($valE === '') {
                throw new ServiceException("La nature du candidat est obligatoire pour une entreprise");
            }
            $statutJuridiqueE = StatutJuridiqueE::tryFrom($valE);
            if ($statutJuridiqueE === null) {
                throw new ServiceException("Nature du candidat invalide");
            }

            
            if (!isset($donneesStructure['effectif']) || $donneesStructure['effectif'] === '') {
                throw new ServiceException("L'effectif est obligatoire pour une entreprise");
            }
            $effectif = (int) $donneesStructure['effectif'];
            if ($effectif < 0) {
                throw new ServiceException("L'effectif ne peut pas être négatif");
            }

            
            if (!isset($donneesStructure['chiffreAffaires']) || $donneesStructure['chiffreAffaires'] === '') {
                throw new ServiceException("Le chiffre d'affaires est obligatoire");
            }
            $chiffreAffaires = (float) $donneesStructure['chiffreAffaires'];
            if ($chiffreAffaires < 0) {
                throw new ServiceException("Le chiffre d'affaires ne peut pas être négatif");
            }

            
            $implantationT = isset($donneesStructure['implantationTerritoire']) && $donneesStructure['implantationTerritoire'] === '1';
            $implantationE = isset($donneesStructure['implantationEnvisagee'])  && $donneesStructure['implantationEnvisagee']  === '1';
        }

        
        $structure = new Structure(
            $nomStructure, $formeJuridique, $numSIRET_RNA, $dateCreation,
            $adresseSiege, $commune, $siteWeb, $effectif, $chiffreAffaires,
            $implantationT, $implantationE, $nombreAssocies, $nombreSalaries,
            $statutJuridiqueS, $statutJuridiqueE, $typeStructure,
            $referentNom, $referentFonction, $referentEmail, $referentTel
        );
        $this->structureRepository->ajouter($structure);

        
        $role = match ($typeStructure) {
            TypeStructure::STARTUP    => RoleUtilisateur::STARTUP,
            TypeStructure::ENTREPRISE => RoleUtilisateur::ENTREPRISE,
        };

        
        $token = bin2hex(random_bytes(32));
        $mdpHache = MotDePasse::hacher($motDePasse);
        $utilisateur = new Utilisateur(
            $nom, $prenom, $email, $telephone, $statut,
            $mdpHache, $role, $structure->getIdStructure(), false, $token
        );
        $this->utilisateurRepository->ajouter($utilisateur);
        $this->mailService->envoyerMailVerification($email, $prenom, $token);
    }

    public function recupererParEmail(string $email): ?Utilisateur
    {
        return $this->utilisateurRepository->recupererParEmail($email);
    }

    public function recupererTous(): array
    {
        return $this->utilisateurRepository->recuperer();
    }

    public function recupererParRole(RoleUtilisateur ...$roles): array
    {
        return $this->utilisateurRepository->recupererParRole(...$roles);
    }

    public function changerRole(int $idUtilisateur, string $role): void
    {
        $roleEnum = RoleUtilisateur::tryFrom($role);
        if ($roleEnum === null) {
            throw new ServiceException("Rôle invalide");
        }
        
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($idUtilisateur);
        if ($utilisateur === null) {
            throw new ServiceException("Utilisateur introuvable");
        }
        if (($roleEnum === RoleUtilisateur::STARTUP || $roleEnum === RoleUtilisateur::ENTREPRISE)
            && $utilisateur->getIdStructure() === null) {
            throw new ServiceException("Impossible d'assigner le rôle « $role » sans structure associée. Utilisez le formulaire de modification pour créer la structure en même temps.");
        }
        $utilisateur->setRoleUtilisateur($roleEnum);
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    public function supprimer(int $idUtilisateur): void
    {
        $this->utilisateurRepository->supprimer($idUtilisateur);
    }

    public function creerParAdmin(array $donnees, array $donneesStructure = []): void
    {
        $nom        = trim($donnees['nom'] ?? '');
        $prenom     = trim($donnees['prenom'] ?? '');
        $email      = trim($donnees['email'] ?? '');
        $telephone  = trim($donnees['telephone'] ?? '');
        $statut     = trim($donnees['statut'] ?? '');
        $roleVal    = trim($donnees['role'] ?? '');

        if ($nom === '')        throw new ServiceException("Le nom est obligatoire");
        if ($prenom === '')     throw new ServiceException("Le prénom est obligatoire");
        if ($email === '')      throw new ServiceException("L'adresse email est obligatoire");
        if ($telephone === '')  throw new ServiceException("Le téléphone est obligatoire");
        if ($statut === '')     throw new ServiceException("La fonction / statut est obligatoire");

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("L'adresse email est incorrecte");
        }
        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $telephone))) {
            throw new ServiceException("Le numéro de téléphone est invalide");
        }
        if ($this->utilisateurRepository->recupererParEmail($email) !== null) {
            throw new ServiceException("Un compte est déjà enregistré avec cette adresse email");
        }

        $role = RoleUtilisateur::tryFrom($roleVal);
        if ($role === null) throw new ServiceException("Rôle invalide");

        
        $idStructure = null;
        if ($role === RoleUtilisateur::STARTUP || $role === RoleUtilisateur::ENTREPRISE) {
            $typeVal = $role === RoleUtilisateur::STARTUP ? 'startup' : 'entreprise';

            $nomStructure     = trim($donneesStructure['nomStructure'] ?? '');
            $formeJuridique   = trim($donneesStructure['formeJuridique'] ?? '');
            $numSIRET_RNA     = trim($donneesStructure['numSIRET_RNA'] ?? '');
            $dateCreationStr  = trim($donneesStructure['dateCreation'] ?? '');
            $adresseSiege     = trim($donneesStructure['adresseSiege'] ?? '');
            $commune          = trim($donneesStructure['commune'] ?? '');
            $referentNom      = trim($donneesStructure['referentNom'] ?? '');
            $referentFonction = trim($donneesStructure['referentFonction'] ?? '');
            $referentEmail    = trim($donneesStructure['referentEmail'] ?? '');
            $referentTel      = trim($donneesStructure['referentTelephone'] ?? '');

            if ($nomStructure === '')     throw new ServiceException("Le nom de la structure est obligatoire");
            if ($formeJuridique === '')   throw new ServiceException("La forme juridique est obligatoire");
            if ($numSIRET_RNA === '')     throw new ServiceException("Le numéro SIRET/RNA est obligatoire");
            if ($dateCreationStr === '')  throw new ServiceException("La date de création est obligatoire");
            if ($adresseSiege === '')     throw new ServiceException("L'adresse du siège est obligatoire");
            if ($commune === '')         throw new ServiceException("La commune est obligatoire");
            if ($referentNom === '')     throw new ServiceException("Le nom du référent est obligatoire");
            if ($referentFonction === '') throw new ServiceException("La fonction du référent est obligatoire");
            if ($referentEmail === '')   throw new ServiceException("L'email du référent est obligatoire");
            if ($referentTel === '')     throw new ServiceException("Le téléphone du référent est obligatoire");

            $siretRNA = preg_replace('/\s/', '', $numSIRET_RNA);
            if (!preg_match("#^\d{14}$#", $siretRNA) && !preg_match("#^W\w{9}$#i", $siretRNA)) {
                throw new ServiceException("Le numéro SIRET (14 chiffres) ou RNA (W + 9 caractères) est invalide");
            }
            if (!filter_var($referentEmail, FILTER_VALIDATE_EMAIL)) {
                throw new ServiceException("L'adresse email du référent est incorrecte");
            }
            if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $referentTel))) {
                throw new ServiceException("Le téléphone du référent est invalide");
            }

            $dateCreation = \DateTime::createFromFormat('Y-m-d', $dateCreationStr);
            if ($dateCreation === false) throw new ServiceException("Format de date de création invalide");
            if ($dateCreation > new \DateTime('today')) throw new ServiceException("La date de création ne peut pas être dans le futur");

            $siteWeb = trim($donneesStructure['siteWeb'] ?? '');
            if ($siteWeb !== '' && !filter_var($siteWeb, FILTER_VALIDATE_URL)) {
                throw new ServiceException("L'URL du site web est invalide");
            }

            $typeStructure = TypeStructure::from($typeVal);

            $statutJuridiqueS = null;
            $statutJuridiqueE = null;
            $nombreAssocies   = null;
            $nombreSalaries   = null;
            $effectif         = null;
            $chiffreAffaires  = null;
            $implantationT    = null;
            $implantationE    = null;

            if ($typeStructure === TypeStructure::STARTUP) {
                $sjs = StatutJuridiqueS::tryFrom(trim($donneesStructure['statutJuridiqueS'] ?? ''));
                if ($sjs === null) throw new ServiceException("Statut actuel invalide");
                $statutJuridiqueS = $sjs;
                $nombreAssocies = (int)($donneesStructure['nombreAssocies'] ?? 0);
                $nombreSalaries = (int)($donneesStructure['nombreSalaries'] ?? 0);
            } else {
                $sje = StatutJuridiqueE::tryFrom(trim($donneesStructure['statutJuridiqueE'] ?? ''));
                if ($sje === null) throw new ServiceException("Nature du candidat invalide");
                $statutJuridiqueE = $sje;
                $effectif        = (int)($donneesStructure['effectif'] ?? 0);
                $chiffreAffaires = (float)($donneesStructure['chiffreAffaires'] ?? 0);
                $implantationT   = ($donneesStructure['implantationTerritoire'] ?? '') === '1';
                $implantationE   = ($donneesStructure['implantationEnvisagee'] ?? '') === '1';
            }

            $structure = new Structure(
                $nomStructure, $formeJuridique, $numSIRET_RNA, $dateCreation,
                $adresseSiege, $commune, $siteWeb ?: null, $effectif, $chiffreAffaires,
                $implantationT, $implantationE, $nombreAssocies, $nombreSalaries,
                $statutJuridiqueS, $statutJuridiqueE, $typeStructure,
                $referentNom, $referentFonction, $referentEmail, $referentTel
            );
            $this->structureRepository->ajouter($structure);
            $idStructure = $structure->getIdStructure();
        }

        $token    = bin2hex(random_bytes(32));
        $mdpHache = MotDePasse::hacher(bin2hex(random_bytes(16)));
        $utilisateur = new Utilisateur(
            $nom, $prenom, $email, $telephone, $statut,
            $mdpHache, $role, $idStructure, false, $token
        );
        $this->utilisateurRepository->ajouter($utilisateur);
        try {
            $this->mailService->envoyerMailCreationCompte($email, $prenom, $token);
        } catch (\Exception) {}
    }

    public function choisirMotDePasse(string $token, string $motDePasse): void
    {
        $utilisateur = $this->utilisateurRepository->recupererParToken($token);
        if ($utilisateur === null) {
            throw new ServiceException("Lien invalide ou expiré.");
        }
        if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$#", $motDePasse)) {
            throw new ServiceException("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.");
        }
        $utilisateur->setMdpHache(MotDePasse::hacher($motDePasse));
        $utilisateur->setTokenVerification(null);
        $utilisateur->setEmailVerifie(true);
        $this->utilisateurRepository->mettreAJour($utilisateur);
    }

    public function recupererStructure(int $idStructure): ?\App\Gigamed\Modele\DataObject\Structure
    {
        return $this->structureRepository->recupererParClePrimaire($idStructure);
    }

    public function modifierProfil(array $donneesUtilisateur, array $donneesStructure, int $idUtilisateur): void
    {
        
        $nom    = trim($donneesUtilisateur['nom'] ?? '');
        $prenom = trim($donneesUtilisateur['prenom'] ?? '');
        $tel    = trim($donneesUtilisateur['telephone'] ?? '');
        $statut = trim($donneesUtilisateur['statut'] ?? '');

        if ($nom === '')    throw new ServiceException("Le nom est obligatoire");
        if ($prenom === '') throw new ServiceException("Le prénom est obligatoire");
        if ($tel === '')    throw new ServiceException("Le téléphone est obligatoire");
        if ($statut === '') throw new ServiceException("Le statut est obligatoire");

        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $tel))) {
            throw new ServiceException("Le numéro de téléphone est invalide (format français attendu)");
        }

        
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($idUtilisateur);
        $utilisateur->setNomUtilisateur($nom);
        $utilisateur->setPrenomUtilisateur($prenom);
        $utilisateur->setTelephoneUtilisateur($tel);
        $utilisateur->setStatutUtilisateur($statut);
        $this->utilisateurRepository->mettreAJour($utilisateur);

        
        if (empty($donneesStructure)) return;

        $idStructure = $donneesStructure['idStructure'] ?? null;
        if ($idStructure === null) return;

        
        $structure = $this->structureRepository->recupererParClePrimaire((int) $idStructure);
        if ($structure === null) return;

        
        $champsObligatoires = ['nomStructure', 'formeJuridique', 'numSIRET_RNA', 'dateCreation',
                               'adresseSiege', 'commune', 'referentNom', 'referentFonction',
                               'referentEmail', 'referentTelephone'];
        foreach ($champsObligatoires as $champ) {
            if (empty(trim($donneesStructure[$champ] ?? ''))) {
                throw new ServiceException("Tous les champs obligatoires de la structure doivent être remplis");
            }
        }

        $siretRNA = preg_replace('/\s/', '', trim($donneesStructure['numSIRET_RNA']));
        if (!preg_match("#^\d{14}$#", $siretRNA) && !preg_match("#^W\w{9}$#i", $siretRNA)) {
            throw new ServiceException("Le numéro SIRET (14 chiffres) ou RNA (W + 9 caractères) est invalide");
        }

        $dateCreation = \DateTime::createFromFormat('Y-m-d', trim($donneesStructure['dateCreation']));
        if ($dateCreation === false) {
            throw new ServiceException("Format de date de création invalide");
        }
        if ($dateCreation > new \DateTime('today')) {
            throw new ServiceException("La date de création ne peut pas être dans le futur");
        }

        $siteWeb = trim($donneesStructure['siteWeb'] ?? '');
        if ($siteWeb !== '' && !filter_var($siteWeb, FILTER_VALIDATE_URL)) {
            throw new ServiceException("L'URL du site web est invalide");
        }

        if (!filter_var(trim($donneesStructure['referentEmail']), FILTER_VALIDATE_EMAIL)) {
            throw new ServiceException("L'adresse email du référent est incorrecte");
        }

        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', trim($donneesStructure['referentTelephone'])))) {
            throw new ServiceException("Le numéro de téléphone du référent est invalide");
        }

        
        $typeInjecte = $donneesStructure['typeStructure'] ?? null;
        $typeStructureEnum = ($typeInjecte !== null)
            ? (TypeStructure::tryFrom($typeInjecte) ?? $structure->getTypeStructure())
            : $structure->getTypeStructure();
        $type = $typeStructureEnum->value;

        if ($type === 'startup') {
            $sjs = StatutJuridiqueS::tryFrom(trim($donneesStructure['statutJuridiqueS'] ?? ''));
            if ($sjs === null) throw new ServiceException("Veuillez sélectionner un statut juridique valide");

            if (!isset($donneesStructure['nombreAssocies']) || $donneesStructure['nombreAssocies'] === '') {
                throw new ServiceException("Le nombre d'associés / fondateurs est obligatoire");
            }
            if ((int)$donneesStructure['nombreAssocies'] < 0) {
                throw new ServiceException("Le nombre d'associés ne peut pas être négatif");
            }
            if (!isset($donneesStructure['nombreSalaries']) || $donneesStructure['nombreSalaries'] === '') {
                throw new ServiceException("Le nombre de salariés est obligatoire");
            }
            if ((int)$donneesStructure['nombreSalaries'] < 0) {
                throw new ServiceException("Le nombre de salariés ne peut pas être négatif");
            }

            $structure->setStatutJuridiqueS($sjs);
            $structure->setNombreAssocies((int)$donneesStructure['nombreAssocies']);
            $structure->setNombreSalaries((int)$donneesStructure['nombreSalaries']);
            $structure->setStatutJuridiqueE(null);
            $structure->setEffectif(null);
            $structure->setChiffreAffaires(null);
            $structure->setImplantationTerritoire(null);
            $structure->setImplantationEnvisagee(null);

        } elseif ($type === 'entreprise') {
            $sje = StatutJuridiqueE::tryFrom(trim($donneesStructure['statutJuridiqueE'] ?? ''));
            if ($sje === null) throw new ServiceException("Veuillez sélectionner une nature de candidat valide");

            if (!isset($donneesStructure['effectif']) || $donneesStructure['effectif'] === '') {
                throw new ServiceException("L'effectif est obligatoire");
            }
            if ((int)$donneesStructure['effectif'] < 0) {
                throw new ServiceException("L'effectif ne peut pas être négatif");
            }
            if (!isset($donneesStructure['chiffreAffaires']) || $donneesStructure['chiffreAffaires'] === '') {
                throw new ServiceException("Le chiffre d'affaires est obligatoire");
            }
            if ((float)$donneesStructure['chiffreAffaires'] < 0) {
                throw new ServiceException("Le chiffre d'affaires ne peut pas être négatif");
            }

            $structure->setStatutJuridiqueE($sje);
            $structure->setEffectif((int)$donneesStructure['effectif']);
            $structure->setChiffreAffaires((float)$donneesStructure['chiffreAffaires']);
            $structure->setImplantationTerritoire(($donneesStructure['implantationTerritoire'] ?? '') === '1');
            $structure->setImplantationEnvisagee(($donneesStructure['implantationEnvisagee'] ?? '') === '1');
            $structure->setStatutJuridiqueS(null);
            $structure->setNombreAssocies(null);
            $structure->setNombreSalaries(null);
        }

        $structure->setTypeStructure($typeStructureEnum);
        $structure->setNomStructure(trim($donneesStructure['nomStructure']));
        $structure->setFormeJuridique(trim($donneesStructure['formeJuridique']));
        $structure->setNumSIRET_RNA(trim($donneesStructure['numSIRET_RNA']));
        $structure->setDateCreation($dateCreation);
        $structure->setAdresseSiege(trim($donneesStructure['adresseSiege']));
        $structure->setCommune(trim($donneesStructure['commune']));
        $structure->setSiteWeb($siteWeb ?: null);
        $structure->setReferentNom(trim($donneesStructure['referentNom']));
        $structure->setReferentFonction(trim($donneesStructure['referentFonction']));
        $structure->setReferentEmail(trim($donneesStructure['referentEmail']));
        $structure->setReferentTelephone(trim($donneesStructure['referentTelephone']));
        $this->structureRepository->mettreAJour($structure);
    }

    public function modifierParAdmin(array $donneesUtilisateur, array $donneesStructure, int $idUtilisateur): void
    {
        
        $nom    = trim($donneesUtilisateur['nom'] ?? '');
        $prenom = trim($donneesUtilisateur['prenom'] ?? '');
        $tel    = trim($donneesUtilisateur['telephone'] ?? '');
        $statut = trim($donneesUtilisateur['statut'] ?? '');

        if ($nom === '')    throw new ServiceException("Le nom est obligatoire");
        if ($prenom === '') throw new ServiceException("Le prénom est obligatoire");
        if ($tel === '')    throw new ServiceException("Le téléphone est obligatoire");
        if ($statut === '') throw new ServiceException("Le statut est obligatoire");

        if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $tel))) {
            throw new ServiceException("Le numéro de téléphone est invalide");
        }

        
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($idUtilisateur);
        if ($utilisateur === null) throw new ServiceException("Utilisateur introuvable");

        
        $roleVal = trim($donneesUtilisateur['role'] ?? '');
        if ($roleVal !== '') {
            $newRole = RoleUtilisateur::tryFrom($roleVal);
            if ($newRole === null) throw new ServiceException("Rôle invalide");
            $utilisateur->setRoleUtilisateur($newRole);
        }

        $utilisateur->setNomUtilisateur($nom);
        $utilisateur->setPrenomUtilisateur($prenom);
        $utilisateur->setTelephoneUtilisateur($tel);
        $utilisateur->setStatutUtilisateur($statut);
        

        
        
        $role = $utilisateur->getRoleUtilisateur();
        $needsStructure = ($role === RoleUtilisateur::STARTUP || $role === RoleUtilisateur::ENTREPRISE);

        if ($needsStructure && $utilisateur->getIdStructure() !== null) {
            
            $donneesStructure['idStructure'] = $utilisateur->getIdStructure();
            
            $donneesStructure['typeStructure'] = ($role === RoleUtilisateur::STARTUP) ? 'startup' : 'entreprise';
            $this->modifierProfil(
                ['nom' => $nom, 'prenom' => $prenom, 'telephone' => $tel, 'statut' => $statut],
                $donneesStructure,
                $idUtilisateur
            );
            
            $this->utilisateurRepository->mettreAJour($utilisateur);
        } elseif ($needsStructure && !empty(array_filter($donneesStructure, fn($v) => $v !== null && $v !== ''))) {
            
            $typeVal   = $role === RoleUtilisateur::STARTUP ? 'startup' : 'entreprise';
            $typeStructure = TypeStructure::from($typeVal);

            $nomStructure     = trim($donneesStructure['nomStructure'] ?? '');
            $formeJuridique   = trim($donneesStructure['formeJuridique'] ?? '');
            $numSIRET_RNA     = trim($donneesStructure['numSIRET_RNA'] ?? '');
            $dateCreationStr  = trim($donneesStructure['dateCreation'] ?? '');
            $adresseSiege     = trim($donneesStructure['adresseSiege'] ?? '');
            $commune          = trim($donneesStructure['commune'] ?? '');
            $referentNom      = trim($donneesStructure['referentNom'] ?? '');
            $referentFonction = trim($donneesStructure['referentFonction'] ?? '');
            $referentEmail    = trim($donneesStructure['referentEmail'] ?? '');
            $referentTel      = trim($donneesStructure['referentTelephone'] ?? '');

            if ($nomStructure === '')     throw new ServiceException("Le nom de la structure est obligatoire");
            if ($formeJuridique === '')   throw new ServiceException("La forme juridique est obligatoire");
            if ($numSIRET_RNA === '')     throw new ServiceException("Le numéro SIRET/RNA est obligatoire");
            if ($dateCreationStr === '')  throw new ServiceException("La date de création est obligatoire");
            if ($adresseSiege === '')     throw new ServiceException("L'adresse du siège est obligatoire");
            if ($commune === '')         throw new ServiceException("La commune est obligatoire");
            if ($referentNom === '')     throw new ServiceException("Le nom du référent est obligatoire");
            if ($referentFonction === '') throw new ServiceException("La fonction du référent est obligatoire");
            if ($referentEmail === '')   throw new ServiceException("L'email du référent est obligatoire");
            if ($referentTel === '')     throw new ServiceException("Le téléphone du référent est obligatoire");

            $siretRNA = preg_replace('/\s/', '', $numSIRET_RNA);
            if (!preg_match("#^\d{14}$#", $siretRNA) && !preg_match("#^W\w{9}$#i", $siretRNA)) {
                throw new ServiceException("Le numéro SIRET (14 chiffres) ou RNA (W + 9 caractères) est invalide");
            }
            if (!filter_var($referentEmail, FILTER_VALIDATE_EMAIL)) {
                throw new ServiceException("L'adresse email du référent est incorrecte");
            }
            if (!preg_match("#^(\+33|0033|0)[1-9](\d{2}){4}$#", preg_replace('/[\s.\-]/', '', $referentTel))) {
                throw new ServiceException("Le téléphone du référent est invalide");
            }

            $dateCreation = \DateTime::createFromFormat('Y-m-d', $dateCreationStr);
            if ($dateCreation === false) throw new ServiceException("Format de date de création invalide");
            if ($dateCreation > new \DateTime('today')) throw new ServiceException("La date de création ne peut pas être dans le futur");

            $siteWeb = trim($donneesStructure['siteWeb'] ?? '');
            if ($siteWeb !== '' && !filter_var($siteWeb, FILTER_VALIDATE_URL)) {
                throw new ServiceException("L'URL du site web est invalide");
            }

            $statutJuridiqueS = null;
            $statutJuridiqueE = null;
            $nombreAssocies   = null;
            $nombreSalaries   = null;
            $effectif         = null;
            $chiffreAffaires  = null;
            $implantationT    = null;
            $implantationE    = null;

            if ($typeStructure === TypeStructure::STARTUP) {
                $sjs = StatutJuridiqueS::tryFrom(trim($donneesStructure['statutJuridiqueS'] ?? ''));
                if ($sjs === null) throw new ServiceException("Statut actuel invalide");
                $statutJuridiqueS = $sjs;
                $nombreAssocies = (int)($donneesStructure['nombreAssocies'] ?? 0);
                $nombreSalaries = (int)($donneesStructure['nombreSalaries'] ?? 0);
            } else {
                $sje = StatutJuridiqueE::tryFrom(trim($donneesStructure['statutJuridiqueE'] ?? ''));
                if ($sje === null) throw new ServiceException("Nature du candidat invalide");
                $statutJuridiqueE = $sje;
                $effectif        = (int)($donneesStructure['effectif'] ?? 0);
                $chiffreAffaires = (float)($donneesStructure['chiffreAffaires'] ?? 0);
                $implantationT   = ($donneesStructure['implantationTerritoire'] ?? '') === '1';
                $implantationE   = ($donneesStructure['implantationEnvisagee'] ?? '') === '1';
            }

            $structure = new Structure(
                $nomStructure, $formeJuridique, $numSIRET_RNA, $dateCreation,
                $adresseSiege, $commune, $siteWeb ?: null, $effectif, $chiffreAffaires,
                $implantationT, $implantationE, $nombreAssocies, $nombreSalaries,
                $statutJuridiqueS, $statutJuridiqueE, $typeStructure,
                $referentNom, $referentFonction, $referentEmail, $referentTel
            );
            $this->structureRepository->ajouter($structure);

            $utilisateur->setIdStructure($structure->getIdStructure());
            $this->utilisateurRepository->mettreAJour($utilisateur);
        } else {
            
            if ($utilisateur->getIdStructure() !== null) {
                $utilisateur->setIdStructure(null);
            }
            $this->utilisateurRepository->mettreAJour($utilisateur);
        }
    }
}