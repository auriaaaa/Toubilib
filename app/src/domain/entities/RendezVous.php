<?php

namespace toubilib\domain\entities;

use DateTime;

use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

class RendezVous
{
    private string $id;
    private string $praticien_id;
    private string $patient_id;
    private DateTime $date_heure_debut;
    private DateTime $date_heure_fin;
    private DateTime $date_creation;    
    private int $statut;               
    private int $duree;                 
    private string $motif_visite; 

    public function __construct(
        string $id,
        string $praticien_id,
        string $patient_id,
        DateTime $date_heure_debut,
        DateTime $date_heure_fin,
        DateTime $date_creation,
        string $statut,
        int $duree,
        string $motif_visite,
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->date_heure_debut = $date_heure_debut;
        $this->date_heure_fin = $date_heure_fin;
        $this->date_creation = $date_creation;
        $this->statut = $statut;
        $this->duree = $duree;
        $this->motif_visite = $motif_visite;
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
    
    public static function creer(
        Praticien $praticien,
        Patient $patient,
        DateTime $date_heure_debut,
        string $motif_visite,
        ?DateTime $maintenant = null
    ): RendezVous{

        $maintenant = $maintenant ?? new DateTime();

        $praticien->verifierMotif($motif_visite);
        $duree = $praticien->duree($motif_visite);

        $debut = clone $date_heure_debut;
        if ($debut < $maintenant) {
            throw new \InvalidArgumentException("La date et l'heure de début du rendez-vous ne peuvent pas être dans le passé.");
        }
        $praticien->rdvPossible($debut, $duree);

        $fin = (clone $debut)->modify("+{$duree} minutes");

        $rendezVous = new self(
            uniqid(),
            $praticien->getId(),
            $patient->getId(),
            $debut,
            $fin,
            clone $maintenant,
            0,
            $duree,
            $motif_visite
        );

        $praticien->verifierDisponibilite($rendezVous);

        return $rendezVous;

    }

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

    public function chevauche(RendezVous $rdv): bool
    {
        return $this->date_heure_debut < $rdv->date_heure_fin
            && $rdv->date_heure_debut < $this->date_heure_fin;
    }

}