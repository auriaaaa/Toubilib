<?php

declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\CreateRendezVousDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\CreateRendezVousValidator;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\PraticienIndisponibleException;

class ServiceRendezVous implements ServiceRendezVousInterface
{
    private RendezVousRepositoryInterface $rendezVousRepository;
    private PraticienRepositoryInterface $praticienRepository;
    private PatientRepositoryInterface $patientRepository;
    private CreateRendezVousValidator $validator;

    public function __construct(
        RendezVousRepositoryInterface $rendezVousRepository,
        PraticienRepositoryInterface $praticienRepository,
        PatientRepositoryInterface $patientRepository,
        CreateRendezVousValidator $validator
    ) {
        $this->rendezVousRepository = $rendezVousRepository;
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository = $patientRepository;
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

    public function creer(CreateRendezVousDTO $dto): RendezVousDTO
    {
        $this->validator->valider($dto);

        $patient = $this->patientRepository->findById($dto->patient_id);

        $debutJour = (clone $dto->date_heure_debut)->setTime(0, 0);
        $finJour = (clone $debutJour)->modify('+1 day');
        $praticien = $this->praticienRepository->findByIdAvecRendezVous($dto->praticien_id, $debutJour, $finJour);

        try {
            $rendezVous = RendezVous::creer($praticien, $patient, $dto->date_heure_debut, $dto->motif_visite);
        } catch (\InvalidArgumentException $e) {
            throw new ValidationException(['rendez_vous' => $e->getMessage()]);
        } catch (PraticienIndisponibleException $e) {
            throw new ValidationException(['date_heure_debut' => $e->getMessage()]);
        }

        $this->rendezVousRepository->save($rendezVous);

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


    public function statutModifiable(string $id): bool
    {
        try {
            $rendezVous = $this->rendezVousRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        return $rendezVous->statutModifiable();
    }

    public function honorer(string $id): RendezVousDTO
    {
        try {
            $rendezVous = $this->rendezVousRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        $rendezVous->honorer();

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }

    public function ignorer(string $id): RendezVousDTO
    {
        try {
            $rendezVous = $this->rendezVousRepository->findById($id);
        } catch (\Exception $e) {
            throw new \Exception("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        $rendezVous->ignorer();

        $this->rendezVousRepository->save($rendezVous);

        return RendezVousDTO::fromEntity($rendezVous);
    }
}