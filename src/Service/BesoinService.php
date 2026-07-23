<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Ami;
use App\Gigamed\Modele\DataObject\Besoin;
use App\Gigamed\Modele\DataObject\Challenge;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutAmi;
use App\Gigamed\Modele\DataObject\StatutBesoin;
use App\Gigamed\Modele\DataObject\StatutChallenge;
use App\Gigamed\Modele\DataObject\TypeChallenge;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Modele\Repository\AmiRepository;
use App\Gigamed\Modele\Repository\BesoinRepository;
use App\Gigamed\Modele\Repository\CandidatureRepository;
use App\Gigamed\Modele\Repository\ChallengeRepository;
use App\Gigamed\Modele\Repository\UtilisateurRepository;
use App\Gigamed\Service\AmiService;
use App\Gigamed\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Response;

class BesoinService implements BesoinServiceInterface
{
    public function __construct(
        private BesoinRepository $besoinRepository,
        private CandidatureRepository $candidatureRepository,
        private ChallengeRepository $challengeRepository,
        private AmiRepository $amiRepository,
        private UtilisateurRepository $utilisateurRepository,
        private MailService $mailService,
    ) {
    }

    public function recupererParId(int $id): ?\App\Gigamed\Modele\DataObject\Besoin
    {
        return $this->besoinRepository->recupererParClePrimaire($id);
    }

