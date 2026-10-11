<?php

use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\PraticienIndisponibleException;

beforeEach(function () {
    $this->praticien = new Praticien(
        'praticien-uuid-1', 'Dupont', 'Jean', 'Nancy', 'j.dupont@example.fr', '0600000000',
        1, 'structure-1', '12345678901', false, true, 'Dr'
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
});

// ------------------------------------------------------------ verifierMotif()

it('accepts the known visit reasons', function () {

    foreach (['CI', 'C0', 'CS'] as $motif) {
        expect($this->praticien->verifierMotif($motif))->toBeTrue();
    }
});

it('refuses an unknown visit reason', function () {

    expect(fn () => $this->praticien->verifierMotif('CX'))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses an empty visit reason or one with the wrong case', function () {

    foreach (['', 'ci', 'Ci'] as $motif) {
        expect(fn () => $this->praticien->verifierMotif($motif))
            ->toThrow(InvalidArgumentException::class);
    }
});

// ------------------------------------------------------------ duree()

it('calculates the duration from the visit reason', function () {

    expect($this->praticien->duree('CI'))->toBe(30)
        ->and($this->praticien->duree('C0'))->toBe(20)
        ->and($this->praticien->duree('CS'))->toBe(15);
});

it('refuses to calculate the duration of an unknown visit reason', function () {

    expect(fn () => $this->praticien->duree('XX'))
        ->toThrow(InvalidArgumentException::class);
});

// ------------------------------------------------------------ rdvPossible()

it('accepts a slot on every working day inside opening hours', function () {

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $jour) {
        expect($this->praticien->rdvPossible(new DateTime("next $jour 10:00"), 30))->toBeTrue();
    }
});

it('accepts the first possible slot of the day', function () {

    expect($this->praticien->rdvPossible(new DateTime('next monday 08:00'), 30))->toBeTrue();
});

it('accepts a slot ending exactly at closing time', function () {

    expect($this->praticien->rdvPossible(new DateTime('next monday 17:30'), 30))->toBeTrue()
        ->and($this->praticien->rdvPossible(new DateTime('next monday 17:45'), 15))->toBeTrue();
});

it('refuses a slot on saturday', function () {

    expect(fn () => $this->praticien->rdvPossible(new DateTime('next saturday 10:00'), 30))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses a slot on sunday', function () {

    expect(fn () => $this->praticien->rdvPossible(new DateTime('next sunday 10:00'), 30))
        ->toThrow(InvalidArgumentException::class);
});

it('refuses a slot starting before opening time', function () {

    foreach (['07:00', '07:30', '07:59'] as $heure) {
        expect(fn () => $this->praticien->rdvPossible(new DateTime("next monday $heure"), 15))
            ->toThrow(InvalidArgumentException::class);
    }
});

it('refuses a slot ending after closing time', function () {

    expect(fn () => $this->praticien->rdvPossible(new DateTime('next monday 17:45'), 30))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->praticien->rdvPossible(new DateTime('next monday 18:00'), 15))
        ->toThrow(InvalidArgumentException::class);
});

// ------------------------------------------------------------ liste des RDV du praticien

it('has no appointment by default', function () {

    expect($this->praticien->getRendezVous())->toHaveCount(0);
});

it('keeps the appointments that are added to it', function () {

    $premier = ($this->rdv)('next monday 10:00');
    $second = ($this->rdv)('next monday 11:00');

    $this->praticien->ajouterRendezVous($premier);
    $this->praticien->ajouterRendezVous($second);

    expect($this->praticien->getRendezVous())->toHaveCount(2)
        ->and($this->praticien->getRendezVous()[0])->toBe($premier)
        ->and($this->praticien->getRendezVous()[1])->toBe($second);
});

// ------------------------------------------------------------ verifierDisponibilite()

it('accepts a request when the praticien has no appointment', function () {

    $demande = ($this->rdv)('next monday 10:00');

    $this->praticien->verifierDisponibilite($demande);

    expect($this->praticien->getRendezVous())->toHaveCount(0);
});

it('refuses a request overlapping an existing appointment', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30));

    // 3 cas de chevauchement : même créneau / début pendant le RDV / fin pendant le RDV
    foreach (['10:00', '10:15', '09:45'] as $heure) {
        $demande = ($this->rdv)("next monday $heure", 30);

        expect(fn () => $this->praticien->verifierDisponibilite($demande))
            ->toThrow(PraticienIndisponibleException::class);
    }
});

it('refuses a request fully containing an existing appointment', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:15', 15));

    $demande = ($this->rdv)('next monday 10:00', 60);

    expect(fn () => $this->praticien->verifierDisponibilite($demande))
        ->toThrow(PraticienIndisponibleException::class);
});

it('accepts back-to-back appointments', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30));

    $avant = ($this->rdv)('next monday 09:30', 30);  
    $apres = ($this->rdv)('next monday 10:30', 30);  

    $this->praticien->verifierDisponibilite($avant);
    $this->praticien->verifierDisponibilite($apres);

    expect($this->praticien->getRendezVous())->toHaveCount(1);
});

it('accepts a request at the same time on another day', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30));

    $demande = ($this->rdv)('next tuesday 10:00', 30);

    $this->praticien->verifierDisponibilite($demande);

    expect($this->praticien->getRendezVous())->toHaveCount(1);
});

it('ignores cancelled appointments', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30, 1));

    $demande = ($this->rdv)('next monday 10:00', 30);

    $this->praticien->verifierDisponibilite($demande);

    expect($this->praticien->getRendezVous())->toHaveCount(1);
});

it('still detects an overlap when a cancelled appointment comes first in the list', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 09:00', 30, 1));
    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30, 0));

    $demande = ($this->rdv)('next monday 10:15', 15);

    expect(fn () => $this->praticien->verifierDisponibilite($demande))
        ->toThrow(PraticienIndisponibleException::class);
});

it('refuses a request overlapping an appointment that was already honoured', function () {

    $this->praticien->ajouterRendezVous(($this->rdv)('next monday 10:00', 30, 2));

    $demande = ($this->rdv)('next monday 10:00', 30);

    expect(fn () => $this->praticien->verifierDisponibilite($demande))
        ->toThrow(PraticienIndisponibleException::class);
});