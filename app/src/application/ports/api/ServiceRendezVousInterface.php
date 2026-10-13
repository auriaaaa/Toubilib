<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\dto\CreateRendezVousDTO;

interface ServiceRendezVousInterface
{
    public function getRendezVous(string $id): RendezVousDTO;

    public function creer(CreateRendezVousDTO $dto): RendezVousDTO;
    
    public function annuler(string $id): RendezVousDTO;

    public function honorer(string $id): RendezVousDTO;

    public function ignorer(string $id): RendezVousDTO;

    public function statutModifiable(string $id): bool;

}