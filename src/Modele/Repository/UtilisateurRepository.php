<?php

namespace App\Gigamed\Modele\Repository;

use App\Gigamed\Modele\Database\ConnexionBaseDeDonnees;
use App\Gigamed\Modele\DataObject\AbstractDataObject;
use App\Gigamed\Modele\DataObject\RoleUtilisateur;
use App\Gigamed\Modele\DataObject\Utilisateur;
use PDO;

class UtilisateurRepository extends AbstractRepository
{

    public function __construct(private ConnexionBaseDeDonnees $connexionBaseDeDonnees) {
        parent::__construct($connexionBaseDeDonnees);
    }
    public function construireDepuisTableauSQL(array $utilisateurFormatTableau): Utilisateur
    {
        return new Utilisateur(
            $utilisateurFormatTableau['nomUtilisateur'],
            $utilisateurFormatTableau['prenomUtilisateur'],
            $utilisateurFormatTableau['emailUtilisateur'],
            $utilisateurFormatTableau['telephoneUtilisateur'],
            $utilisateurFormatTableau['statutUtilisateur'],
            $utilisateurFormatTableau['mdpHache'],
            RoleUtilisateur::from($utilisateurFormatTableau['roleUtilisateur']),
            isset($utilisateurFormatTableau['idStructure']) ? (int) $utilisateurFormatTableau['idStructure'] : null,
            (bool) $utilisateurFormatTableau['emailVerifie'],
            $utilisateurFormatTableau['tokenVerification'] ?? null,
            isset($utilisateurFormatTableau['idUtilisateur']) ? (int) $utilisateurFormatTableau['idUtilisateur'] : null
        );
    }

    public function getNomTable(): string
    {
        return "Utilisateur";
    }

    public function getNomClePrimaire(): string
    {
        return "idUtilisateur";
    }

    public function getNomColonnes(): array
    {
        return [
            "idUtilisateur",
            "nomUtilisateur",
            "prenomUtilisateur",
            "emailUtilisateur",
            "telephoneUtilisateur",
            "statutUtilisateur",
            "mdpHache",
            "roleUtilisateur",
            "idStructure",
            "emailVerifie",
            "tokenVerification",
        ];
    }

    public function getNomColonnesExecute(): array
    {
        return [
            ":idUtilisateurTag",
            ":nomUtilisateurTag",
            ":prenomUtilisateurTag",
            ":emailUtilisateurTag",
            ":telephoneUtilisateurTag",
            ":statutUtilisateurTag",
            ":mdpHacheTag",
            ":roleUtilisateurTag",
            ":idStructureTag",
            ":emailVerifieTag",
            ":tokenVerificationTag",
        ];
    }

    public function formatTableauSQL(AbstractDataObject $utilisateur): array
    {
        
        return [
            "idUtilisateurTag"        => $utilisateur->getIdUtilisateur(),
            "nomUtilisateurTag"       => $utilisateur->getNomUtilisateur(),
            "prenomUtilisateurTag"    => $utilisateur->getPrenomUtilisateur(),
            "emailUtilisateurTag"     => $utilisateur->getEmailUtilisateur(),
            "telephoneUtilisateurTag" => $utilisateur->getTelephoneUtilisateur(),
            "statutUtilisateurTag"    => $utilisateur->getStatutUtilisateur(),
            "mdpHacheTag"             => $utilisateur->getMdpHache(),
            "roleUtilisateurTag"      => $utilisateur->getRoleUtilisateur()->value,
            "idStructureTag"          => $utilisateur->getIdStructure(),
            "emailVerifieTag"         => (int) $utilisateur->isEmailVerifie(),
            "tokenVerificationTag"    => $utilisateur->getTokenVerification(),
        ];
    }

    public function recupererIdLastInsert(\PDO $pdo, AbstractDataObject $utilisateur): void
    {
        
        $utilisateur->setIdUtilisateur((int) $pdo->lastInsertId());
    }

    public function recupererParToken(string $token): ?Utilisateur
    {
        $sql = "SELECT * FROM Utilisateur WHERE tokenVerification = :tokenTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["tokenTag" => $token]);
        $objectFormatTableau = $pdoStatement->fetch();
        if ($objectFormatTableau === false) {
            return null;
        }
        return $this->construireDepuisTableauSQL($objectFormatTableau);
    }

    public function recupererParRole(RoleUtilisateur ...$roles): array
    {
        $placeholders = implode(', ', array_map(fn($i) => ":role$i", array_keys($roles)));
        $sql = "SELECT * FROM Utilisateur WHERE roleUtilisateur IN ($placeholders)";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        foreach ($roles as $i => $role) {
            $pdoStatement->bindValue(":role$i", $role->value);
        }
        $pdoStatement->execute();
        $utilisateurs = [];
        foreach ($pdoStatement as $t) {
            $utilisateurs[] = $this->construireDepuisTableauSQL($t);
        }
        return $utilisateurs;
    }

    public function recupererParEmail(string $email): ?Utilisateur
    {
        $sql = "SELECT * FROM Utilisateur WHERE emailUtilisateur = :emailTag";
        $pdoStatement = $this->connexionBaseDeDonnees->getPdo()->prepare($sql);
        $pdoStatement->execute(["emailTag" => $email]);
        $objectFormatTableau = $pdoStatement->fetch();
        if ($objectFormatTableau === false) {
            return null;
        }
        return $this->construireDepuisTableauSQL($objectFormatTableau);
    }
}