<?php

declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class InMemoryPatientRepository implements PatientRepositoryInterface
{
    private array $patients = [];

    public function save(Patient $patient): void
    {
        $this->patients[$patient->getId()] = $patient;
    }

    public function findById(string $id): Patient
    {
        if (!isset($this->patients[$id])) {
            throw new PatientNotFoundException("Aucun patient trouvé avec l'ID : $id");
        }

        return $this->patients[$id];
    }

    public function findAll(): array
    {
        return array_values($this->patients);
    }

    public function existe(string $id): bool
    {
        return isset($this->patients[$id]);
    }
}
