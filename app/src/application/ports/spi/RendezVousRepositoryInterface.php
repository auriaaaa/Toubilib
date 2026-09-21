<?php
namespace toubilib\application\ports\spi;

use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    public function save(RendezVous $rendezVous): void;

    public function findById(string $id): RendezVous;
} 