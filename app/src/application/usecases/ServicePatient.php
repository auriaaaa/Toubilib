<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTime;
use toubilib\application\ports\api\ServicePatientInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\dto\PatientDTO;

class ServicePatient implements ServicePatientInterface
{
    private PatientRepositoryInterface $patientRepository;

    public function __construct(PatientRepositoryInterface $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    public function getPatient(string $id): PatientDTO
    {
        $patient = $this->patientRepository->findById($id);
        return PatientDTO::fromEntity($patient);
    }

    public function getAllPatients(): array
    {
        $resultat = [];

        foreach ($this->patientRepository->findAll() as $patient) {
            $resultat[] = PatientDTO::fromEntity($patient);
        }

        return $resultat;
    }
}


