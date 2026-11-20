<?php
namespace toubilib\application\ports\spi;

use DateTime;
use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    public function save(RendezVous $rendezVous): void;

    public function findById(string $id): RendezVous;

    public function findByPatientId(string $patientId): array; // return RendezVous[]

    public function findPrevusPourPraticien(string $praticienId, DateTime $debut, DateTime $fin): array; // return RendezVous[]
} 