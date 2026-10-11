<?php 

namespace toubilib\domain\entities;

use toubilib\domain\exceptions\PraticienIndisponibleException;

class Praticien
{
    private const MOTIF_DUREE = [
        'CI' => 30,
        'C0' => 20,
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
    private int $specialite_id;
    private string $structure_id;
    private string $rpps_id;
    private bool $organisation;
    private bool $nouveau_patient;
    private string $titre;

    private array $rendezVous = [];

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        string $telephone,
        int $specialite_id,
        string $structure_id,
        string $rpps_id,
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
        $this->specialite_id = $specialite_id;
        $this->structure_id = $structure_id;
        $this->rpps_id = $rpps_id;
        $this->organisation = $organisation;
        $this->nouveau_patient = $nouveau_patient;
        $this->titre = $titre;
    }

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getSpecialiteId(): int { return $this->specialite_id; }
    public function getVille(): string { return $this->ville; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getStructureId(): string { return $this->structure_id; }
    public function getRppsId(): string { return $this->rpps_id; }
    public function isOrganisation(): bool { return $this->organisation; }
    public function isNewPatient(): bool { return $this->nouveau_patient; }
    public function getTitre(): string { return $this->titre; }

    public function getRendezVous(): array { return $this->rendezVous; }

    public function ajouterRendezVous(RendezVous $rendezVous): void
    {
        $this->rendezVous[] = $rendezVous;
    }

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

}