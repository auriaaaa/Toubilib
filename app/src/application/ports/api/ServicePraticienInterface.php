<?php

declare(strict_types=1);

namespace toubilib\application\ports\api;

use DateTime;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\dto\RendezVousAgendaDTO;
use toubilib\application\dto\PraticienDetailDTO;

interface ServicePraticienInterface
{
    public function getPraticien(string $id): PraticienDetailDTO;

    public function getAllPraticiens(): array; // return PraticienDTO[]

    public function getRendezVousPourPeriode(string $praticienId, DateTime $debut, DateTime $fin): array; // return RendezVousAgendaDTO[]

    public function rechercherPraticiens(?string $specialite, ?string $ville): array; // return PraticienDTO[]
}