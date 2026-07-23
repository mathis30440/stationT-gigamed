<?php

namespace App\Gigamed\Modele\DataObject;

class Utilisateur extends AbstractDataObject
{
    private ?int $idUtilisateur;
    private string $nomUtilisateur;
    private string $prenomUtilisateur;
    private string $emailUtilisateur;
    private string $telephoneUtilisateur;
    private string $statutUtilisateur;
    private string $mdpHache;
    private RoleUtilisateur $roleUtilisateur;
    private ?int $idStructure;
    private bool $emailVerifie;
    private ?string $tokenVerification;

    

    public function __construct(string $nomUtilisateur, string $prenomUtilisateur, string $emailUtilisateur, string $telephoneUtilisateur, string $statutUtilisateur, string $mdpHache, RoleUtilisateur $roleUtilisateur, ?int $idStructure, bool $emailVerifie = false, ?string $tokenVerification = null, ?int $idUtilisateur = null)
    {
        $this->idUtilisateur = $idUtilisateur;
        $this->nomUtilisateur = $nomUtilisateur;
        $this->prenomUtilisateur = $prenomUtilisateur;
        $this->emailUtilisateur = $emailUtilisateur;
        $this->telephoneUtilisateur = $telephoneUtilisateur;
        $this->statutUtilisateur = $statutUtilisateur;
        $this->mdpHache = $mdpHache;
        $this->roleUtilisateur = $roleUtilisateur;
        $this->idStructure = $idStructure;
        $this->emailVerifie = $emailVerifie;
        $this->tokenVerification = $tokenVerification;
    }

    public function getIdUtilisateur(): ?int
    {
        return $this->idUtilisateur;
    }

    public function getNomUtilisateur(): string
    {
        return $this->nomUtilisateur;
    }

    public function getPrenomUtilisateur(): string
    {
        return $this->prenomUtilisateur;
    }

    public function getEmailUtilisateur(): string
    {
        return $this->emailUtilisateur;
    }

    public function getTelephoneUtilisateur(): string
    {
        return $this->telephoneUtilisateur;
    }

    public function getStatutUtilisateur(): string
    {
        return $this->statutUtilisateur;
    }

    public function getMdpHache(): string
    {
        return $this->mdpHache;
    }

    public function getRoleUtilisateur(): RoleUtilisateur
    {
        return $this->roleUtilisateur;
    }

    public function getIdStructure(): ?int
    {
        return $this->idStructure;
    }

    public function setIdStructure(?int $idStructure): void
    {
        $this->idStructure = $idStructure;
    }

    public function setNomUtilisateur(string $nomUtilisateur): void
    {
        $this->nomUtilisateur = $nomUtilisateur;
    }

    public function setPrenomUtilisateur(string $prenomUtilisateur): void
    {
        $this->prenomUtilisateur = $prenomUtilisateur;
    }

    public function setEmailUtilisateur(string $emailUtilisateur): void
    {
        $this->emailUtilisateur = $emailUtilisateur;
    }

    public function setTelephoneUtilisateur(string $telephoneUtilisateur): void
    {
        $this->telephoneUtilisateur = $telephoneUtilisateur;
    }

    public function setStatutUtilisateur(string $statutUtilisateur): void
    {
        $this->statutUtilisateur = $statutUtilisateur;
    }

    public function setMdpHache(string $mdpHache): void
    {
        $this->mdpHache = $mdpHache;
    }

    public function setRoleUtilisateur(RoleUtilisateur $roleUtilisateur): void
    {
        $this->roleUtilisateur = $roleUtilisateur;
    }

    public function setIdUtilisateur(?int $idUtilisateur): void
    {
        $this->idUtilisateur = $idUtilisateur;
    }

    public function isEmailVerifie(): bool
    {
        return $this->emailVerifie;
    }

    public function setEmailVerifie(bool $emailVerifie): void
    {
        $this->emailVerifie = $emailVerifie;
    }

    public function getTokenVerification(): ?string
    {
        return $this->tokenVerification;
    }

    public function setTokenVerification(?string $tokenVerification): void
    {
        $this->tokenVerification = $tokenVerification;
    }
}