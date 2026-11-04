<?php
namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\adapters\persistence\RepositoryDatabaseErrorException;
use toubilib\domain\exceptions\RendezVousNotFoundException;

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

    public function findById(string $id): RendezVous {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    pa.nom    AS patient_nom,
                    pa.prenom AS patient_prenom,
                    pr.nom    AS praticien_nom,
                    pr.prenom AS praticien_prenom
            FROM rdv r
            LEFT JOIN patient pa   ON r.patient_id = pa.id
            LEFT JOIN praticien pr ON r.praticien_id = pr.id
            WHERE r.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new RendezVousNotFoundException("Aucun rendez-vous trouvé avec l'ID : $id");
        }

        $rdv = new RendezVous(
            $data['id'],
            $data['praticien_id'],
            $data['patient_id'],
            new \DateTime($data['date_heure_debut']),
            new \DateTime($data['date_heure_fin']),
            new \DateTime($data['date_creation']),
            (int)$data['statut'],
            (int)$data['duree'],
            $data['motif_visite']
        );

        $rdv->setInformationsPatient($data['patient_nom'], $data['patient_prenom']);
        $rdv->setInformationsPraticien($data['praticien_nom'], $data['praticien_prenom']);

        return $rdv;
    }

}