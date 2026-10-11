<?php

namespace toubilib\application\dto;

use DateTime;
use toubilib\application\exceptions\ValidationException;

final class CreateRendezVousDTO {

    private function __construct(
        public readonly string $praticien_id,
        public readonly string $patient_id,
        public readonly DateTime $date_heure_debut,
        public readonly string $motif_visite,
    ) {}

    public static function fromArray(array $body): self {
        $errors = [];

        $praticien_id     = self::asString($body['praticien_id'] ?? null, 'praticien_id', $errors);
        $patient_id       = self::asString($body['patient_id'] ?? null, 'patient_id', $errors);
        $date_heure_debut = self::asDateTime($body['date_heure_debut'] ?? null, 'date_heure_debut', $errors);
        $motif_visite     = self::asString($body['motif_visite'] ?? null, 'motif_visite', $errors);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return new self($praticien_id, $patient_id, $date_heure_debut, $motif_visite);
    }

    private static function asString(mixed $value, string $field, array &$errors): ?string {
        if ((!is_string($value) && !is_numeric($value)) || trim((string) $value) === '') {
            $errors[$field] = "Le champ $field est obligatoire et doit être une chaîne de caractères.";
            return null;
        }
        return trim((string) $value);
    }

    private static function asDateTime(mixed $value, string $field, array &$errors): ?DateTime {
        if (!is_string($value) || trim($value) === '') {
            $errors[$field] = "Le champ $field est obligatoire et doit être une date-time valide.";
            return null;
        }
        try {
            return new DateTime($value);
        } catch (\Exception $e) {
            $errors[$field] = "Le champ $field doit être une date-time valide.";
            return null;
        }
    }
}
