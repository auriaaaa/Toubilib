<?php

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\dto\RendezVousDTO;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousNotFoundException;
use tests\pest\fakes\InMemoryRendezVousRepository;

const RENDEZ_VOUS_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';

function makeRendezVous(): RendezVous
{
    return new RendezVous(
        RENDEZ_VOUS_UUID,
        'praticien-uuid-1',
        'patient-uuid-1',
        new DateTime('+1 day'),
        new DateTime('now'),
        0,
        'Consultation'
    );
}


function makeServiceRendezVous(array $rendezVous = []): ServiceRendezVous
{
    $repository = new InMemoryRendezVousRepository();

    foreach ($rendezVous as $rdv) {
        $repository->save($rdv);
    }

    return new ServiceRendezVous($repository);
}


// ------------------------------------------------------------ Tests

it('should return a rendez-vous DTO by id', function () {

    $rendezVous = makeRendezVous();

    $service = makeServiceRendezVous([
        $rendezVous
    ]);

    $result = $service->getRendezVous(RENDEZ_VOUS_UUID);

    expect($result)
        ->toBeInstanceOf(RendezVousDTO::class);
});


it('should throw an exception when rendez-vous does not exist', function () {

    $id = '00000000-0000-0000-0000-000000000000';

    $service = makeServiceRendezVous();

    expect(
        fn () => $service->getRendezVous($id)
    )->toThrow(
        \Exception::class,
        "Aucun rendez-vous trouvé avec l'ID : $id"
    );
});

it('should cancel a rendez-vous', function () {

    $rendezVous = makeRendezVous();

    $service = makeServiceRendezVous([
        $rendezVous
    ]);

    $result = $service->annuler(RENDEZ_VOUS_UUID);

    expect($result)
        ->toBeInstanceOf(RendezVousDTO::class);
});


it('should throw RendezVousNotFoundException when cancelling an unknown rendez-vous', function () {

    $id = '00000000-0000-0000-0000-000000000000';

    $service = makeServiceRendezVous();

    expect(fn () => $service->annuler($id))
        ->toThrow(
        \Exception::class,
        'Aucun Rendez-vous trouvé avec l\'ID : 00000000-0000-0000-0000-000000000000'
    );
});