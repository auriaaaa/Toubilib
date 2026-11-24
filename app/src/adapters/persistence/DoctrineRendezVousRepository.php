<?php
namespace toubilib\adapters\persistence;

use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousNotFoundException;

class DoctrineRendezVousRepository extends EntityRepository implements RendezVousRepositoryInterface
{
    public function save(RendezVous $rendezVous): void
    {
        try {
            $this->getEntityManager()->persist($rendezVous);
            $this->getEntityManager()->flush();
        } catch (DBALException | ORMException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : " . $e->getMessage());
        }
    }

    public function findById(string $id): RendezVous
    {
        $rdv = $this->find($id);

        if ($rdv === null) {
            throw new RendezVousNotFoundException("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        return $rdv;
    }

    public function findByPatientId(string $patientId): array
    {
        // Fetch join : patient, praticien et sa spécialité chargés avec le RDV
        $dql = "SELECT r, pa, pr, sp
                FROM " . RendezVous::class . " r
                JOIN r.patient pa
                JOIN r.praticien pr
                JOIN pr.specialite sp
                WHERE pa.id = :id
                ORDER BY r.date_heure_debut DESC";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('id', $patientId)
            ->getResult();
    }
    
    public function findPrevusPourPraticien(string $praticienId, DateTime $debut, DateTime $fin): array
    {
        $dql = "SELECT r.date_heure_debut, r.duree, r.statut
                FROM " . RendezVous::class . " r
                WHERE r.praticien = :praticien
                  AND r.statut = :prevu
                  AND r.date_heure_debut >= :debut
                  AND r.date_heure_debut < :fin
                ORDER BY r.date_heure_debut ASC";

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('praticien', $praticienId)
            ->setParameter('prevu', 0)
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getResult();
    }
}