<?php

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'db' => [
        'driver'   => \DI\env('POSTGRES_DRIVER', 'pdo_pgsql'),
        'host'     => \DI\env('POSTGRES_HOST', 'toubilib.db'),
        'port'     => \DI\env('POSTGRES_PORT', '5432'),
        'dbname'   => \DI\env('POSTGRES_DB'),
        'user'     => \DI\env('POSTGRES_USER'),
        'password' => \DI\env('POSTGRES_PASSWORD'),
    ],
];