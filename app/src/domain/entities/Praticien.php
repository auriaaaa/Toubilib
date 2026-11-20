<?php 

namespace toubilib\domain\entities;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use toubilib\domain\exceptions\PraticienIndisponibleException;

class Praticien
{
    private const MOTIF_DUREE = [
        'CI' => 30,
        'CO' => 20,
        'CS' => 15,
    ];

    // Plage horaire des RDV acceptés
    private const HEURE_OUVERTURE = 8 * 60;
    private const HEURE_FERMETURE = 18 * 60;

    private string $id;
    private string $nom;
    private string $prenom;
    private string $ville;
    private string $email;
    private string $telephone;
    private bool $organisation;
    private bool $nouveau_patient;
    private string $titre;

    private Collection $rendezVous;
    private Specialite $specialite;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        string $telephone,
        Specialite $specialite,
        bool $organisation,
        bool $nouveau_patient,
        string $titre
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->specialite = $specialite;
        $this->organisation = $organisation;
        $this->nouveau_patient = $nouveau_patient;
        $this->titre = $titre;
        $this->rendezVous = new ArrayCollection();
    }

    // -------------------------------------------------------------- Getters

    public function getId(): string { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function getNom(): string { return $this->nom; }
    public function getVille(): string { return $this->ville; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getIsOrganisation(): bool { return $this->organisation; }
    public function getIsNewPatient(): bool { return $this->nouveau_patient; }
    public function getTitre(): string { return $this->titre; }
    public function getSpecialite(): ?Specialite { return $this->specialite; }
    public function getRendezVous(): Collection { return $this->rendezVous; }

    // -------------------------------------------------------------- Setters

    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setVille(string $ville): void { $this->ville = $ville; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setIsOrganisation(bool $organisation): void { $this->organisation = $organisation; }
    public function setIsNewPatient(bool $nouveau_patient): void { $this->nouveau_patient = $nouveau_patient; }
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setSpecialite(Specialite $specialite): void { $this->specialite = $specialite;}

    public function ajouterRendezVous(RendezVous $rendezVous): void {
        $this->rendezVous[] = $rendezVous;
    }

    // -------------------------------------------------------------- Méthodes métiers


    public function verifierMotif($motif_visite) : bool {

        $motif_existe = array_key_exists($motif_visite, self::MOTIF_DUREE);

        if (!$motif_existe) {
            throw new \InvalidArgumentException("Motif de visite invalide : $motif_visite");
        }

        return $motif_existe;
    }

    public function duree($motif_visite): int
    {
        $this->verifierMotif($motif_visite);

        return self::MOTIF_DUREE[$motif_visite];
    }

    public function rdvPossible($date_heure_debut, $duree): bool
    {
       
        // Jour ouvrable (en semaine)
        $jour = (int)$date_heure_debut->format('N');

        if ($jour < 1 || $jour > 5) {
            throw new \InvalidArgumentException("Le praticien ne reçoit pas le week-end.");
        }

        // Horaire de travail
        $debut = (int) $date_heure_debut->format('G') * 60 + (int) $date_heure_debut->format('i');
        $fin = $debut + $duree;
        
        if ($debut < self::HEURE_OUVERTURE || $fin > self::HEURE_FERMETURE) {
            throw new \InvalidArgumentException("Le rendez-vous doit se dérouler entre 8h00 et 18h00.");
        }

        return true;
    }

    public function verifierDisponibilite(RendezVous $rdvDemande): void
    {
        foreach ($this->rendezVous as $rdvExistant) {
            if ($rdvExistant->getStatut() === 1) {
                continue;
            }

            if ($rdvExistant->chevauche($rdvDemande)) {
                throw new PraticienIndisponibleException("Le praticien a déjà un rendez-vous sur ce creneau");
            }
        }
    }

    public function getRendezVousPourPeriode(
        DateTime $debut,
        DateTime $fin
    ): array {
        $rendezVousFiltres = [];

        foreach ($this->rendezVous as $rdv) {
            if (
                $rdv->getDateHeureDebut() < $fin
                && $rdv->getDateHeureFin() > $debut
                && $rdv->getStatut() !== 1
            ) {
                $rendezVousFiltres[] = $rdv;
            }
        }

        return $rendezVousFiltres;
    }
}