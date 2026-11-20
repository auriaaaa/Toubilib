<?php

namespace toubilib\domain\entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Specialite
{
    private int $id;
    private string $libelle;
    private string $description;
    private Collection $praticiens;

    public function __construct(int $id, string $libelle, string $description)
    {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
        $this->praticiens = new ArrayCollection();
    }

    // -------------------------------------------------------------- Getters

    public function getId(): int { return $this->id; }
    public function getLibelle(): string { return $this->libelle; }
    public function getDescription(): string { return $this->description; }
    public function getPraticiens(): Collection { return $this->praticiens; }

    // -------------------------------------------------------------- Setters

    public function setLibelle(string $libelle): void { $this->libelle = $libelle; }
    public function setDescription(string $description): void { $this->description = $description; }
}