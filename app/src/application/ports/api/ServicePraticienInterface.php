<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use DateTime;
use toubilib\application\dto\PraticienDTO;

interface ServicePraticienInterface
{
    public function getPraticien(string $id): PraticienDTO;

    public function getAllPraticiens(): array;

    public function getCreneauxOccupes(
        string $praticienId,
        DateTime $debut,
        DateTime $fin
    ): array;
}