    public function recupererParIdUtilisateur(int $idUtilisateur): array
    {
        return $this->besoinRepository->recupererParIdUtilisateur($idUtilisateur);
    }

    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idBesoin = null): void
    {
        $titreBesoin          = trim($donnees['titreBesoin'] ?? '');
        $filiereVal           = trim($donnees['filiere'] ?? '');
        $objectifBesoin       = trim($donnees['objectifBesoin'] ?? '');
        $publicVise           = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $vision               = trim($donnees['vision'] ?? '');
        $enjeuxMajeurs        = trim($donnees['enjeuxMajeurs'] ?? '');
        $besoinsPrioritaires  = trim($donnees['besoinsPrioritaires'] ?? '');
        $exemplesInnovations  = trim($donnees['exemplesInnovations'] ?? '');
        $partenaires          = trim($donnees['partenaires'] ?? '');

        if ($titreBesoin === '') throw new ServiceException("Le titre est obligatoire pour sauvegarder un brouillon");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("La filière est obligatoire pour sauvegarder un brouillon");

        $brouillon = $idBesoin !== null ? $this->besoinRepository->recupererParClePrimaire($idBesoin) : null;
        if ($brouillon !== null && $brouillon->getIdUtilisateur() === $idUtilisateur) {
            $brouillon->setTitreBesoin($titreBesoin);
            $brouillon->setFiliere($filiere);
            $brouillon->setObjectifBesoin($objectifBesoin);
            $brouillon->setPublicVise($publicVise);
            $brouillon->setPerimetrePrioritaire($perimetrePrioritaire);
            $brouillon->setVision($vision);
            $brouillon->setEnjeuxMajeurs($enjeuxMajeurs);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setExemplesInnovations($exemplesInnovations);
            $brouillon->setPartenaires($partenaires);
            $this->besoinRepository->mettreAJour($brouillon);
        } else {
            $besoin = new Besoin(
                $titreBesoin, $filiere, $objectifBesoin, $publicVise,
                $perimetrePrioritaire, $vision, $enjeuxMajeurs, $besoinsPrioritaires,
                $exemplesInnovations, $partenaires, new \DateTime(), StatutBesoin::BROUILLON, $idUtilisateur,
            );
            $this->besoinRepository->ajouter($besoin);
        }
    }

    public function recupererBrouillonParIdUtilisateur(int $idUtilisateur): ?\App\Gigamed\Modele\DataObject\Besoin
    {
        return $this->besoinRepository->recupererBrouillonParIdUtilisateur($idUtilisateur);
    }

    public function marquerTransformeEnChallenge(int $idBesoin): void
    {
        $besoin = $this->besoinRepository->recupererParClePrimaire($idBesoin);
        if ($besoin === null) return;
        $besoin->setStatutBesoin(StatutBesoin::TRANSFORMEC);
        $this->besoinRepository->mettreAJour($besoin);
    }

    public function marquerTransformeEnAmi(int $idBesoin): void
    {
        $besoin = $this->besoinRepository->recupererParClePrimaire($idBesoin);
        if ($besoin === null) return;
        $besoin->setStatutBesoin(StatutBesoin::TRANSFORMEA);
        $this->besoinRepository->mettreAJour($besoin);
    }

    public function soumettre(array $donnees, int $idUtilisateur, ?int $idBesoin = null): void
    {
        if ($this->candidatureRepository->existeParIdUtilisateur($idUtilisateur)) {
            throw new ServiceException(
                "Vous avez déjà déposé une candidature. Vous ne pouvez pas soumettre un besoin.",
                Response::HTTP_FORBIDDEN
            );
        }

        $titreBesoin          = trim($donnees['titreBesoin'] ?? '');
        $filiereVal           = trim($donnees['filiere'] ?? '');
        $objectifBesoin       = trim($donnees['objectifBesoin'] ?? '');
        $publicVise           = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $vision               = trim($donnees['vision'] ?? '');
        $enjeuxMajeurs        = trim($donnees['enjeuxMajeurs'] ?? '');
        $besoinsPrioritaires  = trim($donnees['besoinsPrioritaires'] ?? '');
        $exemplesInnovations  = trim($donnees['exemplesInnovations'] ?? '');
        $partenaires          = trim($donnees['partenaires'] ?? '');

        if ($titreBesoin === '')          throw new ServiceException("Le titre du besoin est obligatoire");
        if ($filiereVal === '')           throw new ServiceException("La filière est obligatoire");
        if ($objectifBesoin === '')       throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')           throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre prioritaire est obligatoire");
        if ($vision === '')               throw new ServiceException("La vision est obligatoire");
        if ($enjeuxMajeurs === '')        throw new ServiceException("Les enjeux majeurs sont obligatoires");
        if ($besoinsPrioritaires === '')  throw new ServiceException("Les besoins prioritaires sont obligatoires");
        if ($exemplesInnovations === '')  throw new ServiceException("Les exemples d'innovations sont obligatoires");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) {
            throw new ServiceException("La filière sélectionnée est invalide");
        }

        
        $brouillon = $idBesoin !== null ? $this->besoinRepository->recupererParClePrimaire($idBesoin) : null;
        if ($brouillon !== null && $brouillon->getIdUtilisateur() === $idUtilisateur) {
            $brouillon->setTitreBesoin($titreBesoin);
            $brouillon->setFiliere($filiere);
            $brouillon->setObjectifBesoin($objectifBesoin);
            $brouillon->setPublicVise($publicVise);
            $brouillon->setPerimetrePrioritaire($perimetrePrioritaire);
            $brouillon->setVision($vision);
            $brouillon->setEnjeuxMajeurs($enjeuxMajeurs);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setExemplesInnovations($exemplesInnovations);
            $brouillon->setPartenaires($partenaires);
            $brouillon->setStatutBesoin(StatutBesoin::ATTENTE);
            $this->besoinRepository->mettreAJour($brouillon);
            $besoin = $brouillon;
        } else {
            $besoin = new Besoin(
                $titreBesoin,
                $filiere,
                $objectifBesoin,
                $publicVise,
                $perimetrePrioritaire,
                $vision,
                $enjeuxMajeurs,
                $besoinsPrioritaires,
                $exemplesInnovations,
                $partenaires,
                new \DateTime(),
                StatutBesoin::ATTENTE,
                $idUtilisateur,
            );
            $this->besoinRepository->ajouter($besoin);
        }

        $soumetteur = $this->utilisateurRepository->recupererParClePrimaire($idUtilisateur);
        $nomEntreprise = trim(($soumetteur?->getNomUtilisateur() ?? '') . ' ' . ($soumetteur?->getPrenomUtilisateur() ?? ''));

        
        $admins = $this->utilisateurRepository->recupererParRole(RoleUtilisateur::ADMIN, RoleUtilisateur::SUPER_ADMIN);
        foreach ($admins as $admin) {
            try {
                $this->mailService->envoyerMailNouveauBesoin(
                    $admin->getEmailUtilisateur(),
                    $titreBesoin,
                    $filiere->value,
                    $nomEntreprise
                );
            } catch (\Exception) {}
        }

        
        if ($soumetteur !== null) {
            try {
                $this->mailService->envoyerMailBesoinRecu(
                    $soumetteur->getEmailUtilisateur(),
                    $soumetteur->getPrenomUtilisateur(),
                    $titreBesoin
                );
            } catch (\Exception) {}
        }
    }

    public function soumettreParAdmin(array $donnees, int $idUtilisateur, int $idAdmin = 0, ?int $idBesoin = null): void
    {
        
        if ($idUtilisateur <= 0) {
            $idUtilisateur = $idAdmin;
        }
        
        $titreBesoin          = trim($donnees['titreBesoin'] ?? '');
        $filiereVal           = trim($donnees['filiere'] ?? '');
        $objectifBesoin       = trim($donnees['objectifBesoin'] ?? '');
        $publicVise           = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $vision               = trim($donnees['vision'] ?? '');
        $enjeuxMajeurs        = trim($donnees['enjeuxMajeurs'] ?? '');
        $besoinsPrioritaires  = trim($donnees['besoinsPrioritaires'] ?? '');
        $exemplesInnovations  = trim($donnees['exemplesInnovations'] ?? '');
        $partenaires          = trim($donnees['partenaires'] ?? '');

        if ($titreBesoin === '')          throw new ServiceException("Le titre du besoin est obligatoire");
        if ($filiereVal === '')           throw new ServiceException("La filière est obligatoire");
        if ($objectifBesoin === '')       throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')           throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre prioritaire est obligatoire");
        if ($vision === '')               throw new ServiceException("La vision est obligatoire");
        if ($enjeuxMajeurs === '')        throw new ServiceException("Les enjeux majeurs sont obligatoires");
        if ($besoinsPrioritaires === '')  throw new ServiceException("Les besoins prioritaires sont obligatoires");
        if ($exemplesInnovations === '')  throw new ServiceException("Les exemples d'innovations sont obligatoires");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("La filière sélectionnée est invalide");

        
        $brouillon = $idBesoin !== null ? $this->besoinRepository->recupererParClePrimaire($idBesoin) : null;
        if ($brouillon !== null) {
            $brouillon->setTitreBesoin($titreBesoin);
            $brouillon->setFiliere($filiere);
            $brouillon->setObjectifBesoin($objectifBesoin);
            $brouillon->setPublicVise($publicVise);
            $brouillon->setPerimetrePrioritaire($perimetrePrioritaire);
            $brouillon->setVision($vision);
            $brouillon->setEnjeuxMajeurs($enjeuxMajeurs);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setExemplesInnovations($exemplesInnovations);
            $brouillon->setPartenaires($partenaires);
            $brouillon->setIdUtilisateur($idUtilisateur);
            $brouillon->setStatutBesoin(StatutBesoin::ATTENTE);
            $brouillon->setDateDepot(new \DateTime());
            $this->besoinRepository->mettreAJour($brouillon);
        } else {
            $besoin = new Besoin(
                $titreBesoin, $filiere, $objectifBesoin, $publicVise, $perimetrePrioritaire,
                $vision, $enjeuxMajeurs, $besoinsPrioritaires, $exemplesInnovations,
                $partenaires, new \DateTime(), StatutBesoin::ATTENTE, $idUtilisateur,
            );
            $this->besoinRepository->ajouter($besoin);
        }
    }

    public function sauvegarderBrouillonParAdmin(array $donnees, int $idAdmin, ?int $idBesoin = null): void
    {
        $titreBesoin          = trim($donnees['titreBesoin'] ?? '');
        $filiereVal           = trim($donnees['filiere'] ?? '');
        $objectifBesoin       = trim($donnees['objectifBesoin'] ?? '');
        $publicVise           = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $vision               = trim($donnees['vision'] ?? '');
        $enjeuxMajeurs        = trim($donnees['enjeuxMajeurs'] ?? '');
        $besoinsPrioritaires  = trim($donnees['besoinsPrioritaires'] ?? '');
        $exemplesInnovations  = trim($donnees['exemplesInnovations'] ?? '');
        $partenaires          = trim($donnees['partenaires'] ?? '');

        if ($titreBesoin === '') throw new ServiceException("Le titre est obligatoire pour sauvegarder un brouillon");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("La filière est obligatoire pour sauvegarder un brouillon");

        $brouillon = $idBesoin !== null
            ? $this->besoinRepository->recupererParClePrimaire($idBesoin)
            : null;
        if ($brouillon !== null) {
            $brouillon->setTitreBesoin($titreBesoin);
            $brouillon->setFiliere($filiere);
            $brouillon->setObjectifBesoin($objectifBesoin);
            $brouillon->setPublicVise($publicVise);
            $brouillon->setPerimetrePrioritaire($perimetrePrioritaire);
            $brouillon->setVision($vision);
            $brouillon->setEnjeuxMajeurs($enjeuxMajeurs);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setExemplesInnovations($exemplesInnovations);
            $brouillon->setPartenaires($partenaires);
            $this->besoinRepository->mettreAJour($brouillon);
        } else {
            $besoin = new Besoin(
                $titreBesoin, $filiere, $objectifBesoin, $publicVise,
                $perimetrePrioritaire, $vision, $enjeuxMajeurs, $besoinsPrioritaires,
                $exemplesInnovations, $partenaires, new \DateTime(), StatutBesoin::BROUILLON, $idAdmin,
            );
            $this->besoinRepository->ajouter($besoin);
        }
    }

    public function recupererBrouillonParAdmin(int $idAdmin): ?\App\Gigamed\Modele\DataObject\Besoin
    {
        return $this->besoinRepository->recupererBrouillonParIdUtilisateur($idAdmin);
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        return $this->besoinRepository->recupererPourAdmin($idAdmin);
    }

    public function supprimer(int $id): void
    {
        $besoin = $this->besoinRepository->recupererParClePrimaire($id);
        if ($besoin === null) {
            throw new ServiceException("Besoin introuvable");
        }
        $this->besoinRepository->supprimer($id);
    }

    public function modifier(int $id, array $donnees): void
    {
        
        $besoin = $this->besoinRepository->recupererParClePrimaire($id);
        if ($besoin === null) throw new ServiceException("Besoin introuvable");

        $titreBesoin          = trim($donnees['titreBesoin'] ?? '');
        $filiereVal           = trim($donnees['filiere'] ?? '');
        $objectifBesoin       = trim($donnees['objectifBesoin'] ?? '');
        $publicVise           = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $vision               = trim($donnees['vision'] ?? '');
        $enjeuxMajeurs        = trim($donnees['enjeuxMajeurs'] ?? '');
        $besoinsPrioritaires  = trim($donnees['besoinsPrioritaires'] ?? '');
        $exemplesInnovations  = trim($donnees['exemplesInnovations'] ?? '');
        $partenaires          = trim($donnees['partenaires'] ?? '');
        $statutVal            = trim($donnees['statutBesoin'] ?? '');

        if ($titreBesoin === '')          throw new ServiceException("Le titre est obligatoire");
        if ($objectifBesoin === '')       throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')           throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre est obligatoire");
        if ($vision === '')               throw new ServiceException("La vision est obligatoire");
        if ($enjeuxMajeurs === '')        throw new ServiceException("Les enjeux majeurs sont obligatoires");
        if ($besoinsPrioritaires === '')  throw new ServiceException("Les besoins prioritaires sont obligatoires");
        if ($exemplesInnovations === '')  throw new ServiceException("Les exemples d'innovations sont obligatoires");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("Filière invalide");

        $statut = StatutBesoin::tryFrom($statutVal);
        if ($statut === null) throw new ServiceException("Statut invalide");

        $besoin->setTitreBesoin($titreBesoin);
        $besoin->setFiliere($filiere);
        $besoin->setObjectifBesoin($objectifBesoin);
        $besoin->setPublicVise($publicVise);
        $besoin->setPerimetrePrioritaire($perimetrePrioritaire);
        $besoin->setVision($vision);
        $besoin->setEnjeuxMajeurs($enjeuxMajeurs);
        $besoin->setBesoinsPrioritaires($besoinsPrioritaires);
        $besoin->setExemplesInnovations($exemplesInnovations);
        $besoin->setPartenaires($partenaires);
        $besoin->setStatutBesoin($statut);

        $this->besoinRepository->mettreAJour($besoin);
    }

    public function existeParIdUtilisateur(int $idUtilisateur): bool
    {
        return $this->besoinRepository->existeParIdUtilisateur($idUtilisateur);
    }

    public function recupererTous(): array
    {
        return $this->besoinRepository->recuperer();
    }

    public function recupererSoumis(): array
    {
        return $this->besoinRepository->recupererSoumis();
    }

    public function refuser(int $idBesoin, ?string $raison = null): void
    {
        
        $besoin = $this->besoinRepository->recupererParClePrimaire($idBesoin);
        if ($besoin === null || $besoin->getStatutBesoin() === StatutBesoin::BROUILLON) {
            throw new ServiceException("Besoin introuvable");
        }
        if ($raison === null || trim($raison) === '') {
            throw new ServiceException("Le motif du refus est obligatoire");
        }
        $besoin->setStatutBesoin(StatutBesoin::REFUSE);
        $besoin->setRaisonRefus(trim($raison));
        $this->besoinRepository->mettreAJour($besoin);

        $soumetteur = $this->utilisateurRepository->recupererParClePrimaire($besoin->getIdUtilisateur());
        if ($soumetteur !== null) {
            try {
                $this->mailService->envoyerMailBesoinRefuse(
                    $soumetteur->getEmailUtilisateur(),
                    $soumetteur->getPrenomUtilisateur(),
                    $besoin->getTitreBesoin(),
                    trim($raison)
                );
            } catch (\Exception) {}
        }
    }

    public function transformerEnChallenge(int $idBesoin, array $donnees): int
    {
        
        $besoin = $this->besoinRepository->recupererParClePrimaire($idBesoin);
        if ($besoin === null) {
            throw new ServiceException("Besoin introuvable");
        }

        $casUsagePilote      = trim($donnees['casUsagePilote'] ?? '');
        $perimetreCasUsage   = trim($donnees['perimetreCasUsage'] ?? '');
        $dureeIndicative     = trim($donnees['dureeIndicative'] ?? '');
        $sortieAttendue      = trim($donnees['sortieAttendue'] ?? '');
        $typeChallengeVal    = trim($donnees['typeChallenge'] ?? '');

        if ($casUsagePilote === '')    throw new ServiceException("Le cas d'usage pilote est obligatoire");
        if ($perimetreCasUsage === '') throw new ServiceException("Le périmètre du cas d'usage est obligatoire");
        if ($dureeIndicative === '')   throw new ServiceException("La durée indicative est obligatoire");
        if ($sortieAttendue === '')    throw new ServiceException("La sortie attendue est obligatoire");

        $typeChallenge = TypeChallenge::tryFrom($typeChallengeVal);
        if ($typeChallenge === null) {
            throw new ServiceException("Type de challenge invalide");
        }

        $challenge = new Challenge(
            $besoin->getTitreBesoin(),
            $besoin->getObjectifBesoin(),
            $besoin->getPublicVise(),
            $besoin->getPerimetrePrioritaire(),
            $besoin->getExemplesInnovations(),
            $casUsagePilote,
            $perimetreCasUsage,
            $dureeIndicative,
            $sortieAttendue,
            $besoin->getPartenaires(),
            $besoin->getFiliere(),
            $typeChallenge,
            new \DateTime(),
            StatutChallenge::OUVERT,
            $besoin->getIdBesoin(),
        );
        $this->challengeRepository->ajouter($challenge);

        $besoin->setStatutBesoin(StatutBesoin::TRANSFORMEC);
        $this->besoinRepository->mettreAJour($besoin);

        $soumetteur = $this->utilisateurRepository->recupererParClePrimaire($besoin->getIdUtilisateur());
        if ($soumetteur !== null) {
            try {
                $this->mailService->envoyerMailBesoinTransformeChallenge(
                    $soumetteur->getEmailUtilisateur(),
                    $soumetteur->getPrenomUtilisateur(),
                    $besoin->getTitreBesoin()
                );
            } catch (\Exception) {}
        }

        return $challenge->getIdChallenge();
    }

    public function transformerEnAmi(int $idBesoin, array $donnees, int $idUtilisateur): int
    {
        
        $besoin = $this->besoinRepository->recupererParClePrimaire($idBesoin);
        if ($besoin === null) {
            throw new ServiceException("Besoin introuvable");
        }

        $casUsagePilote    = trim($donnees['casUsagePilote'] ?? '');
        $perimetreCasUsage = trim($donnees['perimetreCasUsage'] ?? '');
        $dureeIndicative   = trim($donnees['dureeIndicative'] ?? '');
        $sortieAttendue    = trim($donnees['sortieAttendue'] ?? '');
        $dateOuvertureStr  = trim($donnees['dateOuverture'] ?? '');
        $dateCloturStr     = trim($donnees['dateCloture'] ?? '');
        $dateAuditionsStr  = trim($donnees['dateAuditions'] ?? '');
        $dateResultatsStr  = trim($donnees['dateResultats'] ?? '');
        $dateDemarrageStr  = trim($donnees['dateDemarrage'] ?? '');

        if ($casUsagePilote === '')   throw new ServiceException("Le cas d'usage pilote est obligatoire");
        if ($perimetreCasUsage === '') throw new ServiceException("Le périmètre du cas d'usage est obligatoire");
        if ($dureeIndicative === '')  throw new ServiceException("La durée indicative est obligatoire");
        if ($sortieAttendue === '')   throw new ServiceException("La sortie attendue est obligatoire");
        if ($dateOuvertureStr === '') throw new ServiceException("La date d'ouverture est obligatoire");
        if ($dateCloturStr === '')    throw new ServiceException("La date de clôture est obligatoire");
        if ($dateAuditionsStr === '') throw new ServiceException("La date des auditions est obligatoire");
        if ($dateResultatsStr === '') throw new ServiceException("La date des résultats est obligatoire");
        if ($dateDemarrageStr === '') throw new ServiceException("La date de démarrage est obligatoire");

        $dateOuverture = \DateTime::createFromFormat('Y-m-d', $dateOuvertureStr);
        $dateCloture   = \DateTime::createFromFormat('Y-m-d', $dateCloturStr);
        $dateAuditions = \DateTime::createFromFormat('Y-m-d', $dateAuditionsStr);
        $dateResultats = \DateTime::createFromFormat('Y-m-d', $dateResultatsStr);
        $dateDemarrage = \DateTime::createFromFormat('Y-m-d', $dateDemarrageStr);

        if (!$dateOuverture || !$dateCloture || !$dateAuditions || !$dateResultats || !$dateDemarrage) {
            throw new ServiceException("Format de date invalide (attendu : AAAA-MM-JJ)");
        }

        if ($dateOuverture >= $dateCloture) {
            throw new ServiceException("La date d'ouverture doit être antérieure à la date de clôture");
        }
        if ($dateCloture >= $dateAuditions) {
            throw new ServiceException("La date de clôture doit être antérieure à la date des auditions");
        }
        if ($dateAuditions >= $dateResultats) {
            throw new ServiceException("La date des auditions doit être antérieure à la date des résultats");
        }
        if ($dateResultats > $dateDemarrage) {
            throw new ServiceException("La date des résultats doit être antérieure ou égale à la date de démarrage");
        }

        $statut = AmiService::calculerStatut($dateOuverture, $dateCloture);

        $ami = new Ami(
            $besoin->getTitreBesoin(),
            $besoin->getObjectifBesoin(),
            $besoin->getPublicVise(),
            $besoin->getPerimetrePrioritaire(),
            $besoin->getFiliere(),
            $besoin->getExemplesInnovations(),
            $casUsagePilote,
            $perimetreCasUsage,
            $dureeIndicative,
            $sortieAttendue,
            $besoin->getPartenaires(),
            $dateOuverture,
            $dateCloture,
            $dateAuditions,
            $dateResultats,
            $dateDemarrage,
            $statut,
            $idUtilisateur,
            $besoin->getIdBesoin(),
        );
        $this->amiRepository->ajouter($ami);

        $besoin->setStatutBesoin(StatutBesoin::TRANSFORMEA);
        $this->besoinRepository->mettreAJour($besoin);

        $soumetteur = $this->utilisateurRepository->recupererParClePrimaire($besoin->getIdUtilisateur());
        if ($soumetteur !== null) {
            try {
                $this->mailService->envoyerMailBesoinTransformeAmi(
                    $soumetteur->getEmailUtilisateur(),
                    $soumetteur->getPrenomUtilisateur(),
                    $besoin->getTitreBesoin()
                );
            } catch (\Exception) {}
        }

        return $ami->getIdAmi();
    }
}
