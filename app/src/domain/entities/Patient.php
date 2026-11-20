<?php

namespace toubilib\domain\entities;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Patient
{
    private string $id;
    private string $nom;
    private string $prenom;
    private DateTime $date_naissance;
    private string $adresse;
    private string $code_postal;
    private string $ville;
    private string $email;
    private string $telephone;
    
    private Collection $rendezVous;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        DateTime $date_naissance,
        string $adresse,
        string $code_postal,
        string $ville,
        string $email,
        string $telephone
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->date_naissance = $date_naissance;
        $this->adresse = $adresse;
        $this->code_postal = $code_postal;  
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->rendezVous = new ArrayCollection();
    }

    // -------------------------------------------------------------- Getters

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getDateNaissance(): ?DateTime { return $this->date_naissance; }
    public function getAdresse(): string { return $this->adresse; }
    public function getCodePostal(): string { return $this->code_postal; }
    public function getVille(): string { return $this->ville; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getRendezVous(): Collection { return $this->rendezVous; }

    // -------------------------------------------------------------- Setters

    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setDateNaissance(DateTime $date_naissance): void { $this->date_naissance = $date_naissance; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setCodePostal(string $code_postal): void { $this->code_postal = $code_postal; }
    public function setVille(string $ville): void { $this->ville = $ville; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }

    public function ajouterRendezVous(RendezVous $rendezVous): void
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
        }
    }
}