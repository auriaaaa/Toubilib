<?php

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final class RendezVousDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $praticien_id,
        public readonly ?string $praticien_nom,
        public readonly string $patient_id,
        public readonly ?string $patient_nom,
        public readonly string $date_debut,
        public readonly string $date_creation,
        public readonly int $duree,
        public readonly string $motif_visite,
        public readonly string $etat,
    ) {}

    public static function fromEntity(RendezVous $rdv): self
    {
        return new self(
            $rdv->getId(),
            $rdv->getPraticienId(),
            self::nomComplet($rdv->getPraticienPrenom(), $rdv->getPraticienNom()),
            $rdv->getPatientId(),
            self::nomComplet($rdv->getPatientPrenom(), $rdv->getPatientNom()),
            $rdv->getDateHeureDebut()->format('d/m/Y H:i'),
            $rdv->getDateCreation()->format('d/m/Y'),
            $rdv->getDuree(),
            $rdv->getMotifLibelle(),
            $rdv->getEtat(),
        );
    }

    private static function nomComplet(?string $prenom, ?string $nom): ?string
    {
        if ($prenom === null && $nom === null) {
            return null;
        }

        return trim($prenom . ' ' . $nom);
    }
}