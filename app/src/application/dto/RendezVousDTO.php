<?php

namespace toubilib\application\dto;

use DateTime;

use toubilib\application\exceptions\ValidationException;
use toubilib\domain\entities\RendezVous;

final class RendezVousDTO
{
    private function __construct(
        public readonly string $id,
        public readonly string $praticien_id,
        public readonly string $patient_id,
        public readonly DateTime $date_heure_debut,
        public readonly int $duree,
        public readonly int $statut,

        //public readonly DateTime $date_heure_fin,
        //public readonly DateTime $date_creation,
        //public readonly string $motif_visite,
    ) {}

    public static function fromArray(array $body): self {

        $id                 = self::asString($body['id'] ?? null, 'id', $errors);
        $praticien_id       = self::asString($body['praticien_id'] ?? null, 'praticien_id', $errors);
        $patient_id         = self::asString($body['patient_id'] ?? null, 'patient_id', $errors);
        $date_heure_debut   = self::asDateTime($body['date_heure_debut'] ?? null, 'date_heure_debut', $errors);
        $duree              = self::asInt($body['duree'] ?? null, 'duree', $errors);
        $statut             = self::asInt($body['statut'] ?? null, 'statut', $errors);
        
        if ($id === null || $praticien_id === null || $patient_id === null || $date_heure_debut === null || $duree === null || $statut === null) {
            $errors['body'] = 'Tous les champs sont obligatoires.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return new self($id, $praticien_id, $patient_id, $date_heure_debut, $duree, $statut);
    }

    public static function fromEntity(RendezVous $rendezVous): self
    {
        return new self(
            $rendezVous->getId(),
            $rendezVous->getPraticienId(),
            $rendezVous->getPatientId(),
            $rendezVous->getDateHeureDebut(),
            $rendezVous->getDuree(),
            $rendezVous->getStatut(),
        );
    }

    private static function asString(mixed $value, string $field, array &$errors): ?string {
        if (!is_string($value) && !is_numeric($value)) {
            $errors[$field] = 'Le champ ' . $field . ' doit être une chaîne de caractères.';
            return null;
        }
        return (string) $value;
    }

    private static function asInt(mixed $value, string $field, array &$errors): ?int {
        if (!is_int($value) && !is_numeric($value)) {
            $errors[$field] = 'Le champ ' . $field . ' doit être un entier.';
            return null;
        }
        return (int) $value;
    }

    private static function asDateTime(mixed $value, string $field, array &$errors): ?DateTime {
        if (!is_string($value) && !is_numeric($value)) {
            $errors[$field] = 'Le champ ' . $field . ' doit être une date-time valide.';
            return null;
        }
        try {
            return new DateTime((string) $value);
        } catch (\Exception $e) {
            $errors[$field] = 'Le champ ' . $field . ' doit être une date-time valide.';
            return null;
        }
    }
}