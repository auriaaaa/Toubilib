<?php

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'db.driver'   => \DI\env('POSTGRES_DRIVER', 'pgsql'),
    'db.host'     => \DI\env('POSTGRES_HOST', 'toubilib.db'),
    'db.port'     => \DI\env('POSTGRES_PORT', '5432'),
    'db.name'     => \DI\env('POSTGRES_DB'),
    'db.user'     => \DI\env('POSTGRES_USER'),
    'db.password' => \DI\env('POSTGRES_PASSWORD'),
];