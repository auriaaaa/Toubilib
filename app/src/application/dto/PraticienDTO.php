<?php

namespace toubilib\application\dto;

use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\Praticien;

final class PraticienDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $ville,
        public readonly string $email,
        public readonly string $telephone,
        public readonly string $specialite_id,
        public readonly string $titre
    ) {}

    public static function fromEntity(Praticien $praticien): self
    {
        return new self(
            $praticien->getId(),
            $praticien->getNom(),
            $praticien->getPrenom(),
            $praticien->getVille(),
            $praticien->getEmail(),
            $praticien->getTelephone(),
            $praticien->getSpecialiteId(),
            $praticien->getTitre()
        );
    }
}