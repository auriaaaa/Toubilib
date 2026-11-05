<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use DateTime;
use toubilib\application\dto\PatientDTO;

interface ServicePatientInterface
{
    public function getPatient(string $id): PatientDTO;

    public function getAllPatients(): array; // return PatientDTO[]
}