<?php
namespace toubilib\application\validators;

use toubilib\application\dto\CreateRendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

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

    public function valider(CreateRendezVousDTO $dto): void {
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
    }
}
