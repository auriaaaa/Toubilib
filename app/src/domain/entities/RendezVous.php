<?php

namespace toubilib\domain\entities;

use DateTime;

use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

enum Duree: int
{
    case CI = 30;
    case C0 = 20;
    case CS = 15;
}

class RendezVous
{
    private string $id;
    private string $praticien_id;
    private string $patient_id;
    private DateTime $date_heure_debut;
    private DateTime $date_heure_fin;   // selon la durée du rendez-vous
    private DateTime $date_creation;    
    private int $statut;
    private int $duree;                 // selon le motif de visite
    private string $motif_visite; 

    public function __construct(
        string $id,
        string $praticien_id,
        string $patient_id,
        DateTime $date_heure_debut,
        DateTime $date_creation,
        int $statut,
        string $motif_visite,
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->date_heure_debut = $date_heure_debut;
        $this->date_creation = $date_creation;
        $this->statut = $statut;
        $this->motif_visite = $motif_visite;

        $this->duree = Duree::CI->value;
        $this->date_heure_fin = (clone $date_heure_debut)
            ->modify("+{$this->duree} minutes");
    }

    public function getId(): string { return $this->id; }
    public function getPraticienId(): string { return $this->praticien_id; }
    public function getPatientId(): string { return $this->patient_id; }
    public function getDateHeureDebut(): DateTime { return $this->date_heure_debut; }
    public function getDateHeureFin(): DateTime { return $this->date_heure_fin; }
    public function getDateCreation(): DateTime{ return $this->date_creation; }
    public function getStatut(): int { return $this->statut; }
    public function getDuree(): int { return $this->duree; }
    public function getMotifVisite(): string { return $this->motif_visite; }


    public function annuler(): void
    {
        if ($this->date_heure_debut < new DateTime()) {
            throw new RendezVousDejaPasseException("Le rendez-vous est déjà passé et ne peut pas être annulé.");
        }

        if ($this->statut === 0) {
            $this->statut = 1; 
        } elseif ($this->statut === 1) {
            throw new StatutRendezVousInvalideException("Le rendez-vous est déjà annulé.");
        } elseif ($this->statut === 2 || $this->statut === 3) {
            throw new StatutRendezVousInvalideException("Le rendez-vous est déjà passé et ne peut pas être annulé.");
        } else {
            throw new StatutRendezVousInvalideException("Statut de rendez-vous invalide.");
        }
               
    }

}