<?php
namespace toubilib\adapters\persistence;

use DateTime;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\adapters\persistence\RepositoryDatabaseErrorException;

class PraticienRepository implements PraticienRepositoryInterface {

    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Praticien $praticien): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO praticien (id, nom, prenom, specialite_id, ville, email, telephone, structure_id, rpps_id, organisation, nouveau_patient, titre) 
                                              VALUES (:id, :nom, :prenom, :specialite_id, :ville, :email, :telephone, :structure_id, :rpps_id, :organisation, :nouveau_patient, :titre)
                                              ON CONFLICT (id) DO UPDATE SET 
                                              nom = EXCLUDED.nom,
                                              prenom = EXCLUDED.prenom,
                                              specialite_id = EXCLUDED.specialite_id,
                                              ville = EXCLUDED.ville,
                                              email = EXCLUDED.email,
                                              telephone = EXCLUDED.telephone,
                                              structure_id = EXCLUDED.structure_id,
                                              rpps_id = EXCLUDED.rpps_id,
                                              organisation = EXCLUDED.organisation,
                                              nouveau_patient = EXCLUDED.nouveau_patient,
                                              titre = EXCLUDED.titre');
            $stmt->execute([
                'id' => $praticien->getId(),
                'nom' => $praticien->getNom(),
                'prenom' => $praticien->getPrenom(),
                'specialite_id' => $praticien->getSpecialiteId(),
                'ville' => $praticien->getVille(),
                'email' => $praticien->getEmail(),
                'telephone' => $praticien->getTelephone(),
                'structure_id' => $praticien->getStructureId(),
                'rpps_id' => $praticien->getRppsId(),
                'organisation' => $praticien->isOrganisation() ? 'true' : 'false',
                'nouveau_patient' => $praticien->isNewPatient() ? 'true' : 'false',
                'titre' => $praticien->getTitre()
            ]);
        } catch (\PDOException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : ".$e->getMessage());
        }
    }

    public function findById(string $id) : Praticien {
        $stmt = $this->pdo->prepare('SELECT * FROM praticien WHERE id = :id');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw new \Exception("Praticien not found");
        }
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new Praticien(
            $data['id'],
            $data['nom'],
            $data['prenom'],
            $data['ville'],
            $data['email'],
            $data['telephone'],
            (int)$data['specialite_id'],
            $data['structure_id'],
            $data['rpps_id'] ?? '',
            (bool)$data['organisation'],
            (bool)$data['nouveau_patient'],
            $data['titre']
        );
    }

    public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien {
        $praticien = $this->findById($id);

        $stmt = $this->pdo->prepare('SELECT * FROM rdv
                                     WHERE praticien_id = :id
                                       AND statut <> :annule
                                       AND date_heure_debut < :fin
                                       AND date_heure_fin > :debut
                                     ORDER BY date_heure_debut');
        $stmt->execute([
            'id' => $id,
            'annule' => 1,
            'debut' => $debut->format('Y-m-d H:i:s'),
            'fin' => $fin->format('Y-m-d H:i:s'),
        ]);

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $rdv = new RendezVous(
                $data['id'],
                $data['praticien_id'],
                $data['patient_id'],
                new DateTime($data['date_heure_debut']),
                new DateTime($data['date_heure_fin']),
                new DateTime($data['date_creation']),
                (int) $data['statut'],
                (int) $data['duree'],
                $data['motif_visite']
            );

            $praticien->ajouterRendezVous($rdv);
        }

        return $praticien;
    }

    public function findAll(): array {
        $stmt = $this->pdo->query('SELECT * FROM praticien');
        $praticiens = [];
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $praticiens[] = new Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'],
                $data['email'],
                $data['telephone'],
                (int)$data['specialite_id'],
                $data['structure_id'],
                $data['rpps_id'] ?? '',
                (bool)$data['organisation'],
                (bool)$data['nouveau_patient'],
                $data['titre']
            );
        }
        return $praticiens;
    }

    public function existe(string $id): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM praticien WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

}