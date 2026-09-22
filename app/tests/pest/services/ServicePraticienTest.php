<?php

use toubilib\application\usecases\ServicePraticien;
use toubilib\domain\entities\Praticien;
use tests\pest\fakes\InMemoryPraticienRepository;

const PRATICIEN_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';


function makePraticien(): Praticien
{
    return new Praticien(
        PRATICIEN_UUID,
        'Auriane',
        'Guyot',
        'Nancy',
        'auriane@example.com',
        '0123456789',
        1,
        'structure-uuid',
        'rpps-uuid',
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

// ------------------------------------------------------------ Tests

it('should return a practitioner DTO by id', function () {

    $praticien = makePraticien();

    $service = makeServicePraticien([
        $praticien
    ]);

    $result = $service->getPraticien(PRATICIEN_UUID);

    expect($result)->toBeInstanceOf(
        \toubilib\application\dto\PraticienDTO::class
    );
});

it('should throw an exception when practitioner does not exist', function () {

    $service = makeServicePraticien();

    expect(
        fn () => $service->getPraticien(
            '00000000-0000-0000-0000-000000000000'
        )
    )->toThrow(
        \Exception::class,
        'Aucun praticien trouvé avec l\'ID : 00000000-0000-0000-0000-000000000000'
    );
});

it('should return all practitioners as DTOs', function () {

    $praticien1 = makePraticien();

    $praticien2 = new Praticien(
        '6205e114-4299-338e-a389-bfa83f2109c5',
        'Martin',
        'DUPONT',
        'Nancy',
        'martin@example.com',
        '0123456789',
        2,
        'structure-uuid',
        'rpps-uuid',
        true,
        true,
        'M.'        
    );

    $service = makeServicePraticien([
        $praticien1,
        $praticien2
    ]);

    $result = $service->getAllPraticiens();

    expect($result)
        ->toHaveCount(2);

    expect($result[0])
        ->toBeInstanceOf(
            \toubilib\application\dto\PraticienDTO::class
        );

    expect($result[1])
        ->toBeInstanceOf(
            \toubilib\application\dto\PraticienDTO::class
        );
});

it('should return an empty array when there are no practitioners', function () {

    $service = makeServicePraticien();

    expect($service->getAllPraticiens())
        ->toBe([]);
});