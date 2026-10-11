<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\validators\RendezVousValidator;

class ServiceRendezVous implements ServiceRendezVousInterface
{
    private RendezVousRepositoryInterface $rendezVousRepository;
    private CreateRendezVousValidator $validator;

    public function __construct(RendezVousRepositoryInterface $rendezVousRepository, CreateRendezVousValidator $validator)
    {
        $this->rendezVousRepository = $rendezVousRepository;
        $this->validator = $validator;
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

    public function creer(CreateRdvDTO $dto): CreateRdvDTO
    {
        $this->validator->valider($dto);
 
        return $dto;
    }
}