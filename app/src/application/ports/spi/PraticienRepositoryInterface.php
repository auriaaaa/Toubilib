<?php
namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    public function save(Praticien $praticien): void;

    public function findById(string $id): Praticien;

    public function findAll(): array;
} 