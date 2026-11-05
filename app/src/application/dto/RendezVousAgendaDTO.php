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
        public readonly string $etat,
        public readonly array $patient,
    ) {}

    public static function fromEntity(RendezVous $rdv): self
    {
        $nom = trim(($rdv->getPatientPrenom() ?? '') . ' ' . ($rdv->getPatientNom() ?? ''));

        return new self(
            $rdv->getId(),
            $rdv->getDateHeureDebut()->format('d/m/Y H:i'),
            $rdv->getDateHeureFin()->format('d/m/Y H:i'),
            $rdv->getDuree(),
            $rdv->getMotifLibelle(),
            $rdv->getEtat(),
            [
                'id'  => $rdv->getPatientId(),
                'nom' => $nom !== '' ? $nom : null,
            ]
        );
    }
}