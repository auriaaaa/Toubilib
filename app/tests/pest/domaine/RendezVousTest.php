<?php

use toubilib\domain\entities\RendezVous;

const RDV_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';

function makeRendezVous(
    int $statut = 0,
    ?DateTime $dateDebut = null
): RendezVous {
    return new RendezVous(
        RDV_UUID,
        'praticien-uuid-1',
        'patient-uuid-1',
        $dateDebut ?? new DateTime('+1 day'),
        new DateTime('now'),
        $statut,
        'Consultation'
    );
}

// ------------------------------------------------------------ Annulation d'un RDV

it('should cancel a created appointment', function () {

    $rdv = makeRendezVous(0);

    $rdv->annuler();

    expect($rdv->getStatut())->toBe(1);
});

it('should not cancel an already cancelled appointment', function () {

    $rdv = makeRendezVous(1);

    expect(fn () => $rdv->annuler())
        ->toThrow(Exception::class);
});

it('should not cancel an honoured appointment', function () {

    $rdv = makeRendezVous(2);

    expect(fn () => $rdv->annuler())
        ->toThrow(Exception::class);
});

it('should not cancel an ignored appointment', function () {

    $rdv = makeRendezVous(3);

    expect(fn () => $rdv->annuler())
        ->toThrow(Exception::class);
});

it('should not cancel a past appointment', function () {

    $rdv = makeRendezVous(
        0,
        new DateTime('-1 day')
    );

    expect(fn () => $rdv->annuler())
        ->toThrow(Exception::class);
});