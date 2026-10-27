<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTime;
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
    
    public function getRendezVousPourPeriode(
        string $praticienId,
        DateTime $debut,
        DateTime $fin
    ): array {
        if ($debut >= $fin) {
            throw new \InvalidArgumentException("La date de début doit précéder la date de fin.");
        }

        $praticien = $this->praticienRepository
            ->findByIdAvecRendezVous($praticienId, $debut, $fin);

        return $praticien->getRendezVousPourPeriode($debut, $fin);
    }

    public function rechercherPraticiens(
        ?string $specialite,
        ?string $ville
    ): array {
        return $this->praticienRepository->rechercher((int)$specialite, $ville);
    }
}


