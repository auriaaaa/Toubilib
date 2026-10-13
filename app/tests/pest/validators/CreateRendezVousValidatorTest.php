<?php

use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use toubilib\application\dto\CreateRendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\validators\CreateRendezVousValidator;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;

beforeEach(function () {
    $praticiens = new InMemoryPraticienRepository();
    $praticiens->save(new Praticien(
        'praticien-uuid-1', 'Dupont', 'Jean', 'Nancy', 'j.dupont@example.fr', '0600000000',
        1, 'structure-1', '12345678901', false, true, 'Dr'
    ));

    $patients = new InMemoryPatientRepository();
    $patients->save(new Patient(
        'patient-uuid-1', 'Martin', 'Claire', '1990-05-12', '1 rue de la Paix',
        '54000', 'Nancy', 'c.martin@example.fr', '0611111111'
    ));

    $this->validator = new CreateRendezVousValidator($praticiens, $patients);

    $this->dto = fn (array $champs = []) => CreateRendezVousDTO::fromArray(array_merge([
        'praticien_id' => 'praticien-uuid-1',
        'patient_id' => 'patient-uuid-1',
        'date_heure_debut' => 'next monday 10:00',
        'motif_visite' => 'CI',
    ], $champs));
});

it('accepts a request when the praticien and the patient exist', function () {

    expect($this->validator->valider(($this->dto)()))->toBeNull();
});

it('refuses an unknown praticien', function () {

    $erreurs = [];

    try {
        $this->validator->valider(($this->dto)(['praticien_id' => 'praticien-inconnu']));
    } catch (ValidationException $e) {
        $erreurs = $e->getErrors();
    }

    expect(array_keys($erreurs))->toBe(['praticien_id']);
});

it('refuses an unknown patient', function () {

    $erreurs = [];

    try {
        $this->validator->valider(($this->dto)(['patient_id' => 'patient-inconnu']));
    } catch (ValidationException $e) {
        $erreurs = $e->getErrors();
    }

    expect(array_keys($erreurs))->toBe(['patient_id']);
});

it('reports both errors when the praticien and the patient are unknown', function () {

    $erreurs = [];

    try {
        $this->validator->valider(($this->dto)([
            'praticien_id' => 'praticien-inconnu',
            'patient_id' => 'patient-inconnu',
        ]));
    } catch (ValidationException $e) {
        $erreurs = $e->getErrors();
    }

    expect(array_keys($erreurs))->toBe(['praticien_id', 'patient_id']);
});
