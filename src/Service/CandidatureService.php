<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Candidature;
use App\Gigamed\Modele\DataObject\EntreeStationT;
use App\Gigamed\Modele\DataObject\NiveauAccompagnement;
use App\Gigamed\Modele\DataObject\ProgrammeGigamed;
use App\Gigamed\Modele\DataObject\StageMaturite;
use App\Gigamed\Modele\DataObject\StatutAmi;
use App\Gigamed\Modele\DataObject\StatutCandidature;
use App\Gigamed\Modele\DataObject\StatutChallenge;
use App\Gigamed\Modele\Repository\AmiRepository;
use App\Gigamed\Modele\Repository\BesoinRepository;
use App\Gigamed\Modele\Repository\AffecterRepository;
use App\Gigamed\Modele\Repository\CandidatureRepository;
use App\Gigamed\Modele\Repository\ChallengeRepository;
use App\Gigamed\Modele\Repository\DocumentRepository;
use App\Gigamed\Modele\Repository\NoterRepository;
use App\Gigamed\Modele\Repository\UtilisateurRepository;
use App\Gigamed\Service\Exception\ServiceException;
use App\Gigamed\Service\MailService;
use Symfony\Component\HttpFoundation\Response;

class CandidatureService implements CandidatureServiceInterface
{
    public function __construct(
        private CandidatureRepository $candidatureRepository,
        private BesoinRepository $besoinRepository,
        private UtilisateurRepository $utilisateurRepository,
        private MailService $mailService,
        private AmiRepository $amiRepository,
        private ChallengeRepository $challengeRepository,
        private AffecterRepository $affecterRepository,
        private DocumentRepository $documentRepository,
        private NoterRepository $noterRepository,
    ) {
    }

    public function recupererParId(int $id): ?\App\Gigamed\Modele\DataObject\Candidature
    {
        return $this->candidatureRepository->recupererParClePrimaire($id);
    }

    public function existeParUtilisateurEtAmi(int $idUtilisateur, int $idAmi): bool
    {
        return $this->candidatureRepository->existeParIdUtilisateurEtIdAmi($idUtilisateur, $idAmi);
    }

    public function existeParUtilisateurEtChallenge(int $idUtilisateur, int $idChallenge): bool
    {
        return $this->candidatureRepository->existeParIdUtilisateurEtIdChallenge($idUtilisateur, $idChallenge);
    }

    public function recupererParIdUtilisateur(int $idUtilisateur): array
    {
        return $this->candidatureRepository->recupererParIdUtilisateur($idUtilisateur);
    }

    public function recupererParIdAmi(int $idAmi): array
    {
        return $this->candidatureRepository->recupererParIdAmi($idAmi);
    }

    public function recupererParIdChallenge(int $idChallenge): array
    {
        return $this->candidatureRepository->recupererParIdChallenge($idChallenge);
    }

    public function recupererBrouillonParUtilisateurEtAmi(int $idUtilisateur, int $idAmi): ?Candidature
    {
        return $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdAmi($idUtilisateur, $idAmi);
    }

    public function recupererBrouillonParUtilisateurEtChallenge(int $idUtilisateur, int $idChallenge): ?Candidature
    {
        return $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdChallenge($idUtilisateur, $idChallenge);
    }

