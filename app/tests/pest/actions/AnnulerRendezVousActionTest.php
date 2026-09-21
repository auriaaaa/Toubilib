<?php

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;

use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\domain\entities\RendezVous;

use tests\pest\fakes\InMemoryRendezVousRepository;

// ------------------------------------------------------------ Setup 

const RDV_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';
const UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';


function makeService(array $rendezVous = []): ServiceRendezVous
{
    $repository = new InMemoryRendezVousRepository();

    foreach ($rendezVous as $rdv) {
        $repository->save($rdv);
    }

    return new ServiceRendezVous($repository);
}


function makeAnnulerAction(array $rendezVous = []): AnnulerRendezVousAction
{
    return new AnnulerRendezVousAction(
        makeService($rendezVous)
    );
}


function makeChangeRequest(): ServerRequestInterface
{
    return (new RequestFactory())
        ->createRequest(
            'POST',
            '/rdv/' . RDV_UUID . '/annuler'
        );
}


function makeRendezVous(int $statut = 0): RendezVous
{
    return new RendezVous(
        RDV_UUID,
        'praticien-uuid-1',
        'patient-uuid-1',
        new DateTime('+1 day'),
        new DateTime('now'),
        $statut,
        'Consultation'
    );
}


// ------------------------------------------------------------ Tests :  AnnulerRendezVousAction 

it('should change the appointment status from created to cancelled', function () {

    $rdv = makeRendezVous(0);

    $action = makeAnnulerAction([$rdv]);

    $action(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => RDV_UUID]
    );

    expect($rdv->getStatut())->toBe(1);
});

it('should return 200 when cancelling an existing appointment', function () {

    $result = makeAnnulerAction([
        makeRendezVous()
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => RDV_UUID]
    );

    expect($result->getStatusCode())->toBe(200);
});

it('should not cancel an already cancelled appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeRendezVous(1)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => RDV_UUID]
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});

it('should not cancel an honoured appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeRendezVous(2)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => RDV_UUID]
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});

it('should not cancel an ignored appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeRendezVous(3)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => RDV_UUID]
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});



it('should throw HttpNotFoundException when cancelling a non-existent appointment', function () {

    expect(fn () => makeAnnulerAction([])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => UNKNOWN_UUID]
    ))->toThrow(
        \Slim\Exception\HttpNotFoundException::class
    );
});


it('should throw HttpBadRequestException when appointment id is missing', function () {

    expect(fn () => makeAnnulerAction([
        makeRendezVous()
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        []
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});


it('should throw HttpBadRequestException when appointment id is empty', function () {

    expect(fn () => makeAnnulerAction([
        makeRendezVous()
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => '']
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});