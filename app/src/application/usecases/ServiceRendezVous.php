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

    public function getRendezVous(string $id): RendezVousDTO
    {
        try {
            $rendezVous = $this->rendezVousRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function annuler(string $id): RendezVousDTO
    {
        try {
            $rendezVous = $this->rendezVousRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun Rendez-vous trouvé avec l'ID : $id");
        }

        $rendezVous->annuler();

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }
}