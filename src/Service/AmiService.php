<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Ami;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutAmi;
use App\Gigamed\Modele\DataObject\StatutCandidature;
use App\Gigamed\Modele\Repository\AmiRepository;
use App\Gigamed\Modele\Repository\CandidatureRepository;
use App\Gigamed\Service\Exception\ServiceException;

class AmiService implements AmiServiceInterface
{
    public function __construct(
        private AmiRepository $amiRepository,
        private CandidatureRepository $candidatureRepository,
    ) {}

    public function recupererAmisOuverts(): array
    {
        $this->mettreAJourStatutsAutomatiques($this->amiRepository->recuperer());
        return $this->amiRepository->recupererAmisOuverts();
    }

    public function recupererParId(int $id): ?Ami
    {
        $ami = $this->amiRepository->recupererParClePrimaire($id);
        if ($ami !== null) {
            $this->mettreAJourStatutsAutomatiques([$ami]);
            $ami = $this->amiRepository->recupererParClePrimaire($id);
        }
        return $ami;
    }

    public function recupererTous(): array
    {
        $amis = $this->amiRepository->recuperer();
        return $this->mettreAJourStatutsAutomatiques($amis);
    }

    public function recupererSoumis(): array
    {
        $amis = $this->amiRepository->recupererSoumis();
        return $this->mettreAJourStatutsAutomatiques($amis);
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        $amis = $this->amiRepository->recupererPourAdmin($idAdmin);
        return $this->mettreAJourStatutsAutomatiques($amis);
    }

    public function supprimer(int $id): void
    {
        $ami = $this->amiRepository->recupererParClePrimaire($id);
        if ($ami === null) {
            throw new ServiceException("AMI introuvable");
        }
        $this->amiRepository->supprimer($id);
    }

    public function cloturer(int $idAmi): void
    {
        
        $ami = $this->amiRepository->recupererParClePrimaire($idAmi);
        if ($ami === null) {
            throw new ServiceException("AMI introuvable");
        }
        if ($ami->getStatutAmi() !== StatutAmi::OUVERT && $ami->getStatutAmi() !== StatutAmi::ATTENTE) {
            throw new ServiceException("Seul un AMI ouvert ou en attente peut être clôturé manuellement.");
        }
        $ami->setStatutAmi(StatutAmi::CLOTURE);
        $this->amiRepository->mettreAJour($ami);
    }

    public function archiver(int $idAmi): void
    {
        
        $ami = $this->amiRepository->recupererParClePrimaire($idAmi);
        if ($ami === null) {
            throw new ServiceException("AMI introuvable");
        }
        if ($ami->getStatutAmi() !== StatutAmi::CLOTURE) {
            throw new ServiceException("Seul un AMI clôturé peut être archivé.");
        }
        $ami->setStatutAmi(StatutAmi::ARCHIVE);
        $this->amiRepository->mettreAJour($ami);
    }

    public static function calculerStatut(\DateTime $dateOuverture, \DateTime $dateCloture): StatutAmi
    {
        $aujourd_hui = new \DateTime('today');
        if ($aujourd_hui < $dateOuverture) {
            return StatutAmi::ATTENTE;
        }
        if ($aujourd_hui < $dateCloture) {
            return StatutAmi::OUVERT;
        }
        return StatutAmi::CLOTURE;
    }

    private function mettreAJourStatutsAutomatiques(array $amis): array
    {
        foreach ($amis as $ami) {
            
            $statut = $ami->getStatutAmi();
            
            if ($statut === StatutAmi::ARCHIVE || $statut === StatutAmi::BROUILLON) continue;
            
            $nouveauStatut = self::calculerStatut($ami->getDateOuverture(), $ami->getDateCloture());
            if ($statut === StatutAmi::CLOTURE && $nouveauStatut !== StatutAmi::CLOTURE) continue;

            if ($nouveauStatut !== $statut) {
                $ami->setStatutAmi($nouveauStatut);
                $this->amiRepository->mettreAJour($ami);
            }
        }
        return $amis;
    }

