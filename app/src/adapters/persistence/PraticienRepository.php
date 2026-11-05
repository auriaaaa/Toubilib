<?php
namespace toubilib\adapters\persistence;

use DateTime;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\adapters\persistence\RepositoryDatabaseErrorException;
use toubilib\domain\exceptions\PraticienNotFoundException;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\Specialite;

class PraticienRepository implements PraticienRepositoryInterface {

    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Praticien $praticien): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO praticien (id, nom, prenom, specialite_id, ville, email, telephone, organisation, nouveau_patient, titre) 
                                              VALUES (:id, :nom, :prenom, :specialite_id, :ville, :email, :telephone, :organisation, :nouveau_patient, :titre)
                                              ON CONFLICT (id) DO UPDATE SET 
                                              nom = EXCLUDED.nom,
                                              prenom = EXCLUDED.prenom,
                                              specialite_id = EXCLUDED.specialite_id,
                                              ville = EXCLUDED.ville,
                                              email = EXCLUDED.email,
                                              telephone = EXCLUDED.telephone,
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
                'organisation' => $praticien->isOrganisation() ? 'true' : 'false',
                'nouveau_patient' => $praticien->isNewPatient() ? 'true' : 'false',
                'titre' => $praticien->getTitre()
            ]);
        } catch (\PDOException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : ".$e->getMessage());
        }
    }

    public function findById(string $id): Praticien {
        $stmt = $this->pdo->prepare(
            'SELECT p.*,
                    s.libelle     AS specialite_libelle,
                    s.description AS specialite_description
            FROM praticien p
            LEFT JOIN specialite s ON p.specialite_id = s.id
            WHERE p.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new PraticienNotFoundException("Aucun praticien trouvé avec l'ID : $id");
        }

        $praticien = new Praticien(
            $data['id'],
            $data['nom'],
            $data['prenom'],
            $data['ville'],
            $data['email'],
            $data['telephone'],
            (int)$data['specialite_id'],
            (bool)$data['organisation'],
            (bool)$data['nouveau_patient'],
            $data['titre']
        );

        if ($data['specialite_libelle'] !== null) {
            $praticien->setSpecialite(new Specialite(
                (int)$data['specialite_id'],
                $data['specialite_libelle'],
                $data['specialite_description'] ?? ''
            ));
        }

        return $praticien;
    }

    public function findByIdAvecRendezVous(string $id, DateTime $debut, DateTime $fin): Praticien {
        $praticien = $this->findById($id);

        $stmt = $this->pdo->prepare('SELECT rdv.*, patient.nom AS patient_nom, patient.prenom AS patient_prenom
            FROM rdv
            INNER JOIN patient ON rdv.patient_id = patient.id
            WHERE rdv.praticien_id = :id
            AND rdv.statut <> :annule
            AND rdv.date_heure_debut < :fin
            AND rdv.date_heure_fin > :debut
            ORDER BY rdv.date_heure_debut');

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

            $rdv->setInformationsPatient($data['patient_nom'], $data['patient_prenom']);

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
                (bool)$data['organisation'],
                (bool)$data['nouveau_patient'],
                $data['titre']
            );
        }
        return $praticiens;
    }

    public function rechercher(?int $specialiteId, ?string $ville): array {
        $sql = 'SELECT * FROM praticien';
        $conditions = [];
        $params = [];

        if ($specialiteId !== null) {
            $conditions[] = 'specialite_id = :specialite_id';
            $params['specialite_id'] = $specialiteId;
        }

        if ($ville !== null && trim($ville) !== '') {
            $conditions[] = 'LOWER(ville) LIKE LOWER(:ville)';
            $params['ville'] = '%' . trim($ville) . '%';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $praticiens = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $praticiens[] = new Praticien(
                $data['id'],
                $data['nom'],
                $data['prenom'],
                $data['ville'],
                $data['email'],
                $data['telephone'],
                (int) $data['specialite_id'],
                (bool) $data['organisation'],
                (bool) $data['nouveau_patient'],
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