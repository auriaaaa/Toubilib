<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDTO;

interface ServicePraticienInterface
{
    public function getPraticien(string $id): PraticienDTO;

    public function getAllPraticiens(): array;
}