    public function sauvegarderBrouillon(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge): Candidature
    {
        if ($idAmi === null && $idChallenge === null) {
            throw new ServiceException("La candidature doit être liée à un AMI ou un challenge.");
        }

        $nomProjet = trim($donnees['nomProjet'] ?? '');
        if ($nomProjet === '') throw new ServiceException("Le nom du projet est obligatoire pour sauvegarder un brouillon");

        $entreeStationT = EntreeStationT::tryFrom(trim($donnees['entreeStationT'] ?? ''));
        $maturite       = StageMaturite::tryFrom(trim($donnees['maturite'] ?? ''));

        $brouillon = $idAmi !== null
            ? $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdAmi($idUtilisateur, $idAmi)
            : $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdChallenge($idUtilisateur, $idChallenge);

        $filiereCandidat       = $this->optionnel($donnees['filiereCandidat'] ?? null);
        $besoinTraite          = trim($donnees['besoinTraite'] ?? '');
        $descriptionProjet     = trim($donnees['descriptionProjet'] ?? '');
        $valeurAjoutee         = $this->optionnel($donnees['valeurAjoutee'] ?? null);
        $innovationDiff        = trim($donnees['innovationDifferentiation'] ?? '');
        $references            = $this->optionnel($donnees['references'] ?? null);
        $objectifPilote        = $this->optionnel($donnees['objectifPilote'] ?? null);
        $perimetreGeographique = $this->optionnel($donnees['perimetreGeographique'] ?? null);
        $dureeExperimentation  = $this->optionnel($donnees['dureeExperimentation'] ?? null);
        $publicsCibles         = $this->optionnel($donnees['publicsCibles'] ?? null);
        $budgetMobiliser       = $this->optionnel($donnees['budgetMobiliser'] ?? null);
        $moyensMobiliser       = $this->optionnel($donnees['moyensMobiliser'] ?? null);
        $conditionsReussite    = $this->optionnel($donnees['conditionsReussite'] ?? null);
        $derouteOperationnel   = $this->optionnel($donnees['derouteOperationnel'] ?? null);
        $partenairesRecherches = $this->optionnel($donnees['partenairesRecherches'] ?? null);
        $appuisStationT        = $this->optionnel($donnees['appuisStationT'] ?? null);
        $modelEconomique       = trim($donnees['modelEconomique'] ?? '');
        $conditionsDeploiement = $this->optionnel($donnees['conditionsDeploiement'] ?? null);
        $impactAttendu         = $this->optionnel($donnees['impactAttendu'] ?? null);
        $conformite            = $this->optionnel($donnees['conformite'] ?? null);
        $mesuresSecurisation   = $this->optionnel($donnees['mesuresSecurisation'] ?? null);
        $pitchCourt            = $this->optionnel($donnees['pitchCourt'] ?? null);
        $problemeIdentifie     = $this->optionnel($donnees['problemeIdentifie'] ?? null);
        $concurrence           = $this->optionnel($donnees['concurrence'] ?? null);
        $clientsCibles         = $this->optionnel($donnees['clientsCibles'] ?? null);
        $preuvesBesoin         = $this->optionnel($donnees['preuvesBesoin'] ?? null);
        $etatAvancement        = $this->optionnel($donnees['etatAvancement'] ?? null);
        $besoinsFinanciers     = $this->optionnel($donnees['besoinsFinanciers'] ?? null);
        $equipe                = $this->optionnel($donnees['equipe'] ?? null);
        $forcesEquipe          = $this->optionnel($donnees['forcesEquipe'] ?? null);
        $objectifAccompagnement = $this->optionnel($donnees['objectifAccompagnement'] ?? null);

        $besoinsPrioritairesRaw = $donnees['besoinsPrioritaires'] ?? null;
        $besoinsPrioritaires = (is_array($besoinsPrioritairesRaw) && !empty($besoinsPrioritairesRaw))
            ? json_encode(array_values($besoinsPrioritairesRaw))
            : null;

        $programmeGigamed = !empty($donnees['programmeGigamed'])
            ? ProgrammeGigamed::tryFrom($donnees['programmeGigamed'])
            : null;

        $niveauAccompagnement = !empty($donnees['niveauAccompagnement'])
            ? NiveauAccompagnement::tryFrom($donnees['niveauAccompagnement'])
            : null;

        if ($entreeStationT === null) {
            throw new ServiceException("Veuillez sélectionner votre entrée dans Station T (option 1, 2 ou 3).");
        }
        if (!$filiereCandidat && $entreeStationT !== EntreeStationT::OPTION3) {
            throw new ServiceException("Veuillez sélectionner une filière.");
        }

        if ($brouillon !== null) {
            $brouillon->setNomProjet($nomProjet);
            $brouillon->setEntreeStationT($entreeStationT);
            $brouillon->setFiliereCandidat($filiereCandidat ?? '');
            $brouillon->setBesoinTraite($besoinTraite);
            $brouillon->setDescriptionProjet($descriptionProjet);
            $brouillon->setValeurAjoutee($valeurAjoutee);
            $brouillon->setInnovationDifferentiation($innovationDiff);
            $brouillon->setMaturite($maturite ?? $brouillon->getMaturite());
            $brouillon->setReferences_($references ?? '');
            $brouillon->setObjectifPilote($objectifPilote);
            $brouillon->setPerimetreGeographique($perimetreGeographique);
            $brouillon->setDureeExperimentation($dureeExperimentation);
            $brouillon->setPublicsCibles($publicsCibles ?? '');
            $brouillon->setBudgetMobiliser($budgetMobiliser);
            $brouillon->setMoyensMobiliser($moyensMobiliser);
            $brouillon->setConditionsReussite($conditionsReussite);
            $brouillon->setDerouteOperationnel($derouteOperationnel);
            $brouillon->setPartenairesRecherches($partenairesRecherches);
            $brouillon->setAppuisStationT($appuisStationT);
            $brouillon->setModelEconomique($modelEconomique);
            $brouillon->setConditionsDeploiement($conditionsDeploiement);
            $brouillon->setImpactAttendu($impactAttendu);
            $brouillon->setConformite($conformite);
            $brouillon->setMesuresSecurisation($mesuresSecurisation);
            $brouillon->setPitchCourt($pitchCourt);
            $brouillon->setProblemeIdentifie($problemeIdentifie);
            $brouillon->setConcurrence($concurrence);
            $brouillon->setClientsCibles($clientsCibles);
            $brouillon->setPreuvesBesoin($preuvesBesoin);
            $brouillon->setEtatAvancement($etatAvancement);
            $brouillon->setBesoinsFinanciers($besoinsFinanciers);
            $brouillon->setEquipe($equipe);
            $brouillon->setForcesEquipe($forcesEquipe);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setObjectifAccompagnement($objectifAccompagnement);
            $brouillon->setProgrammeGigamed($programmeGigamed);
            $brouillon->setNiveauAccompagnement($niveauAccompagnement);
            $this->candidatureRepository->mettreAJour($brouillon);
            return $brouillon;
        }

        $candidature = new Candidature(
            $nomProjet, $entreeStationT, $filiereCandidat, $besoinTraite,
            $descriptionProjet, $valeurAjoutee, $innovationDiff, $maturite ?? StageMaturite::IDEE,
            $references, $objectifPilote, $perimetreGeographique, $dureeExperimentation,
            $publicsCibles, $budgetMobiliser, $moyensMobiliser, $conditionsReussite,
            $derouteOperationnel, $partenairesRecherches, $appuisStationT,
            $modelEconomique, $conditionsDeploiement, $impactAttendu, $conformite,
            $mesuresSecurisation, false, $pitchCourt, $problemeIdentifie,
            $concurrence, $clientsCibles, $preuvesBesoin, $etatAvancement,
            $besoinsFinanciers, $equipe, $forcesEquipe, $programmeGigamed,
            $niveauAccompagnement, $besoinsPrioritaires, $objectifAccompagnement,
            new \DateTime(), StatutCandidature::BROUILLON,
            $idUtilisateur, $idChallenge, $idAmi,
        );
        $this->candidatureRepository->ajouter($candidature);
        return $candidature;
    }

