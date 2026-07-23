# CLAUDE.md — Contexte projet Station T / Gigamed

## Entreprise

**Gigamed** est l'opérateur économique de la Communauté d'Agglomération Hérault Méditerranée (Cap d'Agde Méditerranée).

Le projet s'appelle **Station T** — une plateforme territoriale d'innovation dont la mission est de connecter les entreprises locales à des startups pour tester des solutions concrètes sur 6 filières prioritaires : tourisme, nautisme, commerce, agriculture, sport/bien-être, ville de demain.

---

## Stack technique

- **Langage** : PHP (MVC, namespaces)
- **Namespace** : `App\Gigamed`
- **Base de données** : MySQL 8 via Docker
- **Docker containers** :
  - `serveurWebIUT1` — PHP/Apache, port 80
  - `gigamed-mysql` — MySQL, port externe 3308 → interne 3306
  - `gigamed-phpmyadmin` — phpMyAdmin, port 8082
- **Réseau Docker** : `gigamed-network` (les containers sont connectés dessus)
- **Connexion PHP→MySQL** : hostname = `gigamed-mysql`, port = `3306`
- **phpMyAdmin** : `localhost:8082` — login `root` / `root`

### Fichier de configuration BDD

```php
namespace App\Gigamed\Configuration;
// hostname = "gigamed-mysql"
// port = "3306"
// nomBDD = "gigamed"
// login = "root"
// motDePasse = "root"
```

---

## Architecture du projet

```
src/
├── Modele/
│   ├── DataObject/
│   │   ├── Ami.php
│   │   ├── Utilisateur.php
│   │   ├── Structure.php
│   │   ├── Candidature.php
│   │   ├── Besoin.php
│   │   ├── Challenge.php
│   │   ├── Critere.php
│   │   ├── Document.php
│   │   ├── Journal.php
│   │   └── Enum/
│   │       ├── Filiere.php
│   │       ├── StatutAmi.php
│   │       ├── StatutCandidature.php
│   │       ├── RoleUtilisateur.php
│   │       └── TypeStructure.php
│   └── Repository/
│       ├── UtilisateurRepository.php
│       ├── AmiRepository.php
│       └── ...
├── Controleur/
└── Vue/
```

---

## Flux métier principal

```
BESOIN soumis par une entreprise (informel)
    ↓
Gigamed analyse
    ↓
    ├── → devient un CHALLENGE (formulaire détaillé, soumis par partenaire)
    ├── → devient directement un AMI
    └── → refusé mais conservé en base

CHALLENGE validé → devient un AMI
AMI ouvert → CANDIDATURE déposée par une structure
CANDIDATURE instruite → NOTATION par les agents
NOTATION → DÉCISION (sélectionné / refusé)
```

---

## Rôles utilisateurs

| Rôle | Droits |
|---|---|
| `superAdmin` | Tout |
| `admin` | Gère AMI, critères, utilisateurs, tableaux de bord |
| `agent` | Instruit et note les candidatures |
| `startup` | Dépose candidatures (dossier C), soumet besoins |
| `entreprise` | Soumet besoins uniquement (dossier B) |

---

## Base de données — Schéma relationnel complet

```sql
UTILISATEUR (idUtilisateur, nomUtilisateur, prenomUtilisateur,
             emailUtilisateur, telephoneUtilisateur, statutUtilisateur,
             mdpHache, roleUtilisateur, #idStructure)

STRUCTURE (idStructure, nomStructure, formeJuridique, numSIRET_RNA,
           dateCreation, adresseSiege, commune, siteWeb, effectif,
           chiffreAffaires, implantationTerritoire, implantationEnvisagee,
           secteurActivite, nombreAssocies, nombreSalaries, stadeMaturite,
           typeStructure, statutJuridique, referentNom, referentFonction,
           referentEmail, referentTelephone)

BESOIN (idBesoin, titreBesoin, filiere, objectifBesoin, publicVise,
        perimetrePrioritaire, vision, enjeuxMajeurs, besoinsPrioritaires,
        exemplesInnovations, partenaires, dateDepot, statutBesoin,
        #idUtilisateur)

CHALLENGE (idChallenge, titreChallenge, objectifChallenge, publicVise,
           perimetrePrioritaire, exemplesInnovations, casUsagePilote,
           perimetreCasUsage, dureeIndicative, sortieAttendue, partenaires,
           filiere, typeChallenge, dateDepot, statutChallenge, #idBesoin)

AMI (idAmi, titreAmi, objectifAmi, publicVise, perimetrePrioritaire,
     filiere, descriptionAmi, exemplesInnovations, casUsagePilote,
     perimetreCasUsage, dureeIndicative, sortieAttendue, partenaires,
     dateOuverture, dateCloture, dateAuditions, dateResultats,
     dateDemarrage, statutAmi, #idUtilisateur, #idBesoin)

CANDIDATURE (idCandidature, nomProjet, typeCandidature, entreeStationT,
             filiereCandidat, besoinTraite, descriptionSolution,
             valeurAjoutee, innovationDifferentiation, maturite,
             references, objectifPilote, perimetreGeographique,
             dureeExperimentation, publicsCibles, budgetMobiliser,
             moyensMobiliser, conditionsReussite, derouteOperationnel,
             partenairesRecherches, appuisStationT, modelEconomique,
             conditionsDeploiement, impactAttendu, conformite,
             mesuresSecurisation, engagements, pitchCourt,
             problemeIdentifie, concurrence, clientsCibles, preuvesBesoin,
             etatAvancement, besoinsFinanciers, equipe, forcesEquipe,
             programmeGigamed, niveauAccompagnement, besoinsPrioritaires,
             objectifsAccompagnement, dateDepot, statutCandidature,
             #idChallenge, #idAmi, #idStructure)

CRITERE (idCritere, libelle, ponderation)

NOTER (#idUtilisateur, #idCandidature, #idCritere,
       valeur, commentaire, dateNote)

AFFECTER (#idUtilisateur, #idCandidature, dateAffectation)

DOCUMENT (idDocument, nomDocument, typeDocument, cheminDocument,
          dateUpload, #idCandidature)

JOURNAL (idJournal, action, objet, horodatage, #idUtilisateur)

-- Tables intermédiaires critères
AMI_CRITERE (#idAmi, #idCritere)
CHALLENGE_CRITERE (#idChallenge, #idCritere)
```

---

## ENUM PHP à créer

```php
enum Filiere: string {
    case Tourisme = 'tourisme';
    case Nautisme = 'nautisme';
    case Commerce = 'commerce';
    case Agriculture = 'agriculture';
    case Sport = 'sport';
    case Ville = 'ville';
}

enum StatutAmi: string {
    case EnAttente = 'en attente';
    case Ouvert = 'ouvert';
    case Cloture = 'cloturé';
    case Archive = 'archivé';
}

enum StatutCandidature: string {
    case Brouillon = 'brouillon';
    case Depose = 'déposé';
    case Recevable = 'recevable';
    case Incomplet = 'incomplet';
    case EnInstruction = 'en instruction';
    case Selectionne = 'sélectionné';
    case Refuse = 'refusé';
}

enum RoleUtilisateur: string {
    case SuperAdmin = 'superAdmin';
    case Admin = 'admin';
    case Agent = 'agent';
    case Startup = 'startup';
    case Entreprise = 'entreprise';
}

enum StatutBesoin: string {
    case EnAttente = 'en attente';
    case TransformeChallenge = 'transformé challenge';
    case TransformeAmi = 'transformé AMI';
    case Refuse = 'refusé';
}

enum StatutChallenge: string {
    case Ouvert = 'ouvert';
    case Cloture = 'cloturé';
    case Archive = 'archivé';
}
```

---

## Cycle de vie d'une candidature

```
BROUILLON
    ↓ (startup soumet)
DÉPOSÉ
    ↓ (agent vérifie recevabilité)
RECEVABLE ←→ INCOMPLET
    ↓
EN INSTRUCTION
    ↓ (agents notent)
SÉLECTIONNÉ / REFUSÉ
```

Chaque changement de statut → insertion automatique dans JOURNAL.

---

## Critères de notation (7 critères officiels Station T)

1. Pertinence territoriale
2. Innovation utile
3. Faisabilité opérationnelle
4. Simplicité d'usage
5. Impact potentiel
6. Capacité d'exécution
7. Potentiel de déploiement

Les critères peuvent varier par AMI et par Challenge.
Score = Σ(note × pondération) / Σ(pondérations)

---

## Calendrier AMI Station T 2026

- Ouverture candidatures : 15 juin 2026
- Clôture : 4 septembre 2026 à 12h00
- Auditions : première quinzaine septembre 2026
- Résultats : 2 octobre 2026
- Démarrage : à partir d'octobre 2026

---

## Cas démonstrateurs

| AMI | Startup | Thématique |
|---|---|---|
| Emploi saisonnier | Banajob | Tourisme / RH |
| Services à l'enfance | Capitaine Aventure | Industries culturelles |
| Entraide touristes | WinWinTrip | Tourisme balnéaire |

---

## Contact entreprise

- Email : stationt@agglohm.net
- Téléphone : 06 59 71 38 57
- Adresse : Zone d'activité de la Capucière, 34550 Bessan
