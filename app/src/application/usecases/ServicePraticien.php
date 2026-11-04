<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use DateTime;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\dto\RendezVousAgendaDTO;

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
        $resultat = [];

        foreach ($this->praticienRepository->findAll() as $praticien) {
            $resultat[] = PraticienDTO::fromEntity($praticien);
        }

        return $resultat;
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

        $resultat = [];

        foreach ($praticien->getRendezVousPourPeriode($debut, $fin) as $rdv) {
            $resultat[] = RendezVousAgendaDTO::fromEntity($rdv);
        }

        return $resultat;
    }

    public function rechercherPraticiens(?string $specialite, ?string $ville): array
    {
        $specialiteId = null;

        if ($specialite !== null && $specialite !== '') {
            if (!ctype_digit($specialite)) {
                throw new \InvalidArgumentException("La spécialité doit être un identifiant numérique.");
            }
            $specialiteId = (int) $specialite;
        }

        $praticiens = $this->praticienRepository->rechercher($specialiteId, $ville);

        $resultat = [];

        foreach ($praticiens as $praticien) {
            $resultat[] = PraticienDTO::fromEntity($praticien);
        }

        return $resultat;
    }
}


