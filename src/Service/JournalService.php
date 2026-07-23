<?php

namespace App\Gigamed\Service;

use App\Gigamed\Modele\DataObject\Journal;
use App\Gigamed\Modele\Repository\JournalRepository;

class JournalService implements JournalServiceInterface
{
    public function __construct(private JournalRepository $journalRepository) {}

    public function ajouter(string $action, string $objet, int $idUtilisateur): void
    {
        $journal = new Journal($action, $objet, new \DateTime(), $idUtilisateur);
        $this->journalRepository->ajouter($journal);
    }

    public function recupererTousAvecNom(int $limite = 500): array
    {
        return $this->journalRepository->recupererTousAvecNom($limite);
    }
}