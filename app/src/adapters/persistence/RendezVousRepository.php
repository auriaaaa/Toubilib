<?php
namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\adapters\persistence\RepositoryDatabaseErrorException;

class RendezVousRepository implements RendezVousRepositoryInterface {

    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(RendezVous $rendezVous): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO rdv (id, praticien_id, patient_id, date_heure_debut, date_heure_fin, date_creation, statut, duree, motif_visite) 
                                              VALUES (:id, :praticien_id, :patient_id, :date_heure_debut, :date_heure_fin, :date_creation, :statut, :duree, :motif_visite)
                                              ON CONFLICT (id) DO UPDATE SET 
                                              praticien_id = EXCLUDED.praticien_id,
                                              patient_id = EXCLUDED.patient_id,
                                              date_heure_debut = EXCLUDED.date_heure_debut,
                                              date_heure_fin = EXCLUDED.date_heure_fin,
                                              date_creation = EXCLUDED.date_creation,
                                              statut = EXCLUDED.statut,
                                              duree = EXCLUDED.duree,
                                              motif_visite = EXCLUDED.motif_visite');
            $stmt->execute([
                'id' => $rendezVous->getId(),
                'praticien_id' => $rendezVous->getPraticienId(),
                'patient_id' => $rendezVous->getPatientId(),
                'date_heure_debut' => $rendezVous->getDateHeureDebut()->format('Y-m-d H:i:s'),
                'date_heure_fin' => $rendezVous->getDateHeureFin()->format('Y-m-d H:i:s'),
                'date_creation' => $rendezVous->getDateCreation()->format('Y-m-d H:i:s'),
                'statut' => $rendezVous->getStatut(),
                'duree' => $rendezVous->getDuree(),
                'motif_visite' => $rendezVous->getMotifVisite()
            ]);
        } catch (\PDOException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : ".$e->getMessage());
        }
    }

    public function findById(string $id) : RendezVous {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw new \Exception("Rendez-vous not found");
        }
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new RendezVous(
            $data['id'],
            $data['praticien_id'],
            $data['patient_id'],
            new \DateTime($data['date_heure_debut']),
            new \DateTime($data['date_creation']),
            (int)$data['statut'],
            $data['motif_visite']
        );
    }

}