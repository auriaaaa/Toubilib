<?php

declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;

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
            throw new \Exception('Patient not found');
        }

        return $this->patients[$id];
    }

    public function existe(string $id): bool
    {
        return isset($this->patients[$id]);
    }
}
