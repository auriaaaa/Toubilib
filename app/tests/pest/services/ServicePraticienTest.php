<?php

use tests\pest\fakes\InMemoryPraticienRepository;
use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\dto\RendezVousAgendaDTO;
use toubilib\application\usecases\ServicePraticien;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\PraticienNotFoundException;

const PRATICIEN_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';


function makePraticien(
    string $id = PRATICIEN_UUID,
    string $ville = 'Nancy',
    int $specialite = 1,
    string $nom = 'Auriane',
    string $prenom = 'Guyot'
): Praticien {
    return new Praticien(
        $id,
        $nom,
        $prenom,
        $ville,
        'auriane@example.com',
        '0123456789',
        $specialite,
        true,
        true,
        'Dr.'
    );
}


function makeServicePraticien(array $praticiens = []): ServicePraticien {

    $repository = new InMemoryPraticienRepository();

    foreach ($praticiens as $praticien) {
        $repository->save($praticien);
    }

    return new ServicePraticien($repository);
}

// ------------------------------------------------------------ getPraticien()

it('should return a practitioner detail DTO by id', function () {

    $service = makeServicePraticien([
        makePraticien()
    ]);

    $result = $service->getPraticien(PRATICIEN_UUID);

    expect($result)->toBeInstanceOf(PraticienDetailDTO::class)
        ->and($result->id)->toBe(PRATICIEN_UUID)
        ->and($result->nom)->toBe('Auriane');
});

it('should throw an exception when practitioner does not exist', function () {

    $service = makeServicePraticien();

    expect(
        fn () => $service->getPraticien(
            '00000000-0000-0000-0000-000000000000'
        )
    )->toThrow(
        PraticienNotFoundException::class,
        'Aucun praticien trouvé avec l\'ID : 00000000-0000-0000-0000-000000000000'
    );
});

// ------------------------------------------------------------ getAllPraticiens()

it('should return all practitioners as DTOs', function () {

    $service = makeServicePraticien([
        makePraticien(),
        makePraticien('6205e114-4299-338e-a389-bfa83f2109c5', 'Nancy', 2, 'Martin', 'DUPONT'),
    ]);

    $result = $service->getAllPraticiens();

    expect($result)->toHaveCount(2);

    expect($result[0])->toBeInstanceOf(PraticienDTO::class);

    expect($result[1])->toBeInstanceOf(PraticienDTO::class);
});

it('should return an empty array when there are no practitioners', function () {

    $service = makeServicePraticien();

    expect($service->getAllPraticiens())
        ->toBe([]);
});

// ------------------------------------------------------------ getRendezVousPourPeriode()

it('should return occupied appointments for a practitioner during a period', function () {
    $praticien = makePraticien();

    $rdv1 = new RendezVous(
        'rdv-1',
        PRATICIEN_UUID,
        'patient-1',
        new DateTime('next monday 10:00'),
        new DateTime('next monday 10:30'),
        new DateTime('now'),
        0,
        30,
        'CI'
    );

    $rdv2 = new RendezVous(
        'rdv-2',
        PRATICIEN_UUID,
        'patient-2',
        new DateTime('next monday 11:00'),
        new DateTime('next monday 11:30'),
        new DateTime('now'),
        0,
        30,
        'CS'
    );

    $praticien->ajouterRendezVous($rdv1);
    $praticien->ajouterRendezVous($rdv2);

    $repository = new InMemoryPraticienRepository();
    $repository->save($praticien);

    $service = new ServicePraticien($repository);

    $result = $service->getRendezVousPourPeriode(
        PRATICIEN_UUID,
        new DateTime('next monday 09:00'),
        new DateTime('next monday 12:00')
    );

    // Le service retourne des DTO
    expect($result)->toHaveCount(2);
    expect($result[0])->toBeInstanceOf(RendezVousAgendaDTO::class);
    expect($result[1])->toBeInstanceOf(RendezVousAgendaDTO::class);
    expect($result[0]->id)->toBe('rdv-1');
    expect($result[1]->id)->toBe('rdv-2');
});

it('should not return cancelled appointments', function () {
    $praticien = makePraticien();

    $praticien->ajouterRendezVous(new RendezVous(
        'rdv-annule',
        PRATICIEN_UUID,
        'patient-1',
        new DateTime('next monday 10:00'),
        new DateTime('next monday 10:30'),
        new DateTime('now'),
        1,
        30,
        'CI'
    ));

    $repository = new InMemoryPraticienRepository();
    $repository->save($praticien);

    $service = new ServicePraticien($repository);

    $result = $service->getRendezVousPourPeriode(
        PRATICIEN_UUID,
        new DateTime('next monday 09:00'),
        new DateTime('next monday 12:00')
    );

    expect($result)->toBe([]);
});

