<?php

$dotenv = \Dotenv\Dotenv::createImmutable(
    __DIR__ . '/../../',
    'toubilib.env'
);
$dotenv->load();

return [];