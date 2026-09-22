<?php

namespace tests\pest\fakes;

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

    public function findAll(): array
    {
        return array_values($this->praticiens);
    }
}