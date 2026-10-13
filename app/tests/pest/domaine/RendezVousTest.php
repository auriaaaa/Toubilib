<?php

use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousPasPasseException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

const RDV_UUID = '5105e114-4299-338e-a389-bfa83f2109c4';

function makeRendezVous(int $statut = 0, ?DateTime $dateDebut = null, int $duree = 20): RendezVous {
    $debut = $dateDebut ?? new DateTime('+1 day');

    return new RendezVous(
        RDV_UUID,
        'praticien-uuid-1',
        'patient-uuid-1',
        $debut,
        (clone $debut)->modify("+{$duree} minutes"),
        new DateTime('now'),
        $statut,
        $duree,
        'Consultation'
    );
}

// Création d'un praticien et d'un patient
beforeEach(function () {
    $this->praticien = new Praticien(
        'praticien-uuid-1', 'Dupont', 'Jean', 'Nancy', 'j.dupont@example.fr', '0600000000',
        1, 'structure-1', '12345678901', false, true, 'Dr'
    );

    $this->patient = new Patient(
        'patient-uuid-1', 'Martin', 'Claire', '1990-05-12', '1 rue de la Paix',
        '54000', 'Nancy', 'c.martin@example.fr', '0611111111'
    );
});

// ------------------------------------------------------------ annuler()

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

// ------------------------------------------------------------ creer()
it('creates a valid appointment', function () {

    $rdv = RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next monday 10:00'),
        'CI'
    );

    expect($rdv)->toBeInstanceOf(RendezVous::class)
        ->and($rdv->getPraticienId())->toBe('praticien-uuid-1')
        ->and($rdv->getPatientId())->toBe('patient-uuid-1')
        ->and($rdv->getStatut())->toBe(0)
        ->and($rdv->getDuree())->toBe(30)
        ->and($rdv->getMotifVisite())->toBe('CI')
        ->and($rdv->getDateHeureDebut()->format('H:i'))->toBe('10:00')
        ->and($rdv->getDateHeureFin()->format('H:i'))->toBe('10:30');
});

it('derives the end time from the visit reason', function () {

    $attendus = [
        'CI' => ['duree' => 30, 'fin' => '10:30'],
        'C0' => ['duree' => 20, 'fin' => '10:20'],
        'CS' => ['duree' => 15, 'fin' => '10:15'],
    ];

    foreach ($attendus as $motif => $attendu) {
        $rdv = RendezVous::creer(
            $this->praticien,
            $this->patient,
            new DateTime('next monday 10:00'),
            $motif
        );

        expect($rdv->getDuree())->toBe($attendu['duree'])
            ->and($rdv->getDateHeureFin()->format('H:i'))->toBe($attendu['fin']);
    }
});

it('refuses an unknown visit reason', function () {

    expect(fn () => RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next monday 10:00'),
        'XX'
    ))->toThrow(InvalidArgumentException::class);
});

it('refuses a date in the past', function () {

    expect(fn () => RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('last monday 10:00'),
        'CI'
    ))->toThrow(InvalidArgumentException::class);
});

it('refuses a weekend date', function () {

    expect(fn () => RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next saturday 10:00'),
        'CI'
    ))->toThrow(InvalidArgumentException::class);

    expect(fn () => RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next sunday 10:00'),
        'CI'
    ))->toThrow(InvalidArgumentException::class);
});

it('refuses a time outside opening hours', function () {

    // trop tôt, finit après 18h (17:45 + 30 min), trop tard
    foreach (['07:00', '17:45', '19:00'] as $heure) {
        expect(fn () => RendezVous::creer(
            $this->praticien,
            $this->patient,
            new DateTime("next monday $heure"),
            'CI'
        ))->toThrow(InvalidArgumentException::class);
    }
});

it('refuses an appointment overlapping one of the praticien', function () {

    // RDV existant : lundi de 10:00 à 10:30
    $this->praticien->ajouterRendezVous(
        makeRendezVous(0, new DateTime('next monday 10:00'), 30)
    );

    expect(fn () => RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next monday 10:15'),
        'CS'
    ))->toThrow(PraticienIndisponibleException::class);
});

it('accepts an appointment right after one of the praticien', function () {

    // RDV existant : lundi de 10:00 à 10:30
    $this->praticien->ajouterRendezVous(
        makeRendezVous(0, new DateTime('next monday 10:00'), 30)
    );

    $rdv = RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next monday 10:30'),
        'CS'
    );

    expect($rdv->getDateHeureDebut()->format('H:i'))->toBe('10:30')
        ->and($rdv->getDateHeureFin()->format('H:i'))->toBe('10:45');
});

