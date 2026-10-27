<?php
namespace toubilib\application\ports\spi;

use DateTime;
use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    public function save(Praticien $praticien): void;

    public function findById(string $id): Praticien;

    public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien;

    public function findAll(): array;

    public function rechercher(?int $specialiteId, ?string $ville): array;

    public function existe(string $id): bool;
} 