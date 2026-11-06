<?php

use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\usecases\ServicePatient;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\PatientNotFoundException;

beforeEach(function () {

    $this->patients = new InMemoryPatientRepository();
    $this->rendezVous = new InMemoryRendezVousRepository();

    foreach (['patient-1', 'patient-2'] as $id) {
        $this->patients->save(new Patient(
            $id, 'Martin', 'Claire', '1990-05-12', '1 rue de la Paix',
            '54000', 'Nancy', 'c.martin@example.fr', '0611111111'
        ));
    }

    $this->service = new ServicePatient($this->patients, $this->rendezVous);

    $this->rdv = fn (string $id, string $patientId, string $debut) => new RendezVous(
        $id,
        'praticien-1',
        $patientId,
        new DateTime($debut),
        (new DateTime($debut))->modify('+30 minutes'),
        new DateTime('now'),
        0,
        30,
        'CI'
    );
});

// ------------------------------------------------------------ getHistoriqueRendezVous()

it('should throw an exception for an unknown patient', function () {

    expect(fn () => $this->service->getHistoriqueRendezVous('patient-inconnu'))
        ->toThrow(PatientNotFoundException::class);
});

it('should return an empty list for a patient without appointment', function () {

    expect($this->service->getHistoriqueRendezVous('patient-1'))->toBe([]);
});

it('should return only the appointments of the requested patient', function () {

    $this->rendezVous->save(($this->rdv)('rdv-1', 'patient-1', '+1 day'));
    $this->rendezVous->save(($this->rdv)('rdv-2', 'patient-2', '+2 days'));
    $this->rendezVous->save(($this->rdv)('rdv-3', 'patient-1', '+3 days'));

    $historique = $this->service->getHistoriqueRendezVous('patient-1');

    expect($historique)->toHaveCount(2);
    expect($historique[0])->toBeInstanceOf(RendezVousDTO::class);
    expect([$historique[0]->id, $historique[1]->id])->toBe(['rdv-1', 'rdv-3']);
});
