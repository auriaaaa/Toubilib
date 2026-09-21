<?php

namespace toubilib\adapters\config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

use toubilib\application\usecases\ServiceRendezVous;
use toubilib\application\ports\api\ServiceRendezVousInterface;

use toubilib\adapters\persistence\RendezVousRepository;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

final class ContainerConfig
{
    public static function build(): \DI\Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([

            ServiceRendezVousInterface::class =>
                \DI\create(ServiceRendezVous::class)
                    ->constructor(
                        \DI\get(RendezVousRepositoryInterface::class)
                    ),

            RendezVousRepositoryInterface::class =>
                \DI\create(RendezVousRepository::class)
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
