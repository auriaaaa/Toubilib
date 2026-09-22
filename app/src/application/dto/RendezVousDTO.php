<?php

namespace toubilib\application\dto;

use DateTime;

use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\RendezVous;

final class RendezVousDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $praticien_id,
        public readonly string $patient_id,
        public readonly DateTime $date_heure_debut,
        public readonly int $duree,
        public readonly int $statut,

    ) {}

    public static function fromEntity(RendezVous $rendezVous): self
    {
        return new self(
            $rendezVous->getId(),
            $rendezVous->getPraticienId(),
            $rendezVous->getPatientId(),
            $rendezVous->getDateHeureDebut(),
            $rendezVous->getDuree(),
            $rendezVous->getStatut(),
        );
    }
}