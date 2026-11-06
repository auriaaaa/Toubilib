<?php

namespace tests\pest\fakes;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousNotFoundException;

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
            throw new RendezVousNotFoundException("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        return $this->rendezVous[$id];
    }

    public function findByPatientId(string $patientId): array
    {
        $resultat = [];

        foreach ($this->rendezVous as $rdv) {
            if ($rdv->getPatientId() === $patientId) {
                $resultat[] = $rdv;
            }
        }

        return $resultat;
    }
}