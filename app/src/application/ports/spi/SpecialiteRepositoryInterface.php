<?php
namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Specialite;

interface SpecialiteRepositoryInterface
{
    public function rechercherParMotCle(string $motCle): array;
}