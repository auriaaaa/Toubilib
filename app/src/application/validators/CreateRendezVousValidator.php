<?php
namespace toubilib\application\validators;

use toubilib\domain\entities\RendezVous;
use toubilib\application\dtos\CreateRendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

class CreateRendezVousValidator {

    private PraticienRepositoryInterface $praticienRepository;
    private PatientRepositoryInterface $patientRepository;

    public function __construct(
        PraticienRepositoryInterface $praticienRepository,
        PatientRepositoryInterface $patientRepository
    ) {
        $this->praticienRepository = $praticienRepository;
        $this->patientRepository = $patientRepository;
    }

    public function valider(CreateRendezVousDTO $dto): RendezVous {
        $errors = [];

        if (!$this->praticienRepository->existe($dto->praticien_id)) {
            $errors['praticien_id'] = "Aucun praticien trouvé avec l'ID : {$dto->praticien_id}";
        }

        if (!$this->patientRepository->existe($dto->patient_id)) {
            $errors['patient_id'] = "Aucun patient trouvé avec l'ID : {$dto->patient_id}";
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $patient = $this->patientRepository->findById($dto->patient_id);

        $debutJour = (clone $dto->date_heure_debut)->setTime(0, 0);
        $finJour = (clone $debutJour)->modify('+1 day');
        $praticien = $this->praticienRepository->findByIdAvecRendezVous($dto->praticien_id, $debutJour, $finJour);

        try {
            $rdv =  RendezVous::creer($praticien, $patient, $dto->date_heure_debut, $dto->motif_visite);
        } catch (\InvalidArgumentException $e) {
            throw new ValidationException(['rendez_vous' => $e->getMessage()]);
        } catch (PraticienIndisponibleException $e) {
            throw new ValidationException(['date_heure_debut' => $e->getMessage()]);
        }

        return $rdv;
    }
}
