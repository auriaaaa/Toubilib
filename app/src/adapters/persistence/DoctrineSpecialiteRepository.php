<?php
namespace toubilib\adapters\persistence;

use Doctrine\ORM\EntityRepository;
use toubilib\application\ports\spi\SpecialiteRepositoryInterface;
use toubilib\domain\entities\Specialite;

class DoctrineSpecialiteRepository extends EntityRepository implements SpecialiteRepositoryInterface
{
    public function rechercherParMotCle(string $motCle): array
    {
        $dql = "SELECT s
            FROM " . Specialite::class . " s
            WHERE LOWER(s.libelle) LIKE LOWER(:mot)
            OR LOWER(s.description) LIKE LOWER(:mot)
            ORDER BY s.libelle ASC";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('mot', '%' . $motCle . '%')
            ->getResult();
    }
}