it('should return an empty array when there are no occupied appointments', function () {
    $praticien = makePraticien();

    $repository = new InMemoryPraticienRepository();
    $repository->save($praticien);

    $service = new ServicePraticien($repository);

    $result = $service->getRendezVousPourPeriode(
        PRATICIEN_UUID,
        new DateTime('next monday 09:00'),
        new DateTime('next monday 12:00')
    );

    expect($result)->toBe([]);
});

it('should throw an exception when the practitioner of the period does not exist', function () {
    $service = makeServicePraticien();

    expect(fn () => $service->getRendezVousPourPeriode(
        '00000000-0000-0000-0000-000000000000',
        new DateTime('next monday 09:00'),
        new DateTime('next monday 12:00')
    ))->toThrow(PraticienNotFoundException::class);
});

it('should throw an exception when the start date is after the end date', function () {
    $service = makeServicePraticien([
        makePraticien()
    ]);

    expect(fn () => $service->getRendezVousPourPeriode(
        PRATICIEN_UUID,
        new DateTime('next monday 12:00'),
        new DateTime('next monday 09:00')
    ))->toThrow(
        InvalidArgumentException::class,
        'La date de début doit précéder la date de fin.'
    );
});

it('should throw an exception when the start date equals the end date', function () {
    $service = makeServicePraticien([
        makePraticien()
    ]);

    $date = new DateTime('next monday 10:00');

    expect(fn () => $service->getRendezVousPourPeriode(
        PRATICIEN_UUID,
        $date,
        clone $date
    ))->toThrow(
        InvalidArgumentException::class,
        'La date de début doit précéder la date de fin.'
    );
});

// ------------------------------------------------------------ rechercherPraticiens()

it('should find practitioners by specialty', function () {
    $nancy = makePraticien();
    $paris = makePraticien('praticien-2', 'Paris', 2, 'Martin', 'Paul');

    $service = makeServicePraticien([$nancy, $paris]);

    $result = $service->rechercherPraticiens('2', null);

    expect($result)
        ->toHaveCount(1)
        ->and($result[0]->id)->toBe('praticien-2');
});


it('should find practitioners by city', function () {
    $nancy = makePraticien();
    $paris = makePraticien('praticien-2', 'Paris', 2, 'Martin', 'Paul');

    $service = makeServicePraticien([$nancy, $paris]);

    $result = $service->rechercherPraticiens(null, 'Paris');

    expect($result)
        ->toHaveCount(1)
        ->and($result[0]->ville)->toBe('Paris');
});


it('should find practitioners by specialty and city', function () {
    $praticiens = [
        makePraticien(), // Nancy, spécialité 1
        makePraticien('praticien-2', 'Paris', 2, 'Martin', 'Paul'),
        makePraticien('praticien-3', 'Paris', 1, 'Durand', 'Julie'),
    ];

    $service = makeServicePraticien($praticiens);

    $result = $service->rechercherPraticiens('1', 'Paris');

    expect($result)
        ->toHaveCount(1)
        ->and($result[0]->id)->toBe('praticien-3');
});


it('should return an empty array when no practitioner matches the criteria', function () {
    $service = makeServicePraticien([makePraticien()]);

    $result = $service->rechercherPraticiens('99', 'Paris');

    expect($result)->toBe([]);
});


it('should return all practitioners when no criteria are provided', function () {
    $service = makeServicePraticien([
        makePraticien(),
        makePraticien('praticien-2', 'Paris', 2, 'Martin', 'Paul'),
    ]);

    expect($service->rechercherPraticiens(null, null))
        ->toHaveCount(2);
});

it('should ignore an empty specialty', function () {
    $service = makeServicePraticien([
        makePraticien(),
        makePraticien('praticien-2', 'Paris', 2, 'Martin', 'Paul'),
    ]);

    expect($service->rechercherPraticiens('', null))
        ->toHaveCount(2);
});

it('should refuse a specialty that is not a positive integer', function (string $specialite) {
    $service = makeServicePraticien([makePraticien()]);

    expect(fn () => $service->rechercherPraticiens($specialite, null))
        ->toThrow(InvalidArgumentException::class);
})->with([['abc'], ['-1'], ['1.5'], ['1 OR 1=1']]);