    public function deposerParAdmin(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge, int $idAdmin = 0, ?int $idBrouillonCharge = null): Candidature
    {
        if ($idUtilisateur <= 0) {
            throw new ServiceException("Veuillez sélectionner un utilisateur.");
        }
        if ($idAmi === null && $idChallenge === null) {
            throw new ServiceException("La candidature doit être liée à un AMI ou un challenge.");
        }

        
        $brouillon = null;
        if ($idBrouillonCharge !== null) {
            $brouillon = $this->candidatureRepository->recupererParClePrimaire($idBrouillonCharge);
            if ($brouillon === null || $brouillon->getStatutCandidature() !== StatutCandidature::BROUILLON) {
                $brouillon = null;
            }
        }

        $candidatureValidee = $this->construireEtPersisterCandidature($donnees, $idUtilisateur, $idAmi, $idChallenge);
        if ($brouillon !== null) {
            $this->documentRepository->mettreAJourIdCandidature(
                $brouillon->getIdCandidature(),
                $candidatureValidee->getIdCandidature()
            );
            $this->candidatureRepository->supprimer($brouillon->getIdCandidature());
        }
        return $candidatureValidee;
    }

    public function sauvegarderBrouillonParAdmin(array $donnees, int $idAdmin, ?int $idAmi, ?int $idChallenge, ?int $idBrouillonCharge = null): Candidature
    {
        $nomProjet = trim($donnees['nomProjet'] ?? '');
        if ($nomProjet === '') throw new ServiceException("Le nom du projet est obligatoire pour sauvegarder un brouillon");

        $entreeStationT = EntreeStationT::tryFrom(trim($donnees['entreeStationT'] ?? ''));
        $maturite       = StageMaturite::tryFrom(trim($donnees['maturite'] ?? ''));

        $brouillon = null;
        if ($idBrouillonCharge !== null) {
            $brouillon = $this->candidatureRepository->recupererParClePrimaire($idBrouillonCharge);
            if ($brouillon === null || $brouillon->getStatutCandidature() !== StatutCandidature::BROUILLON) {
                $brouillon = null;
            }
        }

        $filiereCandidat       = $this->optionnel($donnees['filiereCandidat'] ?? null);
        $besoinTraite          = trim($donnees['besoinTraite'] ?? '');
        $descriptionProjet     = trim($donnees['descriptionProjet'] ?? '');
        $valeurAjoutee         = $this->optionnel($donnees['valeurAjoutee'] ?? null);
        $innovationDiff        = trim($donnees['innovationDifferentiation'] ?? '');
        $references            = $this->optionnel($donnees['references'] ?? null);
        $objectifPilote        = $this->optionnel($donnees['objectifPilote'] ?? null);
        $perimetreGeographique = $this->optionnel($donnees['perimetreGeographique'] ?? null);
        $dureeExperimentation  = $this->optionnel($donnees['dureeExperimentation'] ?? null);
        $publicsCibles         = $this->optionnel($donnees['publicsCibles'] ?? null);
        $budgetMobiliser       = $this->optionnel($donnees['budgetMobiliser'] ?? null);
        $moyensMobiliser       = $this->optionnel($donnees['moyensMobiliser'] ?? null);
        $conditionsReussite    = $this->optionnel($donnees['conditionsReussite'] ?? null);
        $derouteOperationnel   = $this->optionnel($donnees['derouteOperationnel'] ?? null);
        $partenairesRecherches = $this->optionnel($donnees['partenairesRecherches'] ?? null);
        $appuisStationT        = $this->optionnel($donnees['appuisStationT'] ?? null);
        $modelEconomique       = trim($donnees['modelEconomique'] ?? '');
        $conditionsDeploiement = $this->optionnel($donnees['conditionsDeploiement'] ?? null);
        $impactAttendu         = $this->optionnel($donnees['impactAttendu'] ?? null);
        $conformite            = $this->optionnel($donnees['conformite'] ?? null);
        $mesuresSecurisation   = $this->optionnel($donnees['mesuresSecurisation'] ?? null);
        $pitchCourt            = $this->optionnel($donnees['pitchCourt'] ?? null);
        $problemeIdentifie     = $this->optionnel($donnees['problemeIdentifie'] ?? null);
        $concurrence           = $this->optionnel($donnees['concurrence'] ?? null);
        $clientsCibles         = $this->optionnel($donnees['clientsCibles'] ?? null);
        $preuvesBesoin         = $this->optionnel($donnees['preuvesBesoin'] ?? null);
        $etatAvancement        = $this->optionnel($donnees['etatAvancement'] ?? null);
        $besoinsFinanciers     = $this->optionnel($donnees['besoinsFinanciers'] ?? null);
        $equipe                = $this->optionnel($donnees['equipe'] ?? null);
        $forcesEquipe          = $this->optionnel($donnees['forcesEquipe'] ?? null);
        $objectifAccompagnement = $this->optionnel($donnees['objectifAccompagnement'] ?? null);

        $besoinsPrioritairesRaw = $donnees['besoinsPrioritaires'] ?? null;
        $besoinsPrioritaires = (is_array($besoinsPrioritairesRaw) && !empty($besoinsPrioritairesRaw))
            ? json_encode(array_values($besoinsPrioritairesRaw))
            : null;

        $programmeGigamed = !empty($donnees['programmeGigamed'])
            ? ProgrammeGigamed::tryFrom($donnees['programmeGigamed'])
            : null;

        $niveauAccompagnement = !empty($donnees['niveauAccompagnement'])
            ? NiveauAccompagnement::tryFrom($donnees['niveauAccompagnement'])
            : null;

        if ($entreeStationT === null) {
            throw new ServiceException("Veuillez sélectionner votre entrée dans Station T (option 1, 2 ou 3).");
        }
        if (!$filiereCandidat && $entreeStationT !== EntreeStationT::OPTION3) {
            throw new ServiceException("Veuillez sélectionner une filière.");
        }

        if ($brouillon !== null) {
            $brouillon->setNomProjet($nomProjet);
            $brouillon->setEntreeStationT($entreeStationT);
            $brouillon->setFiliereCandidat($filiereCandidat ?? '');
            $brouillon->setBesoinTraite($besoinTraite);
            $brouillon->setDescriptionProjet($descriptionProjet);
            $brouillon->setValeurAjoutee($valeurAjoutee);
            $brouillon->setInnovationDifferentiation($innovationDiff);
            $brouillon->setMaturite($maturite ?? $brouillon->getMaturite());
            $brouillon->setReferences_($references ?? '');
            $brouillon->setObjectifPilote($objectifPilote);
            $brouillon->setPerimetreGeographique($perimetreGeographique);
            $brouillon->setDureeExperimentation($dureeExperimentation);
            $brouillon->setPublicsCibles($publicsCibles ?? '');
            $brouillon->setBudgetMobiliser($budgetMobiliser);
            $brouillon->setMoyensMobiliser($moyensMobiliser);
            $brouillon->setConditionsReussite($conditionsReussite);
            $brouillon->setDerouteOperationnel($derouteOperationnel);
            $brouillon->setPartenairesRecherches($partenairesRecherches);
            $brouillon->setAppuisStationT($appuisStationT);
            $brouillon->setModelEconomique($modelEconomique);
            $brouillon->setConditionsDeploiement($conditionsDeploiement);
            $brouillon->setImpactAttendu($impactAttendu);
            $brouillon->setConformite($conformite);
            $brouillon->setMesuresSecurisation($mesuresSecurisation);
            $brouillon->setPitchCourt($pitchCourt);
            $brouillon->setProblemeIdentifie($problemeIdentifie);
            $brouillon->setConcurrence($concurrence);
            $brouillon->setClientsCibles($clientsCibles);
            $brouillon->setPreuvesBesoin($preuvesBesoin);
            $brouillon->setEtatAvancement($etatAvancement);
            $brouillon->setBesoinsFinanciers($besoinsFinanciers);
            $brouillon->setEquipe($equipe);
            $brouillon->setForcesEquipe($forcesEquipe);
            $brouillon->setBesoinsPrioritaires($besoinsPrioritaires);
            $brouillon->setObjectifAccompagnement($objectifAccompagnement);
            $brouillon->setProgrammeGigamed($programmeGigamed);
            $brouillon->setNiveauAccompagnement($niveauAccompagnement);
            if ($idAmi !== null) $brouillon->setIdAmi($idAmi);
            if ($idChallenge !== null) $brouillon->setIdChallenge($idChallenge);
            $this->candidatureRepository->mettreAJour($brouillon);
            return $brouillon;
        }

        if ($entreeStationT === null) {
            throw new ServiceException("Veuillez sélectionner votre entrée dans Station T (option 1, 2 ou 3).");
        }
        if (!$filiereCandidat && $entreeStationT !== EntreeStationT::OPTION3) {
            throw new ServiceException("Veuillez sélectionner une filière.");
        }

        $candidature = new Candidature(
            $nomProjet, $entreeStationT, $filiereCandidat, $besoinTraite,
            $descriptionProjet, $valeurAjoutee, $innovationDiff, $maturite ?? StageMaturite::IDEE,
            $references, $objectifPilote, $perimetreGeographique, $dureeExperimentation,
            $publicsCibles, $budgetMobiliser, $moyensMobiliser, $conditionsReussite,
            $derouteOperationnel, $partenairesRecherches, $appuisStationT,
            $modelEconomique, $conditionsDeploiement, $impactAttendu, $conformite,
            $mesuresSecurisation, false, $pitchCourt, $problemeIdentifie,
            $concurrence, $clientsCibles, $preuvesBesoin, $etatAvancement,
            $besoinsFinanciers, $equipe, $forcesEquipe, $programmeGigamed,
            $niveauAccompagnement, $besoinsPrioritaires, $objectifAccompagnement,
            new \DateTime(), StatutCandidature::BROUILLON,
            $idAdmin, $idChallenge, $idAmi,
        );
        $this->candidatureRepository->ajouter($candidature);
        return $candidature;
    }

