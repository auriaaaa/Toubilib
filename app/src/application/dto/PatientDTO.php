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

// public function getId(): string { return $this->id; }
//     public function getNom(): string { return $this->nom; }
//     public function getPrenom(): string { return $this->prenom; }
//     public function getDateNaissance(): string { return $this->date_naissance; }
//     public function getAdresse(): string { return $this->adresse; }
//     public function getCodePostal(): string { return $this->code_postal; }
//     public function getVille(): string { return $this->ville; }
//     public function getEmail(): string { return $this->email; }
//     public function getTelephone(): string { return $this->telephone; }