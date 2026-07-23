<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Challenge;
use App\Gigamed\Modele\DataObject\Filiere;
use App\Gigamed\Modele\DataObject\StatutChallenge;
use App\Gigamed\Modele\DataObject\TypeChallenge;
use App\Gigamed\Modele\Repository\ChallengeRepository;
use App\Gigamed\Service\Exception\ServiceException;

class ChallengeService implements ChallengeServiceInterface
{
    public function __construct(private ChallengeRepository $challengeRepository) {}

    public function recupererChallengesOuverts(): array
    {
        return $this->challengeRepository->recupererChallengesOuverts();
    }

    public function recupererParId(int $id): ?Challenge
    {
        return $this->challengeRepository->recupererParClePrimaire($id);
    }

    public function recupererTous(): array
    {
        return $this->challengeRepository->recuperer();
    }

    public function recupererSoumis(): array
    {
        return $this->challengeRepository->recupererSoumis();
    }

    public function publier(int $idChallenge): int
    {
        
        $challenge = $this->challengeRepository->recupererParClePrimaire($idChallenge);
        if ($challenge === null) throw new ServiceException("Challenge introuvable");
        if ($challenge->getStatutChallenge() !== StatutChallenge::BROUILLON) {
            throw new ServiceException("Seul un challenge en brouillon peut être publié.");
        }
        $challenge->setStatutChallenge(StatutChallenge::OUVERT);
        $this->challengeRepository->mettreAJour($challenge);
        return $challenge->getIdBesoin();
    }

    public function cloturer(int $idChallenge): void
    {
        
        $challenge = $this->challengeRepository->recupererParClePrimaire($idChallenge);
        if ($challenge === null) {
            throw new ServiceException("Challenge introuvable");
        }
        if ($challenge->getStatutChallenge() !== StatutChallenge::OUVERT) {
            throw new ServiceException("Seul un challenge ouvert peut être clôturé.");
        }
        $challenge->setStatutChallenge(StatutChallenge::CLOTURE);
        $this->challengeRepository->mettreAJour($challenge);
    }

    public function supprimer(int $id): void
    {
        $challenge = $this->challengeRepository->recupererParClePrimaire($id);
        if ($challenge === null) {
            throw new ServiceException("Challenge introuvable");
        }
        $this->challengeRepository->supprimer($id);
    }

    public function modifier(int $id, array $donnees): void
    {
        
        $challenge = $this->challengeRepository->recupererParClePrimaire($id);
        if ($challenge === null) throw new ServiceException("Challenge introuvable");

        $titre               = trim($donnees['titreChallenge'] ?? '');
        $objectif            = trim($donnees['objectifChallenge'] ?? '');
        $publicVise          = trim($donnees['publicVise'] ?? '');
        $perimetrePrioritaire = trim($donnees['perimetrePrioritaire'] ?? '');
        $exemplesInnovations = trim($donnees['exemplesInnovations'] ?? '');
        $casUsagePilote      = trim($donnees['casUsagePilote'] ?? '');
        $perimetreCasUsage   = trim($donnees['perimetreCasUsage'] ?? '');
        $dureeIndicative     = trim($donnees['dureeIndicative'] ?? '');
        $sortieAttendue      = trim($donnees['sortieAttendue'] ?? '');
        $partenaires         = trim($donnees['partenaires'] ?? '');
        $filiereVal          = trim($donnees['filiere'] ?? '');
        $typeChallengeVal    = trim($donnees['typeChallenge'] ?? '');
        $statutVal           = trim($donnees['statutChallenge'] ?? '');

        if ($titre === '')               throw new ServiceException("Le titre est obligatoire");
        if ($objectif === '')            throw new ServiceException("L'objectif est obligatoire");
        if ($publicVise === '')          throw new ServiceException("Le public visé est obligatoire");
        if ($perimetrePrioritaire === '') throw new ServiceException("Le périmètre est obligatoire");
        if ($casUsagePilote === '')      throw new ServiceException("Le cas d'usage pilote est obligatoire");
        if ($perimetreCasUsage === '')   throw new ServiceException("Le périmètre du cas d'usage est obligatoire");
        if ($dureeIndicative === '')     throw new ServiceException("La durée indicative est obligatoire");
        if ($sortieAttendue === '')      throw new ServiceException("La sortie attendue est obligatoire");

        $filiere = Filiere::tryFrom($filiereVal);
        if ($filiere === null) throw new ServiceException("Filière invalide");

        $typeChallenge = TypeChallenge::tryFrom($typeChallengeVal);
        if ($typeChallenge === null) throw new ServiceException("Type de challenge invalide");

        $statut = StatutChallenge::tryFrom($statutVal);
        if ($statut === null) throw new ServiceException("Statut invalide");

        $challenge->setTitreChallenge($titre);
        $challenge->setObjectifChallenge($objectif);
        $challenge->setPublicVise($publicVise);
        $challenge->setPerimetrePrioritaire($perimetrePrioritaire);
        $challenge->setExemplesInnovations($exemplesInnovations);
        $challenge->setCasUsagePilote($casUsagePilote);
        $challenge->setPerimetreCasUsage($perimetreCasUsage);
        $challenge->setDureeIndicative($dureeIndicative);
        $challenge->setSortieAttendue($sortieAttendue);
        $challenge->setPartenaires($partenaires);
        $challenge->setFiliere($filiere);
        $challenge->setTypeChallenge($typeChallenge);
        $challenge->setStatutChallenge($statut);

        $this->challengeRepository->mettreAJour($challenge);
    }

    public function archiver(int $idChallenge): void
    {
        
        $challenge = $this->challengeRepository->recupererParClePrimaire($idChallenge);
        if ($challenge === null) {
            throw new ServiceException("Challenge introuvable");
        }
        if ($challenge->getStatutChallenge() !== StatutChallenge::CLOTURE) {
            throw new ServiceException("Seul un challenge clôturé peut être archivé.");
        }
        $challenge->setStatutChallenge(StatutChallenge::ARCHIVE);
        $this->challengeRepository->mettreAJour($challenge);
    }
}