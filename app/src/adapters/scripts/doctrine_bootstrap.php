<?php

// require_once __DIR__ . '/../../../vendor/autoload.php';

// use Doctrine\DBAL\DriverManager;
// use Doctrine\ORM\EntityManager;
// use Doctrine\ORM\ORMSetup;


// $mapping_path = __DIR__ . '/../persistence/mapping/';
// $isDevMode=true;

// $dbParams = parse_ini_file(__DIR__ . '/../../../config/toubilib.ini');
// // var_dump($dbParams);

// $config = ORMSetup::createXMLMetadataConfiguration([$mapping_path], $isDevMode);
// $connection = DriverManager::getConnection($dbParams, $config);
// $entityManager = new EntityManager($connection, $config);


require_once __DIR__ . '/../../../vendor/autoload.php';

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;

// Mêmes fichiers de configuration que l'application 
$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/../../../config/settings.php');      // contient le tableau 'db'
$builder->addDefinitions(__DIR__ . '/../../../config/services.php');  // contient l'EntityManager
$container = $builder->build();


$entityManager = $container->get(EntityManagerInterface::class);

