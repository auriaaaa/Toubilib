<?php

require_once __DIR__ . '/doctrine_bootstrap.php';

use Doctrine\Common\Collections\Criteria;
use toubilib\domain\entities\Specialite;
use toubilib\domain\entities\Praticien;

$specialiteRepository = $entityManager->getRepository(Specialite::class);
$praticienRepository = $entityManager->getRepository(Praticien::class);

// Afficher le praticien dont le mail est Claire.Moreau@free.fr

$p = $praticienRepository->findOneBy(["email" => "Claire.Moreau@free.fr"]);

print 'Question 1'. "\n";
print $p->getId() . "\n";
print $p->getNom() . "\n";
print $p->getPrenom() . "\n";
print $p->getEmail() . "\n";
print '-------------------------------------------'. "\n";


// Afficher le praticien de nom Lambert à Metz

$p = $praticienRepository->findOneBy([
    "nom" => "Lambert",
    "ville" => "Metz"
]);

print 'Question 2'. "\n";
print $p->getId() . "\n";
print $p->getNom() . "\n";
print $p->getPrenom() . "\n";
print $p->getVille() . "\n";
print '-------------------------------------------'. "\n";


// Afficher la spécialité de libellé 'pédiatrie' ainsi que les praticiens associés.

$s = $specialiteRepository->findOneBy(["libelle" => "pédiatrie"]);

print 'Question 3'. "\n";
print $s->getId() . "\n";
print $s->getLibelle() . "\n";
print $s->getDescription() . "\n";

print 'Praticiens associés :'. "\n";
foreach ($s->getPraticiens() as $p) {
    print "- " . $p->getNom() . " " . $p->getPrenom() . "\n";
}

print '-------------------------------------------'. "\n";


// Afficher les praticiens de la spécialité 'ophtalmologie' exerçants à Paris

$s = $specialiteRepository->findOneBy(["libelle" => "ophtalmologie"]);

$praticiens = $praticienRepository->findBy([
    "specialite" => $s,
    "ville" => "Metz"
]);

print 'Question 2'. "\n";
print 'Spécialité :'. "\n";

print $s->getId() . "\n";
print $s->getLibelle() . "\n";
print $s->getDescription() . "\n";

print 'Praticien.s :'. "\n";
foreach ($praticiens as $p) {
    print "- " . $p->getTitre() . " " . $p->getNom() . " " . $p->getPrenom()
        . " (" . $p->getVille() . ")\n";
}

if (count($praticiens) === 0) {
    print "Aucun praticien\n";
}
print '-------------------------------------------'. "\n";



