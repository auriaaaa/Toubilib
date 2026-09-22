<?php

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;

use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\domain\entities\RendezVous;

use tests\pest\fakes\InMemoryRendezVousRepository;

const ACTION_RDV_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';
const UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';


function makeActionService(array $rendezVous = []): ServiceRendezVous
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
        makeActionService($rendezVous)
    );
}


function makeChangeRequest(): ServerRequestInterface
{
    return (new RequestFactory())
        ->createRequest(
            'POST',
            '/rdv/' . ACTION_RDV_UUID . '/annuler'
        );
}


function makeActionRendezVous(int $statut = 0): RendezVous
{
    return new RendezVous(
        ACTION_RDV_UUID,
        'praticien-uuid-1',
        'patient-uuid-1',
        new DateTime('+1 day'),
        new DateTime('now'),
        $statut,
        'Consultation'
    );
}


it('should return 200 when cancelling an existing appointment', function () {

    $result = makeAnnulerAction([
        makeActionRendezVous()
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => ACTION_RDV_UUID]
    );

    expect($result->getStatusCode())->toBe(200);
});

// ------------------------------------------------------------ Tests

it('should return bad request when cancelling an already cancelled appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeActionRendezVous(1)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => ACTION_RDV_UUID]
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});


it('should return bad request when cancelling an honoured appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeActionRendezVous(2)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => ACTION_RDV_UUID]
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});


it('should return bad request when cancelling an ignored appointment', function () {

    expect(fn () => makeAnnulerAction([
        makeActionRendezVous(3)
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => ACTION_RDV_UUID]
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
        makeActionRendezVous()
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
        makeActionRendezVous()
    ])(
        makeChangeRequest(),
        (new ResponseFactory())->createResponse(),
        ['id' => '']
    ))->toThrow(
        \Slim\Exception\HttpBadRequestException::class
    );
});