<?php

namespace App\Gigamed\Service;

interface JournalServiceInterface
{
    public function ajouter(string $action, string $objet, int $idUtilisateur): void;
    public function recupererTousAvecNom(int $limite = 500): array;
}