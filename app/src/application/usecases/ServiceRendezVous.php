<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\dto\RendezVousDTO;

class ServiceRendezVous implements ServiceRendezVousInterface
{
    private RendezVousRepositoryInterface $rendezVousRepository;

    public function __construct(RendezVousRepositoryInterface $rendezVousRepository)
    {
        $this->rendezVousRepository = $rendezVousRepository;
    }

    public function annuler(string $id): RendezVousDTO
    {
        $rendezVous = $this->rendezVousRepository->findById($id);

        $rendezVous->annuler();

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }
}