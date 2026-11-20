<?php

require_once __DIR__ . '/doctrine_bootstrap.php';

use toubilib\domain\entities\Specialite;
use toubilib\domain\entities\Praticien;

$specialiteRepository = $entityManager->getRepository(Specialite::class);
$praticienRepository = $entityManager->getRepository(Praticien::class);

// Afficher la spécialité d'identifiant 1 : id, libellé, description.

$s = $specialiteRepository->find(1);

if ($s === null) {
    exit("La spécialité 1 n'existe pas\n");
}

print 'Question 1'. "\n";
print $s->getId() . "\n";
print $s->getLibelle() . "\n";
print $s->getDescription() . "\n";
print '-------------------------------------------'. "\n";


// Afficher le praticien dont l’id est : 6a44c94902f47 - id, nom, prénom, ville, email, téléphone

$p = $praticienRepository->find('6a44c94902f47');

if ($p === null) {
    exit("Le praticien n'existe pas\n");
}

print 'Question 2'. "\n";
print $p->getId() . "\n";
print $p->getNom() . "\n";
print $p->getPrenom() . "\n";
print $p->getVille() . "\n";
print $p->getEmail() . "\n";
print $p->getTelephone() . "\n";
print '-------------------------------------------'. "\n";


// Compléter en affichant sa spécialité (id, libellé, descrption)

$s = $p->getSpecialite();

print 'Question 3'. "\n";
print $s->getId() . "\n";
print $s->getLibelle() . "\n";
print $s->getDescription() . "\n";
print '-------------------------------------------'. "\n";


// Afficher la spécialité d'identifiant 1 et la liste de praticiens associée.

$s = $specialiteRepository->find(1);

if ($s === null) {
    exit("La spécialité 1 n'existe pas\n");
}

print 'Question 4'. "\n";

foreach ($s->getPraticiens() as $p) {
    print "- " . $p->getNom() . " " . $p->getPrenom() . "\n";
}
print '-------------------------------------------'. "\n";


// Créer un praticien, spécialité pédiatrie (3), et le sauvegarder dans la base,

$s = $entityManager->getRepository(Specialite::class)->find(3);

if ($s === null) {
    exit("La spécialité 3 n'existe pas\n");
}

$p = new Praticien(
    uniqid(),
    "Guyot",
    "Auriane",
    "Nancy",
    "auriane.guyot@exemple.com",
    "0383000000",
    $s,
    false,
    true,
    "Dr."
);

$entityManager->persist($p);
$entityManager->flush();

print 'Question 5'. "\n";
print "Praticien créé : " . $p->getId() . "\n";
print '-------------------------------------------'. "\n";


// Modifier ce praticien : changer sa ville pour "Paris", modifier son numéro de téléphone et sauvegarder dans la base.

print 'Question 6' . "\n";
print "Ville avant maj : " . $p->getVille() . "\n";
print "Téléphone avant maj : " . $p->getTelephone() . "\n";

$p->setVille("Paris");
$p->setTelephone("1234567890");
$entityManager->flush();

print "Ville après maj : " . $p->getVille() . "\n";
print "Téléphone après maj : " . $p->getTelephone() . "\n";
print '-------------------------------------------' . "\n";


// Supprimer ce praticien et mettre à jour la base.

$entityManager->remove($p);
$entityManager->flush();

print 'Question 7'. "\n";
print "Praticien supprimé \n";
print '-------------------------------------------'. "\n";