    public function recupererBrouillonParAdmin(int $idAdmin): ?Candidature
    {
        return $this->candidatureRepository->recupererBrouillonParAdmin($idAdmin);
    }

    public function recupererBrouillonsParAdmin(int $idAdmin): array
    {
        return $this->candidatureRepository->recupererBrouillonsParAdmin($idAdmin);
    }

    public function recupererPourAdmin(int $idAdmin): array
    {
        return $this->candidatureRepository->recupererPourAdmin($idAdmin);
    }

    public function deposer(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge): Candidature
    {
        if ($this->besoinRepository->existeParIdUtilisateur($idUtilisateur)) {
            throw new ServiceException(
                "Vous avez soumis un besoin. Vous ne pouvez pas déposer une candidature.",
                Response::HTTP_FORBIDDEN
            );
        }

        if ($idAmi === null && $idChallenge === null) {
            throw new ServiceException("La candidature doit être liée à un AMI ou un challenge.");
        }

        if ($idAmi !== null) {
            if ($this->candidatureRepository->existeParIdUtilisateurEtIdAmi($idUtilisateur, $idAmi)) {
                throw new ServiceException("Vous avez déjà soumis une candidature pour cet AMI.", Response::HTTP_FORBIDDEN);
            }
            $ami = $this->amiRepository->recupererParClePrimaire($idAmi);
            if ($ami === null) {
                throw new ServiceException("Cet AMI n'existe pas.");
            }
            $now = new \DateTime();
            if ($ami->getStatutAmi() !== StatutAmi::OUVERT) {
                throw new ServiceException("Cet AMI n'est pas ouvert aux candidatures.");
            }
            if ($now < $ami->getDateOuverture()) {
                throw new ServiceException("Les candidatures pour cet AMI ne sont pas encore ouvertes (ouverture le " . $ami->getDateOuverture()->format('d/m/Y') . ").");
            }
            if ($now > $ami->getDateCloture()) {
                throw new ServiceException("La période de candidature pour cet AMI est clôturée depuis le " . $ami->getDateCloture()->format('d/m/Y') . ".");
            }
        }

        if ($idChallenge !== null) {
            if ($this->candidatureRepository->existeParIdUtilisateurEtIdChallenge($idUtilisateur, $idChallenge)) {
                throw new ServiceException("Vous avez déjà soumis une candidature pour ce challenge.", Response::HTTP_FORBIDDEN);
            }
            $challenge = $this->challengeRepository->recupererParClePrimaire($idChallenge);
            if ($challenge === null) {
                throw new ServiceException("Ce challenge n'existe pas.");
            }
            if ($challenge->getStatutChallenge() !== StatutChallenge::OUVERT) {
                throw new ServiceException("Ce challenge n'est pas ouvert aux candidatures.");
            }
        }

        return $this->construireEtPersisterCandidature($donnees, $idUtilisateur, $idAmi, $idChallenge);
    }

