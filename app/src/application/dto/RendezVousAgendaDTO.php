<?php

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

final class RendezVousAgendaDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $date_heure_debut,
        public readonly string $date_heure_fin,
        public readonly int $duree,
        public readonly string $motif_visite,
        public readonly int $statut,
        public readonly string $patient_id,
        public readonly ?string $patient_nom,
        public readonly ?string $patient_prenom,
    ) {}

    public static function fromEntity(RendezVous $rdv): self
    {
        return new self(
            $rdv->getId(),
            $rdv->getDateHeureDebut()->format('d/m/Y à H:i'),
            $rdv->getDateHeureFin()->format('d/m/Y à H:i'),
            $rdv->getDuree(),
            $rdv->getMotifVisite(),
            $rdv->getStatut(),
            $rdv->getPatientId(),
            $rdv->getPatientNom(),
            $rdv->getPatientPrenom(),
        );
    }
}