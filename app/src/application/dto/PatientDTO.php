<?php

namespace toubilib\application\dto;

use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\Patient;

final class PatientDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $date_naissance,
        public readonly string $adresse,
        public readonly string $code_postal,
        public readonly string $ville,
        public readonly string $email,
        public readonly string $telephone
    ) {}

    public static function fromEntity(Patient $patient): self
    {
        return new self(
            $patient->getId(),
            $patient->getNom(),
            $patient->getPrenom(),
            $patient->getDateNaissance(),
            $patient->getAdresse(),
            $patient->getCodePostal(),
            $patient->getVille(),
            $patient->getEmail(),
            $patient->getTelephone()
        );
    }
}
