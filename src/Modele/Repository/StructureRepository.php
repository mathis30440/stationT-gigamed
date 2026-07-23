<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\StatutJuridiqueE;
use App\Gigamed\Modele\DataObject\StatutJuridiqueS;
use App\Gigamed\Modele\DataObject\Structure;
use App\Gigamed\Modele\DataObject\TypeStructure;

class StructureRepository extends AbstractRepository
{
    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees)
    {
        parent::__construct($connexionBaseDeDonnees);
    }

    public function getNomTable(): string
    {
        return "Structure";
    }

    public function getNomClePrimaire(): string
    {
        return "idStructure";
    }

    public function getNomColonnes(): array
    {
        return [
            "idStructure",
            "nomStructure",
            "formeJuridique",
            "numSIRET_RNA",
            "dateCreation",
            "adresseSiege",
            "commune",
            "siteWeb",
            "effectif",
            "chiffreAffaires",
            "implantationTerritoire",
            "implantationEnvisagee",
            "nombreAssocies",
            "nombreSalaries",
            "statutJuridiqueS",
            "statutJuridiqueE",
            "typeStructure",
            "referentNom",
            "referentFonction",
            "referentEmail",
            "referentTelephone",
        ];
    }

    public function getNomColonnesExecute(): array
    {
        return [
            ":idStructureTag",
            ":nomStructureTag",
            ":formeJuridiqueTag",
            ":numSIRET_RNATag",
            ":dateCreationTag",
            ":adresseSiegeTag",
            ":communeTag",
            ":siteWebTag",
            ":effectifTag",
            ":chiffreAffairesTag",
            ":implantationTerritoireTag",
            ":implantationEnvisageeTag",
            ":nombreAssociesTag",
            ":nombreSalariesTag",
            ":statutJuridiqueSTag",
            ":statutJuridiqueETag",
            ":typeStructureTag",
            ":referentNomTag",
            ":referentFonctionTag",
            ":referentEmailTag",
            ":referentTelephoneTag",
        ];
    }

    public function formatTableauSQL(AbstractDataObject $objet): array
    {
        
        $implantationT = $objet->isImplantationTerritoire();
        $implantationE = $objet->isImplantationEnvisagee();
        return [
            "idStructureTag"             => $objet->getIdStructure(),
            "nomStructureTag"            => $objet->getNomStructure(),
            "formeJuridiqueTag"          => $objet->getFormeJuridique(),
            "numSIRET_RNATag"            => $objet->getNumSIRET_RNA(),
            "dateCreationTag"            => $objet->getDateCreation()->format('Y-m-d'),
            "adresseSiegeTag"            => $objet->getAdresseSiege(),
            "communeTag"                 => $objet->getCommune(),
            "siteWebTag"                 => $objet->getSiteWeb(),
            "effectifTag"                => $objet->getEffectif(),
            "chiffreAffairesTag"         => $objet->getChiffreAffaires(),
            "implantationTerritoireTag"  => $implantationT !== null ? (int) $implantationT : null,
            "implantationEnvisageeTag"   => $implantationE !== null ? (int) $implantationE : null,
            "nombreAssociesTag"          => $objet->getNombreAssocies(),
            "nombreSalariesTag"          => $objet->getNombreSalaries(),
            "statutJuridiqueSTag"        => $objet->getStatutJuridiqueS()?->value,
            "statutJuridiqueETag"        => $objet->getStatutJuridiqueE()?->value,
            "typeStructureTag"           => $objet->getTypeStructure()->value,
            "referentNomTag"             => $objet->getReferentNom(),
            "referentFonctionTag"        => $objet->getReferentFonction(),
            "referentEmailTag"           => $objet->getReferentEmail(),
            "referentTelephoneTag"       => $objet->getReferentTelephone(),
        ];
    }

    public function construireDepuisTableauSQL(array $t): Structure
    {
        return new Structure(
            $t['nomStructure'],
            $t['formeJuridique'],
            $t['numSIRET_RNA'],
            new \DateTime($t['dateCreation']),
            $t['adresseSiege'],
            $t['commune'],
            $t['siteWeb'] ?? null,
            isset($t['effectif']) ? (int) $t['effectif'] : null,
            isset($t['chiffreAffaires']) ? (float) $t['chiffreAffaires'] : null,
            isset($t['implantationTerritoire']) ? (bool) $t['implantationTerritoire'] : null,
            isset($t['implantationEnvisagee']) ? (bool) $t['implantationEnvisagee'] : null,
            isset($t['nombreAssocies']) ? (int) $t['nombreAssocies'] : null,
            isset($t['nombreSalaries']) ? (int) $t['nombreSalaries'] : null,
            isset($t['statutJuridiqueS']) ? StatutJuridiqueS::from($t['statutJuridiqueS']) : null,
            isset($t['statutJuridiqueE']) ? StatutJuridiqueE::from($t['statutJuridiqueE']) : null,
            TypeStructure::from($t['typeStructure']),
            $t['referentNom'],
            $t['referentFonction'],
            $t['referentEmail'],
            $t['referentTelephone'],
            isset($t['idStructure']) ? (int) $t['idStructure'] : null
        );
    }

    public function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $objet): void
    {
        
        $objet->setIdStructure((int) $pdo->lastInsertId());
    }
}