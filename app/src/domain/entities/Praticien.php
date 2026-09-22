<?php 

namespace toubilib\domain\entities;

class Praticien
{
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

}