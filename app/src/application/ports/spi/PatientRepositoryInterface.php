<?php
namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Patient;

interface PatientRepositoryInterface
{
    public function save(Patient $patient): void;

    public function findById(string $id): Patient;

    public function existe(string $id): bool;
} 