it('accepts an appointment on the slot of a cancelled one', function () {

    // RDV annulé (statut 1) : son créneau est libre
    $this->praticien->ajouterRendezVous(
        makeRendezVous(1, new DateTime('next monday 10:00'), 30)
    );

    $rdv = RendezVous::creer(
        $this->praticien,
        $this->patient,
        new DateTime('next monday 10:00'),
        'CI'
    );

    expect($rdv->getStatut())->toBe(0);
});

// ------------------------------------------------------------ chevauche()
it('detects overlapping appointments', function () {

    $a = makeRendezVous(0, new DateTime('next monday 10:00'), 30);

    $cas = [
        '10:00' => true,   // même créneau
        '10:15' => true,   // commence pendant a
        '09:45' => true,   // finit pendant a
        '09:00' => false,  // avant, sans contact
        '09:30' => false,  // se termine exactement quand a commence
        '10:30' => false,  // commence exactement quand a se termine
        '11:00' => false,  // après
    ];

    foreach ($cas as $heure => $attendu) {
        $b = makeRendezVous(0, new DateTime("next monday $heure"), 30);

        expect($a->chevauche($b))->toBe($attendu)
            ->and($b->chevauche($a))->toBe($attendu);
    }
});

it('detects an appointment fully containing another one', function () {

    $long = makeRendezVous(0, new DateTime('next monday 10:00'), 60);
    $court = makeRendezVous(0, new DateTime('next monday 10:15'), 15);

    expect($long->chevauche($court))->toBeTrue()
        ->and($court->chevauche($long))->toBeTrue();
});

// ------------------------------------------------------------ honorer()
it('honours a past appointment with status 0', function () {
    $rdv = makeRendezVous(0, new DateTime('-1 day'));

    $rdv->honorer();

    expect($rdv->getStatut())->toBe(2);
});

it('does not honour a future appointment', function () {
    $rdv = makeRendezVous(0, new DateTime('+1 day'));

    expect(fn () => $rdv->honorer())
        ->toThrow(RendezVousPasPasseException::class);

    expect($rdv->getStatut())->toBe(0);
});

it('does not honour an already cancelled appointment', function () {
    $rdv = makeRendezVous(1, new DateTime('-1 day'));

    expect(fn () => $rdv->honorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});

it('does not honour an already honoured appointment', function () {
    $rdv = makeRendezVous(2, new DateTime('-1 day'));

    expect(fn () => $rdv->honorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});

it('does not honour an already ignored appointment', function () {
    $rdv = makeRendezVous(3, new DateTime('-1 day'));

    expect(fn () => $rdv->honorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});


// ------------------------------------------------------------ ignorer()
it('ignores a past appointment with status 0', function () {
    $rdv = makeRendezVous(0, new DateTime('-1 day'));

    $rdv->ignorer();

    expect($rdv->getStatut())->toBe(3);
});

it('does not ignore a future appointment', function () {
    $rdv = makeRendezVous(0, new DateTime('+1 day'));

    expect(fn () => $rdv->ignorer())
        ->toThrow(RendezVousPasPasseException::class);

    expect($rdv->getStatut())->toBe(0);
});

it('does not ignore an already cancelled appointment', function () {
    $rdv = makeRendezVous(1, new DateTime('-1 day'));

    expect(fn () => $rdv->ignorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});

it('does not ignore an already honoured appointment', function () {
    $rdv = makeRendezVous(2, new DateTime('-1 day'));

    expect(fn () => $rdv->ignorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});

it('does not ignore an already ignored appointment', function () {
    $rdv = makeRendezVous(3, new DateTime('-1 day'));

    expect(fn () => $rdv->ignorer())
        ->toThrow(StatutRendezVousInvalideException::class);
});


// ------------------------------------------------------------ statutModifiable()
it('considers an appointment with status 0 modifiable', function () {
    $rdv = makeRendezVous(0);

    expect($rdv->statutModifiable())->toBeTrue();
});

it('considers an appointment with status 1 not modifiable', function () {
    $rdv = makeRendezVous(1);

    expect($rdv->statutModifiable())->toBeFalse();
});

it('considers an appointment with status 2 not modifiable', function () {
    $rdv = makeRendezVous(2);

    expect($rdv->statutModifiable())->toBeFalse();
});

it('considers an appointment with status 3 not modifiable', function () {
    $rdv = makeRendezVous(3);

    expect($rdv->statutModifiable())->toBeFalse();
});

it('considers an appointment with an unknown status not modifiable', function () {
    $rdv = makeRendezVous(99);

    expect($rdv->statutModifiable())->toBeFalse();
});