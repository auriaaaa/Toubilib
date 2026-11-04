<?php
use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->useAutowiring(false);

$builder->addDefinitions(
    __DIR__ . '/settings.php',   // charge aussi le .env
    __DIR__ . '/services.php',
    __DIR__ . '/api.php'
);

return $builder->build();