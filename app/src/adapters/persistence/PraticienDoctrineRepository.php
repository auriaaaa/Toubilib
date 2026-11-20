<?php
namespace toubilib\adapters\persistence;

use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PraticienNotFoundException;

class PraticienDoctrineRepository extends EntityRepository implements PraticienRepositoryInterface
{
    public function save(Praticien $praticien): void
    {
        try {
            $this->getEntityManager()->persist($praticien);
            $this->getEntityManager()->flush();
        } catch (DBALException | ORMException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : " . $e->getMessage());
        }
    }

    public function findById(string $id): Praticien
    {
        $praticien = $this->find($id);

        if ($praticien === null) {
            throw new PraticienNotFoundException("Aucun praticien trouvé avec l'ID : $id");
        }

        return $praticien;
    }

    public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien
    {
        $dql = "SELECT p, r, pat
                FROM " . Praticien::class . " p
                LEFT JOIN p.rendezVous r
                    WITH r.statut <> :annule
                     AND r.date_heure_debut < :fin
                     AND r.date_heure_fin > :debut
                LEFT JOIN r.patient pat
                WHERE p.id = :id
                ORDER BY r.date_heure_debut ASC";

        $praticien = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('id', $id)
            ->setParameter('annule', 1)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getOneOrNullResult();

        if ($praticien === null) {
            throw new PraticienNotFoundException("Aucun praticien trouvé avec l'ID : $id");
        }

        return $praticien;
    }

    public function rechercher(?int $specialiteId, ?string $ville): array
    {
        $dql = "SELECT p FROM " . Praticien::class . " p";
        $conditions = [];
        $params = [];

        if ($specialiteId !== null) {
            $conditions[] = 'p.specialite = :specialite';
            $params['specialite'] = $specialiteId;
        }

        if ($ville !== null && trim($ville) !== '') {
            $conditions[] = 'LOWER(p.ville) LIKE LOWER(:ville)';
            $params['ville'] = '%' . trim($ville) . '%';
        }

        if (!empty($conditions)) {
            $dql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query = $this->getEntityManager()->createQuery($dql);
        foreach ($params as $nom => $valeur) {
            $query->setParameter($nom, $valeur);
        }

        return $query->getResult();
    }
    public function rechercherParMotCleSpecialite(string $motCle): array
    {
        $dql = "SELECT p, s
                FROM " . Praticien::class . " p
                JOIN p.specialite s
                WHERE LOWER(s.libelle) LIKE LOWER(:mc)
                   OR LOWER(s.description) LIKE LOWER(:mc)
                ORDER BY p.nom ASC, p.prenom ASC";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('mc', '%' . $motCle . '%')
            ->getResult();
    }

    public function existe(string $id): bool
    {
        return $this->count(['id' => $id]) > 0;
    }
}