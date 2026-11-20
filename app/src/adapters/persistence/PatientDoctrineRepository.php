<?php
namespace toubilib\adapters\persistence;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class PatientDoctrineRepository extends EntityRepository implements PatientRepositoryInterface
{
    public function save(Patient $patient): void
    {
        try {
            $this->getEntityManager()->persist($patient);
            $this->getEntityManager()->flush();
        } catch (DBALException | ORMException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : " . $e->getMessage());
        }
    }

    public function findById(string $id): Patient
    {
        $patient = $this->find($id);

        if ($patient === null) {
            throw new PatientNotFoundException("Aucun patient trouvé avec l'ID : $id");
        }

        return $patient;
    }

    public function existe(string $id): bool
    {
        return $this->count(['id' => $id]) > 0;
    }
}