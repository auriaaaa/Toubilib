<?php

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

final class PraticienDetailDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $titre,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $email,
        public readonly string $telephone,
        public readonly string $ville,
        public readonly bool $accepte_nouveau_patient,
        public readonly ?string $specialite,
        public readonly ?string $specialite_description,
    ) {}

    public static function fromEntity(Praticien $praticien): self
    {
        $specialite = $praticien->getSpecialite();

        return new self(
            $praticien->getId(),
            $praticien->getTitre(),
            $praticien->getNom(),
            $praticien->getPrenom(),
            $praticien->getEmail(),
            $praticien->getTelephone(),
            $praticien->getVille(),
            $praticien->isNewPatient(),
            $specialite?->getLibelle(),
            $specialite?->getDescription(),
        );
    }
}