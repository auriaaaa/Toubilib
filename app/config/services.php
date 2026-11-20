<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;

use toubilib\application\ports\api\{ServicePraticienInterface, ServiceRendezVousInterface, ServicePatientInterface};
use toubilib\application\ports\spi\{PatientRepositoryInterface, PraticienRepositoryInterface, RendezVousRepositoryInterface};
use toubilib\application\usecases\{ServicePraticien, ServiceRendezVous, ServicePatient};
use toubilib\application\validators\CreerRendezVousValidator;
use toubilib\domain\entities\{Patient, Praticien, RendezVous};

return [

    'doctrine.mapping_path' => __DIR__ . '/../src/adapters/persistence/mapping/',

    // EntityManager Doctrine
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
        $config = ORMSetup::createXMLMetadataConfiguration([$c->get('doctrine.mapping_path')],  true);
        $connection = DriverManager::getConnection($c->get('db'), $config);

        return new EntityManager($connection, $config);
    },

    // Repositories
    PraticienRepositoryInterface::class => function (ContainerInterface $c) {
        return $c->get(EntityManagerInterface::class)->getRepository(Praticien::class);
    },
    PatientRepositoryInterface::class => function (ContainerInterface $c) {
        return $c->get(EntityManagerInterface::class)->getRepository(Patient::class);
    },
    RendezVousRepositoryInterface::class => function (ContainerInterface $c) {
        return $c->get(EntityManagerInterface::class)->getRepository(RendezVous::class);
    },
    SpecialiteRepositoryInterface::class => function (ContainerInterface $c) {
        return $c->get(EntityManagerInterface::class)->getRepository(Specialite::class);
    },

    // Validateur 
    CreerRendezVousValidator::class => \DI\create(CreerRendezVousValidator::class)
        ->constructor(
            \DI\get(PraticienRepositoryInterface::class),
            \DI\get(PatientRepositoryInterface::class)
        ),

    // Services
    ServicePraticienInterface::class => \DI\create(ServicePraticien::class)
        ->constructor(\DI\get(PraticienRepositoryInterface::class)),

    ServiceRendezVousInterface::class => \DI\create(ServiceRendezVous::class)
        ->constructor(
            \DI\get(RendezVousRepositoryInterface::class),
            \DI\get(PraticienRepositoryInterface::class),
            \DI\get(PatientRepositoryInterface::class),
            \DI\get(CreerRendezVousValidator::class)
        ),

    ServicePatientInterface::class => \DI\create(ServicePatient::class)
        ->constructor(
            \DI\get(PatientRepositoryInterface::class),
            \DI\get(RendezVousRepositoryInterface::class)
        ),
];