    private function construireEtPersisterCandidature(array $donnees, int $idUtilisateur, ?int $idAmi, ?int $idChallenge): Candidature
    {
        $nomProjet         = trim($donnees['nomProjet'] ?? '');
        $entreeStationTVal = trim($donnees['entreeStationT'] ?? '');
        $filiereCandidat   = $this->optionnel($donnees['filiereCandidat'] ?? null);
        $besoinTraite      = trim($donnees['besoinTraite'] ?? '');
        $descriptionProjet = trim($donnees['descriptionProjet'] ?? '');
        $innovationDiff    = trim($donnees['innovationDifferentiation'] ?? '');
        $maturiteVal       = trim($donnees['maturite'] ?? '');
        $modelEconomique   = trim($donnees['modelEconomique'] ?? '');
        $engagements       = isset($donnees['eng_certifie']) && $donnees['eng_certifie'] === '1';

        if ($nomProjet === '')         throw new ServiceException("Le nom du projet est obligatoire");
        if ($entreeStationTVal === '') throw new ServiceException("L'entrée Station T est obligatoire");
        if ($besoinTraite === '')      throw new ServiceException("Le besoin traité est obligatoire");
        if ($descriptionProjet === '') throw new ServiceException("La description du projet est obligatoire");
        if ($innovationDiff === '')    throw new ServiceException("L'innovation / différentiation est obligatoire");
        if ($maturiteVal === '')       throw new ServiceException("Le stade de maturité est obligatoire");
        if ($modelEconomique === '')   throw new ServiceException("Le modèle économique est obligatoire");
        if (!$engagements)             throw new ServiceException("Vous devez accepter tous les engagements pour déposer une candidature");

        $entreeStationT = EntreeStationT::tryFrom($entreeStationTVal);
        if ($entreeStationT === null) throw new ServiceException("Valeur d'entrée Station T invalide");

        $maturite = StageMaturite::tryFrom($maturiteVal);
        if ($maturite === null) throw new ServiceException("Stade de maturité invalide");

        $references             = $this->optionnel($donnees['references'] ?? null);
        $publicsCibles          = $this->optionnel($donnees['publicsCibles'] ?? null);
        $valeurAjoutee          = $this->optionnel($donnees['valeurAjoutee'] ?? null);
        $objectifPilote         = $this->optionnel($donnees['objectifPilote'] ?? null);
        $perimetreGeographique  = $this->optionnel($donnees['perimetreGeographique'] ?? null);
        $dureeExperimentation   = $this->optionnel($donnees['dureeExperimentation'] ?? null);
        $budgetMobiliser        = $this->optionnel($donnees['budgetMobiliser'] ?? null);
        $moyensMobiliser        = $this->optionnel($donnees['moyensMobiliser'] ?? null);
        $conditionsReussite     = $this->optionnel($donnees['conditionsReussite'] ?? null);
        $derouteOperationnel    = $this->optionnel($donnees['derouteOperationnel'] ?? null);
        $partenairesRecherches  = $this->optionnel($donnees['partenairesRecherches'] ?? null);
        $appuisStationT         = $this->optionnel($donnees['appuisStationT'] ?? null);
        $conditionsDeploiement  = $this->optionnel($donnees['conditionsDeploiement'] ?? null);
        $impactAttendu          = $this->optionnel($donnees['impactAttendu'] ?? null);
        $conformite             = $this->optionnel($donnees['conformite'] ?? null);
        $mesuresSecurisation    = $this->optionnel($donnees['mesuresSecurisation'] ?? null);
        $pitchCourt             = $this->optionnel($donnees['pitchCourt'] ?? null);
        $problemeIdentifie      = $this->optionnel($donnees['problemeIdentifie'] ?? null);
        $concurrence            = $this->optionnel($donnees['concurrence'] ?? null);
        $clientsCibles          = $this->optionnel($donnees['clientsCibles'] ?? null);
        $preuvesBesoin          = $this->optionnel($donnees['preuvesBesoin'] ?? null);
        $etatAvancement         = $this->optionnel($donnees['etatAvancement'] ?? null);
        $besoinsFinanciers      = $this->optionnel($donnees['besoinsFinanciers'] ?? null);
        $equipe                 = $this->optionnel($donnees['equipe'] ?? null);
        $forcesEquipe           = $this->optionnel($donnees['forcesEquipe'] ?? null);
        $besoinsPrioritairesRaw = $donnees['besoinsPrioritaires'] ?? null;
        if (is_array($besoinsPrioritairesRaw) && !empty($besoinsPrioritairesRaw)) {
            $besoinsPrioritaires = json_encode(array_values($besoinsPrioritairesRaw));
        } else {
            $besoinsPrioritaires = null;
        }
        $objectifAccompagnement = $this->optionnel($donnees['objectifAccompagnement'] ?? null);

        $programmeGigamed = null;
        if (!empty($donnees['programmeGigamed'])) {
            $programmeGigamed = ProgrammeGigamed::tryFrom($donnees['programmeGigamed']);
        }

        $niveauAccompagnement = null;
        if (!empty($donnees['niveauAccompagnement'])) {
            $niveauAccompagnement = NiveauAccompagnement::tryFrom($donnees['niveauAccompagnement']);
        }

        $candidature = new Candidature(
            $nomProjet, $entreeStationT, $filiereCandidat, $besoinTraite,
            $descriptionProjet, $valeurAjoutee, $innovationDiff, $maturite,
            $references, $objectifPilote, $perimetreGeographique, $dureeExperimentation,
            $publicsCibles, $budgetMobiliser, $moyensMobiliser, $conditionsReussite,
            $derouteOperationnel, $partenairesRecherches, $appuisStationT,
            $modelEconomique, $conditionsDeploiement, $impactAttendu, $conformite,
            $mesuresSecurisation, $engagements, $pitchCourt, $problemeIdentifie,
            $concurrence, $clientsCibles, $preuvesBesoin, $etatAvancement,
            $besoinsFinanciers, $equipe, $forcesEquipe, $programmeGigamed,
            $niveauAccompagnement, $besoinsPrioritaires, $objectifAccompagnement,
            new \DateTime(), StatutCandidature::DEPOSE,
            $idUtilisateur, $idChallenge, $idAmi,
        );

        
        $brouillon = $idAmi !== null
            ? $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdAmi($idUtilisateur, $idAmi)
            : ($idChallenge !== null
                ? $this->candidatureRepository->recupererBrouillonParIdUtilisateurEtIdChallenge($idUtilisateur, $idChallenge)
                : null);

        if ($brouillon !== null) {
            $candidature->setIdCandidature($brouillon->getIdCandidature());
            $this->candidatureRepository->mettreAJour($candidature);
        } else {
            $this->candidatureRepository->ajouter($candidature);
        }
        return $candidature;
    }