    public function recupererBrouillon(int $idUtilisateur): ?Ami
    {
        return $this->amiRepository->recupererBrouillonParIdUtilisateur($idUtilisateur);
    }

    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idBrouillonCharge = null): Ami
    {
        $titre   = trim($donnees['titre'] ?? '');
        $filiere = Filiere::tryFrom(trim($donnees['filiere'] ?? ''));

        if ($titre === '') throw new ServiceException("Le titre est obligatoire pour sauvegarder un brouillon");
        if ($filiere === null) throw new ServiceException("La filière est obligatoire pour sauvegarder un brouillon");

        $datePlaceholder = new \DateTime('2099-01-01');

        $brouillon = $idBrouillonCharge !== null
            ? $this->amiRepository->recupererParClePrimaire($idBrouillonCharge)
            : null;
        if ($brouillon !== null) {
            $brouillon->setTitreAmi($titre);
            $brouillon->setObjectifAmi(trim($donnees['objectif'] ?? ''));
            $brouillon->setPublicVise(trim($donnees['publicVise'] ?? ''));
            $brouillon->setPerimetrePrioritaire(trim($donnees['perimetrePrioritaire'] ?? ''));
            $brouillon->setFiliere($filiere);
            $brouillon->setExemplesInnovations(trim($donnees['exemplesInnovations'] ?? ''));
            $brouillon->setCasUsagePilote(trim($donnees['casUsagePilote'] ?? ''));
            $brouillon->setPerimetreCasUsage(trim($donnees['perimetreCasUsage'] ?? ''));
            $brouillon->setDureeIndicative(trim($donnees['dureeIndicative'] ?? ''));
            $brouillon->setSortieAttendue(trim($donnees['sortieAttendue'] ?? ''));
            $brouillon->setPartenaires(trim($donnees['partenaires'] ?? ''));
            $brouillon->setDateOuverture(!empty($donnees['dateOuverture']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateOuverture']) ?: $datePlaceholder : $datePlaceholder);
            $brouillon->setDateCloture(!empty($donnees['dateCloture'])   ? \DateTime::createFromFormat('Y-m-d', $donnees['dateCloture'])   ?: $datePlaceholder : $datePlaceholder);
            $brouillon->setDateAuditions(!empty($donnees['dateAuditions']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateAuditions']) ?: $datePlaceholder : $datePlaceholder);
            $brouillon->setDateResultats(!empty($donnees['dateResultats']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateResultats']) ?: $datePlaceholder : $datePlaceholder);
            $brouillon->setDateDemarrage(!empty($donnees['dateDemarrage']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateDemarrage']) ?: $datePlaceholder : $datePlaceholder);
            $this->amiRepository->mettreAJour($brouillon);
            return $brouillon;
        } else {
            $ami = new Ami(
                $titre,
                trim($donnees['objectif'] ?? ''),
                trim($donnees['publicVise'] ?? ''),
                trim($donnees['perimetrePrioritaire'] ?? ''),
                $filiere,
                trim($donnees['exemplesInnovations'] ?? ''),
                trim($donnees['casUsagePilote'] ?? ''),
                trim($donnees['perimetreCasUsage'] ?? ''),
                trim($donnees['dureeIndicative'] ?? ''),
                trim($donnees['sortieAttendue'] ?? ''),
                trim($donnees['partenaires'] ?? ''),
                !empty($donnees['dateOuverture']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateOuverture']) ?: $datePlaceholder : $datePlaceholder,
                !empty($donnees['dateCloture'])   ? \DateTime::createFromFormat('Y-m-d', $donnees['dateCloture'])   ?: $datePlaceholder : $datePlaceholder,
                !empty($donnees['dateAuditions']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateAuditions']) ?: $datePlaceholder : $datePlaceholder,
                !empty($donnees['dateResultats']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateResultats']) ?: $datePlaceholder : $datePlaceholder,
                !empty($donnees['dateDemarrage']) ? \DateTime::createFromFormat('Y-m-d', $donnees['dateDemarrage']) ?: $datePlaceholder : $datePlaceholder,
                StatutAmi::BROUILLON,
                $idUtilisateur,
                null,
            );
            $this->amiRepository->ajouter($ami);
            return $ami;
        }
    }

    public function publier(int $idAmi): void
    {
        
        $ami = $this->amiRepository->recupererParClePrimaire($idAmi);
        if ($ami === null) throw new ServiceException("AMI introuvable");
        if ($ami->getStatutAmi() !== StatutAmi::BROUILLON) {
            throw new ServiceException("Seul un AMI en brouillon peut être publié.");
        }

        $placeholder = '2099-01-01';
        if (trim($ami->getTitreAmi()) === '')                              throw new ServiceException("Le titre de l'AMI est obligatoire avant de publier.");
        if (trim($ami->getObjectifAmi()) === '')                           throw new ServiceException("L'objectif est obligatoire avant de publier.");
        if (trim($ami->getPublicVise()) === '')                            throw new ServiceException("Le public visé est obligatoire avant de publier.");
        if (trim($ami->getPerimetrePrioritaire()) === '')                  throw new ServiceException("Le périmètre prioritaire est obligatoire avant de publier.");
        if (trim($ami->getCasUsagePilote()) === '')                        throw new ServiceException("Le cas d'usage pilote est obligatoire avant de publier.");
        if (trim($ami->getPerimetreCasUsage()) === '')                     throw new ServiceException("Le périmètre du cas d'usage est obligatoire avant de publier.");
        if (trim($ami->getDureeIndicative()) === '')                       throw new ServiceException("La durée indicative est obligatoire avant de publier.");
        if (trim($ami->getSortieAttendue()) === '')                        throw new ServiceException("La sortie attendue est obligatoire avant de publier.");
        if ($ami->getDateOuverture()->format('Y-m-d') === $placeholder)   throw new ServiceException("La date d'ouverture est obligatoire avant de publier.");
        if ($ami->getDateCloture()->format('Y-m-d') === $placeholder)     throw new ServiceException("La date de clôture est obligatoire avant de publier.");
        if ($ami->getDateAuditions()->format('Y-m-d') === $placeholder)   throw new ServiceException("La date des auditions est obligatoire avant de publier.");
        if ($ami->getDateResultats()->format('Y-m-d') === $placeholder)   throw new ServiceException("La date des résultats est obligatoire avant de publier.");
        if ($ami->getDateDemarrage()->format('Y-m-d') === $placeholder)   throw new ServiceException("La date de démarrage est obligatoire avant de publier.");
        if ($ami->getDateOuverture() >= $ami->getDateCloture())           throw new ServiceException("La date d'ouverture doit être antérieure à la date de clôture.");
        if ($ami->getDateCloture() >= $ami->getDateAuditions())           throw new ServiceException("La date de clôture doit être antérieure à la date des auditions.");
        if ($ami->getDateAuditions() >= $ami->getDateResultats())         throw new ServiceException("La date des auditions doit être antérieure à la date des résultats.");
        if ($ami->getDateResultats() > $ami->getDateDemarrage())          throw new ServiceException("La date des résultats doit être antérieure ou égale à la date de démarrage.");

        $statut = self::calculerStatut($ami->getDateOuverture(), $ami->getDateCloture());
        $ami->setStatutAmi($statut);
        $this->amiRepository->mettreAJour($ami);
    }

    public function creer(array $donnees, int $idUtilisateur): Ami
    {
        $titre               = trim($donnees['titre'] ?? '');
        $objectif            = trim($donnees['objectif'] ?? '');
        $publicVise          = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $filiereVal          = trim($donnees['filiere'] ?? '');
        $exemplesInnovations = trim($donnees['exemplesInnovations'] ?? '');
        $casUsagePilote      = trim($donnees['casUsagePilote'] ?? '');
        $perimetreCasUsage   = trim($donnees['perimetreCasUsage'] ?? '');
        $dureeIndicative     = trim($donnees['dureeIndicative'] ?? '');
        $sortieAttendue      = trim($donnees['sortieAttendue'] ?? '');
        $partenaires         = trim($donnees['partenaires'] ?? '');
        $dateOuvertureStr    = trim($donnees['dateOuverture'] ?? '');
        $dateCloturStr       = trim($donnees['dateCloture'] ?? '');
        $dateAuditionsStr    = trim($donnees['dateAuditions'] ?? '');
        $dateResultatsStr    = trim($donnees['dateResultats'] ?? '');
        $dateDemarrageStr    = trim($donnees['dateDemarrage'] ?? '');

        if ($titre === '')               throw new ServiceException("Le titre est obligatoire");
        if ($objectif === '')            throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')          throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre prioritaire est obligatoire");
        if ($casUsagePilote === '')      throw new ServiceException("Le cas d'usage pilote est obligatoire");
        if ($perimetreCasUsage === '')   throw new ServiceException("Le périmètre du cas d'usage est obligatoire");
        if ($dureeIndicative === '')     throw new ServiceException("La durée indicative est obligatoire");
        if ($sortieAttendue === '')      throw new ServiceException("La sortie attendue est obligatoire");
        if ($dateOuvertureStr === '')    throw new ServiceException("La date d'ouverture est obligatoire");
        if ($dateCloturStr === '')       throw new ServiceException("La date de clôture est obligatoire");
        if ($dateAuditionsStr === '')    throw new ServiceException("La date des auditions est obligatoire");
        if ($dateResultatsStr === '')    throw new ServiceException("La date des résultats est obligatoire");
        if ($dateDemarrageStr === '')    throw new ServiceException("La date de démarrage est obligatoire");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("Filière invalide");

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

        $statut = self::calculerStatut($dateOuverture, $dateCloture);

        $ami = new Ami(
            $titre, $objectif, $publicVise, $perimetrePrioritaire, $filiere,
            $exemplesInnovations, $casUsagePilote, $perimetreCasUsage,
            $dureeIndicative, $sortieAttendue, $partenaires,
            $dateOuverture, $dateCloture, $dateAuditions, $dateResultats, $dateDemarrage,
            $statut, $idUtilisateur, null
        );
        $this->amiRepository->ajouter($ami);
        return $ami;
    }

    public function modifier(int $id, array $donnees): void
    {
        
        $ami = $this->amiRepository->recupererParClePrimaire($id);
        if ($ami === null) throw new ServiceException("AMI introuvable");

        $titre               = trim($donnees['titre'] ?? '');
        $objectif            = trim($donnees['objectif'] ?? '');
        $publicVise          = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $filiereVal          = trim($donnees['filiere'] ?? '');
        $exemplesInnovations = trim($donnees['exemplesInnovations'] ?? '');
        $casUsagePilote      = trim($donnees['casUsagePilote'] ?? '');
        $perimetreCasUsage   = trim($donnees['perimetreCasUsage'] ?? '');
        $dureeIndicative     = trim($donnees['dureeIndicative'] ?? '');
        $sortieAttendue      = trim($donnees['sortieAttendue'] ?? '');
        $partenaires         = trim($donnees['partenaires'] ?? '');
        $dateOuvertureStr    = trim($donnees['dateOuverture'] ?? '');
        $dateCloturStr       = trim($donnees['dateCloture'] ?? '');
        $dateAuditionsStr    = trim($donnees['dateAuditions'] ?? '');
        $dateResultatsStr    = trim($donnees['dateResultats'] ?? '');
        $dateDemarrageStr    = trim($donnees['dateDemarrage'] ?? '');
        $statutVal           = trim($donnees['statutAmi'] ?? '');

        if ($titre === '')               throw new ServiceException("Le titre est obligatoire");
        if ($objectif === '')            throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')          throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre est obligatoire");
        if ($casUsagePilote === '')      throw new ServiceException("Le cas d'usage pilote est obligatoire");
        if ($perimetreCasUsage === '')   throw new ServiceException("Le périmètre du cas d'usage est obligatoire");
        if ($dureeIndicative === '')     throw new ServiceException("La durée indicative est obligatoire");
        if ($sortieAttendue === '')      throw new ServiceException("La sortie attendue est obligatoire");
        if ($dateOuvertureStr === '')    throw new ServiceException("La date d'ouverture est obligatoire");
        if ($dateCloturStr === '')       throw new ServiceException("La date de clôture est obligatoire");
        if ($dateAuditionsStr === '')    throw new ServiceException("La date des auditions est obligatoire");
        if ($dateResultatsStr === '')    throw new ServiceException("La date des résultats est obligatoire");
        if ($dateDemarrageStr === '')    throw new ServiceException("La date de démarrage est obligatoire");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("Filière invalide");

        $statut = StatutAmi::tryFrom($statutVal);
        if ($statut === null) throw new ServiceException("Statut invalide");

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

        $ami->setTitreAmi($titre);
        $ami->setObjectifAmi($objectif);
        $ami->setPublicVise($publicVise);
        $ami->setPerimetrePrioritaire($perimetrePrioritaire);
        $ami->setFiliere($filiere);
        $ami->setExemplesInnovations($exemplesInnovations);
        $ami->setCasUsagePilote($casUsagePilote);
        $ami->setPerimetreCasUsage($perimetreCasUsage);
        $ami->setDureeIndicative($dureeIndicative);
        $ami->setSortieAttendue($sortieAttendue);
        $ami->setPartenaires($partenaires);
        $ami->setDateOuverture($dateOuverture);
        $ami->setDateCloture($dateCloture);
        $ami->setDateAuditions($dateAuditions);
        $ami->setDateResultats($dateResultats);
        $ami->setDateDemarrage($dateDemarrage);
        $ami->setStatutAmi($statut);

        $this->amiRepository->mettreAJour($ami);
    }
}