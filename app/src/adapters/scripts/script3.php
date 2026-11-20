<?php

require_once __DIR__ . '/doctrine_bootstrap.php';

use Doctrine\Common\Collections\Criteria;
use toubilib\domain\entities\Specialite;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;


$specialiteRepository = $entityManager->getRepository(Specialite::class);
$praticienRepository = $entityManager->getRepository(Praticien::class);
$rdvRepository = $entityManager->getRepository(RendezVous::class);


// liste des spécialités contenant un mot clé dans le libellé ou la description

$specialites = $specialiteRepository->rechercherParMotCle('ophtalmo');

print 'Question 1'. "\n";
foreach ($specialites as $s) {
    print $s->getId() . " - " . $s->getLibelle() . "\n";
}
print '-------------------------------------------'. "\n";


// liste des praticiens dont la spécialité contient un mot-clé dans le libellé ou la description

$praticiens = $praticienRepository->rechercherParMotCleSpecialite('ophtalmo');

print 'Question 2'. "\n";
foreach ($praticiens as $p) {
    print $p->getTitre() . " " . $p->getNom() . " (" . $p->getSpecialite()->getLibelle() . ")\n";
}
print '-------------------------------------------'. "\n";



// liste des praticiens d’une spécialité exerçant dans une ville donnée

$praticiens = $praticienRepository->rechercher(1, 'Nancy');

print 'Question 3'. "\n";
foreach ($praticiens as $p) {
    print $p->getTitre() . " " . $p->getNom() . " " . $p->getPrenom() . " (" . $p->getSpecialite()->getLibelle() . " - " .  $p->getVille() . ") \n";
}
print '-------------------------------------------'. "\n";


// Rendez-vous prévus pour un praticien sur une période donnée : date de début, durée, statut

$rdvs = $rdvRepository->findPrevusPourPraticien(
    '6a44c94902f47',
    new DateTime('2026-11-01'),
    new DateTime('2026-12-01')
);

print 'Question 4' . "\n";

if (count($rdvs) === 0) {
    print "Aucun rendez-vous prévu\n";
}

foreach ($rdvs as $r) {
    print $r['date_heure_debut']->format('d/m/Y H:i')
        . " - " . $r['duree'] . " min - statut " . $r['statut'] . "\n";
}
print '-------------------------------------------' . "\n";


// Agenda d'un praticien sur une journée : rendez-vous incluant le motif de visite et le patient (nom, prénom, téléphone, email)

$jour = new DateTime('2026-11-24');
$debut = (clone $jour)->setTime(0, 0);
$fin = (clone $debut)->modify('+1 day');

$praticien = $praticienRepository->findByIdAvecRendezVous('6a44c94902f47', $debut, $fin);

print 'Question 5' . "\n";
print 'Agenda de ' . $praticien->getTitre() . ' ' . $praticien->getNom()
    . ' le ' . $jour->format('d/m/Y') . "\n";

if ($praticien->getRendezVous()->isEmpty()) {
    print "Aucun rendez-vous ce jour-là\n";
}

foreach ($praticien->getRendezVous() as $rdv) {
    $p = $rdv->getPatient();
    print $rdv->getDateHeureDebut()->format('H:i')
        . " - " . $rdv->getMotifVisite()
        . " - " . $p->getNom() . " " . $p->getPrenom()
        . " - " . $p->getTelephone() . " - " . $p->getEmail() . "\n";
}
print '-------------------------------------------' . "\n";


// Historique des rendez-vous d'un patient : pour chaque rdv, date, heure, durée, motif, nom, prénom et spécialité du praticien

$rendezvous = $rdvRepository->findByPatientId('6a44c902fb686');

print 'Question 6' . "\n";

if (count($rendezvous) === 0) {
    print "Aucun rendez-vous\n";
}

foreach ($rendezvous as $rdv) {
    $pr = $rdv->getPraticien();
    print $rdv->getDateHeureDebut()->format('d/m/Y') . " "
        . $rdv->getDateHeureDebut()->format('H:i')
        . " - " . $rdv->getDuree() . " min"
        . " - " . $rdv->getMotifVisite()
        . " - " . $pr->getNom() . " " . $pr->getPrenom()
        . " (" . $pr->getSpecialite()->getLibelle() . ")\n";
}
print '-------------------------------------------' . "\n";