    public function supprimer(int $id): void
    {
        $candidature = $this->candidatureRepository->recupererParClePrimaire($id);
        if ($candidature === null) {
            throw new ServiceException("Candidature introuvable");
        }
        $this->candidatureRepository->supprimer($id);
    }

    public function existeParIdUtilisateur(int $idUtilisateur): bool
    {
        return $this->candidatureRepository->existeParIdUtilisateur($idUtilisateur);
    }

    public function recupererTous(): array
    {
        return $this->candidatureRepository->recuperer();
    }

    public function recupererSoumis(): array
    {
        return $this->candidatureRepository->recupererSoumis();
    }

    private const STATUTS_ADMIN = [
        StatutCandidature::ELIGIBLE,
        StatutCandidature::INCOMPLET,
        StatutCandidature::REFUSE,
    ];

    public function getStatutsAdmin(): array
    {
        return self::STATUTS_ADMIN;
    }

    public function passerEnInstructionParAmi(int $idAmi): void
    {
        foreach ($this->candidatureRepository->recupererParIdAmi($idAmi) as $candidature) {
            $sc = $candidature->getStatutCandidature();
            if ($sc === StatutCandidature::ELIGIBLE) {
                $candidature->setStatutCandidature(StatutCandidature::INSTRUCTION);
                $this->candidatureRepository->mettreAJour($candidature);
            } elseif ($sc === StatutCandidature::DEPOSE) {
                $candidature->setStatutCandidature(StatutCandidature::REFUSE);
                $this->candidatureRepository->mettreAJour($candidature);
            }
        }
    }

    public function passerEnInstructionParChallenge(int $idChallenge): void
    {
        foreach ($this->candidatureRepository->recupererParIdChallenge($idChallenge) as $candidature) {
            $sc = $candidature->getStatutCandidature();
            if ($sc === StatutCandidature::ELIGIBLE) {
                $candidature->setStatutCandidature(StatutCandidature::INSTRUCTION);
                $this->candidatureRepository->mettreAJour($candidature);
            } elseif ($sc === StatutCandidature::DEPOSE) {
                $candidature->setStatutCandidature(StatutCandidature::REFUSE);
                $this->candidatureRepository->mettreAJour($candidature);
            }
        }
    }

