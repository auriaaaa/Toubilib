<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\dto\PraticienDTO;

class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function getPraticien(string $id): PraticienDTO
    {
        try {
            $praticien = $this->praticienRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun praticien trouvé avec l'ID : $id");
        }

        return PraticienDTO::fromEntity($praticien);
    }

    public function getAllPraticiens(): array
    {
        $praticiens = $this->praticienRepository->findAll();
        return array_map([PraticienDTO::class, 'fromEntity'], $praticiens);
    }
}


