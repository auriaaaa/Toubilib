<?php

use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\CreateRendezVousDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\validators\CreateRendezVousValidator;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;

beforeEach(function () {

    $this->rendezVousRepository = new class extends InMemoryRendezVousRepository {
        public int $saves = 0;

        public function save(RendezVous $rendezVous): void
        {
            $this->saves++;
            parent::save($rendezVous);
        }
    };

    $this->praticienRepository = new class extends InMemoryPraticienRepository {
        public array $periodes = [];

        public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien
        {
            $this->periodes[] = [$debut, $fin];
            return parent::findByIdAvecRendezVous($id, $debut, $fin);
        }
    };

    $this->patientRepository = new InMemoryPatientRepository();

    $this->praticien = new Praticien(
        'praticien-uuid-1', 'Dupont', 'Jean', 'Nancy', 'j.dupont@example.fr', '0600000000',
        1, 'structure-1', '12345678901', false, true, 'Dr'
    );
    $this->praticienRepository->save($this->praticien);

    $this->patientRepository->save(new Patient(
        'patient-uuid-1', 'Martin', 'Claire', '1990-05-12', '1 rue de la Paix',
        '54000', 'Nancy', 'c.martin@example.fr', '0611111111'
    ));

    $this->service = new ServiceRendezVous(
        $this->rendezVousRepository,
        $this->praticienRepository,
        $this->patientRepository,
        new CreateRendezVousValidator($this->praticienRepository, $this->patientRepository)
    );

    $this->rdv = fn (string $debut, int $duree = 30, int $statut = 0) => new RendezVous(
        'rdv-uuid-1',
        'praticien-uuid-1',
        'patient-uuid-1',
        new DateTime($debut),
        (new DateTime($debut))->modify("+{$duree} minutes"),
        new DateTime('now'),
        $statut,
        $duree,
        'CI'
    );

    $this->dto = fn (array $champs = []) => CreateRendezVousDTO::fromArray(array_merge([
        'praticien_id' => 'praticien-uuid-1',
        'patient_id' => 'patient-uuid-1',
        'date_heure_debut' => 'next monday 10:00',
        'motif_visite' => 'CI',
    ], $champs));
});

// ------------------------------------------------------------ getRendezVous()

it('should return a rendez-vous DTO by id', function () {

    $this->rendezVousRepository->save(($this->rdv)('+2 days', 20));

    expect($this->service->getRendezVous('rdv-uuid-1'))->toBeInstanceOf(RendezVousDTO::class);
});

it('should throw an exception when rendez-vous does not exist', function () {

    $id = '00000000-0000-0000-0000-000000000000';

    expect(fn () => $this->service->getRendezVous($id))
        ->toThrow(Exception::class, "Aucun rendez-vous trouvé avec l'ID : $id");
});

// ------------------------------------------------------------ annuler()

it('should cancel a rendez-vous', function () {

    $this->rendezVousRepository->save(($this->rdv)('+2 days', 20));

    $result = $this->service->annuler('rdv-uuid-1');

    expect($result)->toBeInstanceOf(RendezVousDTO::class)
        ->and($result->statut)->toBe(1);
});

it('should throw an exception when cancelling an unknown rendez-vous', function () {

    $id = '00000000-0000-0000-0000-000000000000';

    expect(fn () => $this->service->annuler($id))
        ->toThrow(Exception::class, "Aucun Rendez-vous trouvé avec l'ID : $id");
});

// ------------------------------------------------------------ creer()

it('should create a rendez-vous and return its data', function () {

    $result = $this->service->creer(($this->dto)());

    expect($result)->toBeInstanceOf(RendezVousDTO::class)
        ->and($result->praticien_id)->toBe('praticien-uuid-1')
        ->and($result->patient_id)->toBe('patient-uuid-1')
        ->and($result->date_heure_debut->format('H:i'))->toBe('10:00')
        ->and($result->duree)->toBe(30)
        ->and($result->statut)->toBe(0);
});

it('should persist the created rendez-vous', function () {

    $result = $this->service->creer(($this->dto)());

    $enBase = $this->rendezVousRepository->findById($result->id);

    expect($this->rendezVousRepository->saves)->toBe(1)
        ->and($enBase->getPraticienId())->toBe('praticien-uuid-1')
        ->and($enBase->getPatientId())->toBe('patient-uuid-1')
        ->and($enBase->getMotifVisite())->toBe('CI')
        ->and($enBase->getDateHeureFin()->format('H:i'))->toBe('10:30')
        ->and($enBase->getStatut())->toBe(0);
});

it('should derive the duration from the visit reason', function () {

    $result = $this->service->creer(($this->dto)(['motif_visite' => 'CS']));

    expect($result->duree)->toBe(15);
});

it('should load the praticien with the agenda of the requested day', function () {

    $this->service->creer(($this->dto)(['date_heure_debut' => 'next monday 10:00']));

    [$debut, $fin] = $this->praticienRepository->periodes[0];
    $lundi = new DateTime('next monday');

    expect($this->praticienRepository->periodes)->toHaveCount(1)
        ->and($debut->format('Y-m-d H:i'))->toBe($lundi->format('Y-m-d') . ' 00:00')
        ->and($fin->format('Y-m-d H:i'))->toBe((clone $lundi)->modify('+1 day')->format('Y-m-d') . ' 00:00');
});

it('should refuse to create a rendez-vous for an unknown praticien', function () {

    expect(fn () => $this->service->creer(($this->dto)(['praticien_id' => 'praticien-inconnu'])))
        ->toThrow(ValidationException::class);

    expect($this->rendezVousRepository->saves)->toBe(0);
});

it('should refuse to create a rendez-vous for an unknown patient', function () {

    expect(fn () => $this->service->creer(($this->dto)(['patient_id' => 'patient-inconnu'])))
        ->toThrow(ValidationException::class);

    expect($this->rendezVousRepository->saves)->toBe(0);
});

it('should refuse to create a rendez-vous with an unknown visit reason', function () {

    expect(fn () => $this->service->creer(($this->dto)(['motif_visite' => 'XX'])))
        ->toThrow(ValidationException::class);

    expect($this->rendezVousRepository->saves)->toBe(0);
});

it('should refuse to create a rendez-vous on a weekend', function () {

    expect(fn () => $this->service->creer(($this->dto)(['date_heure_debut' => 'next saturday 10:00'])))
        ->toThrow(ValidationException::class);

    expect($this->rendezVousRepository->saves)->toBe(0);
});

it('should refuse to create a rendez-vous overlapping one of the praticien', function () {

    // RDV existant : lundi de 10:00 à 10:30
    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30));

    $erreurs = [];

    try {
        $this->service->creer(($this->dto)(['date_heure_debut' => 'next monday 10:15', 'motif_visite' => 'CS']));
    } catch (ValidationException $e) {
        $erreurs = $e->getErrors();
    }

    expect(array_keys($erreurs))->toBe(['date_heure_debut'])
        ->and($this->rendezVousRepository->saves)->toBe(0);
});

it('should create a rendez-vous right after one of the praticien', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30));

    $result = $this->service->creer(($this->dto)(['date_heure_debut' => 'next monday 10:30']));

    expect($result->date_heure_debut->format('H:i'))->toBe('10:30')
        ->and($this->rendezVousRepository->saves)->toBe(1);
});

it('should create a rendez-vous on the slot of a cancelled one', function () {

    // RDV annulé (statut 1) : son créneau est libre
    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30, 1));

    $result = $this->service->creer(($this->dto)());

    expect($result->statut)->toBe(0)
        ->and($this->rendezVousRepository->saves)->toBe(1);
});