    public function changerStatut(int $idCandidature, string $statut, ?string $messageIncomplet = null, ?string $messageRefus = null): void
    {
        $statutEnum = StatutCandidature::tryFrom($statut);
        if ($statutEnum === null) {
            throw new ServiceException("Statut invalide");
        }
        if (!in_array($statutEnum, self::STATUTS_ADMIN, true)) {
            throw new ServiceException("Ce statut ne peut pas être défini manuellement.");
        }
        
        $candidature = $this->candidatureRepository->recupererParClePrimaire($idCandidature);
        if ($candidature === null) {
            throw new ServiceException("Candidature introuvable");
        }
        if ($candidature->getStatutCandidature() === StatutCandidature::INSTRUCTION) {
            throw new ServiceException("La notation est terminée — le statut de cette candidature ne peut plus être modifié manuellement.");
        }
        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($candidature->getIdUtilisateur());

        if ($statutEnum === StatutCandidature::INCOMPLET) {
            if ($messageIncomplet === null || trim($messageIncomplet) === '') {
                throw new ServiceException("Vous devez préciser les éléments manquants pour marquer un dossier comme incomplet.");
            }
            if ($utilisateur !== null) {
                try {
                    $this->mailService->envoyerMailIncomplet(
                        $utilisateur->getEmailUtilisateur(),
                        $utilisateur->getPrenomUtilisateur(),
                        $candidature->getNomProjet(),
                        trim($messageIncomplet)
                    );
                } catch (\Exception) {}
            }
        } elseif ($statutEnum === StatutCandidature::REFUSE) {
            if ($messageRefus === null || trim($messageRefus) === '') {
                throw new ServiceException("Vous devez préciser le motif du refus.");
            }
            if ($utilisateur !== null) {
                try {
                    $this->mailService->envoyerMailRefus(
                        $utilisateur->getEmailUtilisateur(),
                        $utilisateur->getPrenomUtilisateur(),
                        $candidature->getNomProjet(),
                        trim($messageRefus)
                    );
                } catch (\Exception) {}
            }
        } elseif ($statutEnum === StatutCandidature::ELIGIBLE) {
            if ($utilisateur !== null) {
                try {
                    $this->mailService->envoyerMailEligible(
                        $utilisateur->getEmailUtilisateur(),
                        $utilisateur->getPrenomUtilisateur(),
                        $candidature->getNomProjet()
                    );
                } catch (\Exception) {}
            }
        }

        $candidature->setStatutCandidature($statutEnum);
        $this->candidatureRepository->mettreAJour($candidature);
    }

    public function decisionFinale(int $idCandidature, string $decision, ?string $motifRefus = null): void
    {
        
        $candidature = $this->candidatureRepository->recupererParClePrimaire($idCandidature);
        if ($candidature === null) throw new ServiceException("Candidature introuvable.");
        if ($candidature->getStatutCandidature() !== StatutCandidature::INSTRUCTION) {
            throw new ServiceException("La décision finale ne s'applique qu'aux candidatures en instruction.");
        }

        $utilisateur = $this->utilisateurRepository->recupererParClePrimaire($candidature->getIdUtilisateur());

        if ($decision === 'selectionne') {
            $candidature->setStatutCandidature(StatutCandidature::SELECTIONNE);
            if ($utilisateur !== null) {
                try {
                    $this->mailService->envoyerMailSelection(
                        $utilisateur->getEmailUtilisateur(),
                        $utilisateur->getPrenomUtilisateur(),
                        $candidature->getNomProjet()
                    );
                } catch (\Exception) {}
            }
        } elseif ($decision === 'refuse') {
            if (empty(trim($motifRefus ?? ''))) {
                throw new ServiceException("Le motif du refus est obligatoire.");
            }
            $candidature->setStatutCandidature(StatutCandidature::REFUSE);
            if ($utilisateur !== null) {
                try {
                    $this->mailService->envoyerMailRefus(
                        $utilisateur->getEmailUtilisateur(),
                        $utilisateur->getPrenomUtilisateur(),
                        $candidature->getNomProjet(),
                        trim($motifRefus)
                    );
                } catch (\Exception) {}
            }
        } else {
            throw new ServiceException("Décision invalide.");
        }

        $this->candidatureRepository->mettreAJour($candidature);
    }

