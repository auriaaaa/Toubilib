<?php
namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\adapters\persistence\RepositoryDatabaseErrorException;

class PatientRepository implements PatientRepositoryInterface {

    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function save(Patient $patient): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO patient (id, nom, prenom, date_naissance, adresse, code_postal, ville, email, telephone) 
                                              VALUES (:id, :nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :email, :telephone)
                                              ON CONFLICT (id) DO UPDATE SET 
                                              nom = EXCLUDED.nom,
                                              prenom = EXCLUDED.prenom,
                                              date_naissance = EXCLUDED.date_naissance,
                                              adresse = EXCLUDED.adresse,
                                              code_postal = EXCLUDED.code_postal,
                                              ville = EXCLUDED.ville,
                                              email = EXCLUDED.email,
                                              telephone = EXCLUDED.telephone');
            $stmt->execute([
                'id' => $patient->getId(),
                'nom' => $patient->getNom(),
                'prenom' => $patient->getPrenom(),
                'date_naissance' => $patient->getDateNaissance(),
                'adresse' => $patient->getAdresse(),
                'code_postal' => $patient->getCodePostal(),
                'ville' => $patient->getVille(),
                'email' => $patient->getEmail(),
                'telephone' => $patient->getTelephone()
            ]);
        } catch (\PDOException $e) {
            throw new RepositoryDatabaseErrorException("Database Error : ".$e->getMessage());
        }
    }

    public function findById(string $id): Patient {
        $stmt = $this->pdo->prepare('SELECT * FROM patient WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($data === false) {
            throw new \Exception("Patient not found");
        }

        return new Patient(
            $data['id'],
            $data['nom'],
            $data['prenom'],
            (string) ($data['date_naissance'] ?? ''),
            $data['adresse'] ?? '',
            $data['code_postal'] ?? '',
            $data['ville'] ?? '',
            $data['email'] ?? '',
            $data['telephone'] ?? ''
        );
    }

    public function existe(string $id): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM patient WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

}