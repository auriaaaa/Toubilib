<?php
use Psr\Container\ContainerInterface;
use toubilib\adapters\persistence\{PatientRepository, PraticienRepository, RendezVousRepository};
use toubilib\application\ports\api\{ServicePraticienInterface, ServiceRendezVousInterface, ServicePatientInterface};
use toubilib\application\ports\spi\{PatientRepositoryInterface, PraticienRepositoryInterface, RendezVousRepositoryInterface};
use toubilib\application\usecases\{ServicePraticien, ServiceRendezVous, ServicePatient};
use toubilib\application\validators\CreerRendezVousValidator;

return [

    // Connexion PDO
    'toubilib.pdo' => function (ContainerInterface $c): \PDO {
        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s',
            $c->get('db.driver'), $c->get('db.host'), $c->get('db.port'), $c->get('db.name'));

        return new \PDO($dsn, $c->get('db.user'), $c->get('db.password'), [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    },

    // Repositories
    PraticienRepositoryInterface::class  => \DI\create(PraticienRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),
    PatientRepositoryInterface::class    => \DI\create(PatientRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),
    RendezVousRepositoryInterface::class => \DI\create(RendezVousRepository::class)
        ->constructor(\DI\get('toubilib.pdo')),

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