<?php

namespace tests\pest\fakes;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;

class InMemoryRendezVousRepository implements RendezVousRepositoryInterface
{
    private array $rendezVous = [];

    public function save(RendezVous $rendezVous): void
    {
        $this->rendezVous[$rendezVous->getId()] = $rendezVous;
    }

    public function findById(string $id): RendezVous
    {
        if (!isset($this->rendezVous[$id])) {
            throw new \Exception('Rendez-vous not found');
        }

        return $this->rendezVous[$id];
    }
}