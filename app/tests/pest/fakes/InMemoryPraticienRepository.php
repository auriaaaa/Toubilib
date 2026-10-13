<?php

namespace tests\pest\fakes;

use DateTime;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;

class InMemoryPraticienRepository implements PraticienRepositoryInterface
{
    private array $praticiens = [];

    public function save(Praticien $praticien): void
    {
        $this->praticiens[$praticien->getId()] = $praticien;
    }

    public function findById(string $id): Praticien
    {
        if (!isset($this->praticiens[$id])) {
            throw new \Exception('Praticien not found');
        }

        return $this->praticiens[$id];
    }

    public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien
    {
        return $this->findById($id);
    }

    public function findAll(): array
    {
        return array_values($this->praticiens);
    }

    public function existe(string $id): bool
    {
        return isset($this->praticiens[$id]);
    }
}