    public function modifier(int $id, array $donnees): void
    {
        
        $candidature = $this->candidatureRepository->recupererParClePrimaire($id);
        if ($candidature === null) throw new ServiceException("Candidature introuvable");

        $nomProjet         = trim($donnees['nomProjet'] ?? '');
        $entreeStationTVal = trim($donnees['entreeStationT'] ?? '');
        $filiereCandidat   = $this->optionnel($donnees['filiereCandidat'] ?? null);
        $besoinTraite      = trim($donnees['besoinTraite'] ?? '');
        $descriptionProjet = trim($donnees['descriptionProjet'] ?? '');
        $innovationDiff    = trim($donnees['innovationDifferentiation'] ?? '');
        $maturiteVal       = trim($donnees['maturite'] ?? '');
        $modelEconomique   = trim($donnees['modelEconomique'] ?? '');
        $statutVal         = trim($donnees['statutCandidature'] ?? '');

        if ($nomProjet === '')         throw new ServiceException("Le nom du projet est obligatoire");
        if ($besoinTraite === '')      throw new ServiceException("Le besoin traité est obligatoire");
        if ($descriptionProjet === '') throw new ServiceException("La description est obligatoire");
        if ($innovationDiff === '')    throw new ServiceException("L'innovation / différentiation est obligatoire");
        if ($maturiteVal === '')       throw new ServiceException("Le stade de maturité est obligatoire");
        if ($modelEconomique === '')   throw new ServiceException("Le modèle économique est obligatoire");

        if ($entreeStationTVal !== '') {
            $entreeStationT = EntreeStationT::tryFrom($entreeStationTVal);
            if ($entreeStationT === null) throw new ServiceException("Entrée Station T invalide");
        } else {
            $entreeStationT = $candidature->getEntreeStationT();
        }

        if (!$filiereCandidat && $entreeStationT !== EntreeStationT::OPTION3) {
            throw new ServiceException("Veuillez sélectionner une filière (obligatoire pour l'option 1 et 2).");
        }

        $maturite = StageMaturite::tryFrom($maturiteVal);
        if ($maturite === null) throw new ServiceException("Stade de maturité invalide");

        $statut = $statutVal !== '' ? StatutCandidature::tryFrom($statutVal) : $candidature->getStatutCandidature();
        if ($statut === null) throw new ServiceException("Statut invalide");

        $candidature->setNomProjet($nomProjet);
        $candidature->setEntreeStationT($entreeStationT);
        $candidature->setFiliereCandidat($filiereCandidat ?? '');
        $candidature->setBesoinTraite($besoinTraite);
        $candidature->setDescriptionProjet($descriptionProjet);
        $candidature->setValeurAjoutee($this->optionnel($donnees['valeurAjoutee'] ?? null));
        $candidature->setInnovationDifferentiation($innovationDiff);
        $candidature->setMaturite($maturite);
        $candidature->setReferences_($this->optionnel($donnees['references'] ?? null) ?? '');
        $candidature->setObjectifPilote($this->optionnel($donnees['objectifPilote'] ?? null));
        $candidature->setPerimetreGeographique($this->optionnel($donnees['perimetreGeographique'] ?? null));
        $candidature->setDureeExperimentation($this->optionnel($donnees['dureeExperimentation'] ?? null));
        $candidature->setPublicsCibles($this->optionnel($donnees['publicsCibles'] ?? null) ?? '');
        $candidature->setBudgetMobiliser($this->optionnel($donnees['budgetMobiliser'] ?? null));
        $candidature->setMoyensMobiliser($this->optionnel($donnees['moyensMobiliser'] ?? null));
        $candidature->setConditionsReussite($this->optionnel($donnees['conditionsReussite'] ?? null));
        $candidature->setDerouteOperationnel($this->optionnel($donnees['derouteOperationnel'] ?? null));
        $candidature->setPartenairesRecherches($this->optionnel($donnees['partenairesRecherches'] ?? null));
        $candidature->setAppuisStationT($this->optionnel($donnees['appuisStationT'] ?? null));
        $candidature->setModelEconomique($modelEconomique);
        $candidature->setConditionsDeploiement($this->optionnel($donnees['conditionsDeploiement'] ?? null));
        $candidature->setImpactAttendu($this->optionnel($donnees['impactAttendu'] ?? null));
        $candidature->setConformite($this->optionnel($donnees['conformite'] ?? null));
        $candidature->setMesuresSecurisation($this->optionnel($donnees['mesuresSecurisation'] ?? null));
        $candidature->setPitchCourt($this->optionnel($donnees['pitchCourt'] ?? null));
        $candidature->setProblemeIdentifie($this->optionnel($donnees['problemeIdentifie'] ?? null));
        $candidature->setConcurrence($this->optionnel($donnees['concurrence'] ?? null));
        $candidature->setClientsCibles($this->optionnel($donnees['clientsCibles'] ?? null));
        $candidature->setPreuvesBesoin($this->optionnel($donnees['preuvesBesoin'] ?? null));
        $candidature->setEtatAvancement($this->optionnel($donnees['etatAvancement'] ?? null));
        $candidature->setBesoinsFinanciers($this->optionnel($donnees['besoinsFinanciers'] ?? null));
        $candidature->setEquipe($this->optionnel($donnees['equipe'] ?? null));
        $candidature->setForcesEquipe($this->optionnel($donnees['forcesEquipe'] ?? null));
        $candidature->setObjectifAccompagnement($this->optionnel($donnees['objectifAccompagnement'] ?? null));
        $candidature->setStatutCandidature($statut);

        $besoinsPrioritairesRaw = $donnees['besoinsPrioritaires'] ?? null;
        if (is_array($besoinsPrioritairesRaw) && !empty($besoinsPrioritairesRaw)) {
            $candidature->setBesoinsPrioritaires(json_encode(array_values($besoinsPrioritairesRaw)));
        } else {
            $candidature->setBesoinsPrioritaires(null);
        }

        if (!empty($donnees['programmeGigamed'])) {
            $candidature->setProgrammeGigamed(ProgrammeGigamed::tryFrom($donnees['programmeGigamed']));
        } else {
            $candidature->setProgrammeGigamed(null);
        }

        if (!empty($donnees['niveauAccompagnement'])) {
            $candidature->setNiveauAccompagnement(NiveauAccompagnement::tryFrom($donnees['niveauAccompagnement']));
        } else {
            $candidature->setNiveauAccompagnement(null);
        }

        $this->candidatureRepository->mettreAJour($candidature);
    }

    private function optionnel(?string $valeur): ?string
    {
        if ($valeur === null) return null;
        $valeur = trim($valeur);
        return $valeur === '' ? null : $valeur;
    }
}