<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTime;
use toubilib\application\ports\api\ServicePatientInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\dto\PatientDTO;
use toubilib\application\dto\RendezVousDTO;

class ServicePatient implements ServicePatientInterface
{
    private PatientRepositoryInterface $patientRepository;
    private RendezVousRepositoryInterface $rendezVousRepository;

    public function __construct(
        PatientRepositoryInterface $patientRepository,
        RendezVousRepositoryInterface $rendezVousRepository
    ) {
        $this->patientRepository = $patientRepository;
        $this->rendezVousRepository = $rendezVousRepository;
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

    public function getHistoriqueRendezVous(string $patientId): array
    {
        $this->patientRepository->findById($patientId);

        $resultat = [];

        foreach ($this->rendezVousRepository->findByPatientId($patientId) as $rdv) {
            $resultat[] = RendezVousDTO::fromEntity($rdv);
        }

        return $resultat;
    }
}


