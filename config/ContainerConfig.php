<?php

namespace toubilib\adapters\config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\ports\api\ServiceRendezVousInterface;

use toubilib\adapters\persistence\PraticienRepository;
use toubilib\adapters\persistence\PatientRepository;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\validators\CreateRendezVousValidator;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\usecases\ServicePraticien;
use toubilib\application\ports\api\ServicePraticienInterface;

final class ContainerConfig
{
    public static function build(): \DI\Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([

            ServiceRendezVousInterface::class =>
                \DI\create(ServiceRendezVous::class)
                    ->constructor(
                        \DI\get(RendezVousRepositoryInterface::class),
                        \DI\get(PraticienRepositoryInterface::class),
                        \DI\get(PatientRepositoryInterface::class),
                        \DI\get(CreateRendezVousValidator::class)
                    ),

            CreateRendezVousValidator::class =>
                \DI\create(CreateRendezVousValidator::class)
                    ->constructor(
                        \DI\get(PraticienRepositoryInterface::class),
                        \DI\get(PatientRepositoryInterface::class)
                    ),

            PatientRepositoryInterface::class =>
                \DI\create(PatientRepository::class)
                    ->constructor(
                        \DI\get('toubilib.pdo')
                    ),

            RendezVousRepositoryInterface::class =>
                \DI\create(RendezVousRepository::class)
                    ->constructor(
                        \DI\get('toubilib.pdo')
                    ),

           ServicePraticienInterface::class =>
                \DI\create(ServicePraticien::class)
                    ->constructor(
                        \DI\get(PraticienRepositoryInterface::class)
                    ),

            PraticienRepositoryInterface::class =>
                \DI\create(PraticienRepository::class)
                    ->constructor(
                        \DI\get('toubilib.pdo')
                    ),

            'toubilib.pdo' => function (ContainerInterface $c) {

                $dsn = "pgsql:host=toubilib.db;dbname={$_ENV['POSTGRES_DB']}";

                return new \PDO(
                    $dsn,
                    $_ENV['POSTGRES_USER'],
                    $_ENV['POSTGRES_PASSWORD'],
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    ]
                );
            },
        ]);

        return $builder->build();
    }
}
