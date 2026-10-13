<?php

namespace toubilib\application\dto;

use DateTime;
use toubilib\application\exceptions\ValidationException;

final class CreateRendezVousDTO {

    private const LONGUEUR_MAX = 64;
    private const ID_REGEX = '/^[A-Za-z0-9_-]+$/';
    private const MOTIF_REGEX = '/^[A-Za-z0-9]{1,10}$/';

    private const FORMATS_DATE = [
        'Y-m-d\TH:i:sp',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
    ];

    private function __construct(
        public readonly string $praticien_id,
        public readonly string $patient_id,
        public readonly DateTime $date_heure_debut,
        public readonly string $motif_visite,
    ) {}

    public static function fromArray(array $data): self {
        $errors = [];

        $praticien_id     = self::lireIdentifiant($data, 'praticien_id', $errors);
        $patient_id       = self::lireIdentifiant($data, 'patient_id', $errors);
        $date_heure_debut = self::lireDate($data, 'date_heure_debut', $errors);
        $motif_visite     = self::lireMotif($data, 'motif_visite', $errors);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return new self($praticien_id, $patient_id, $date_heure_debut, $motif_visite);
    }


    private static function lireTexte(array $data, string $champ, array &$errors): ?string {
        // Existence
        if (!array_key_exists($champ, $data) || $data[$champ] === null) {
            $errors[$champ] = "Le champ $champ est obligatoire.";
            return null;
        }

        // Type string
        if (!is_string($data[$champ])) {
            $errors[$champ] = "Le champ $champ doit être une chaîne de caractères.";
            return null;
        }

        $valeur = trim($data[$champ]);

        if ($valeur === '') {
            $errors[$champ] = "Le champ $champ est obligatoire.";
            return null;
        }

        // Taille maximale
        if (strlen($valeur) > self::LONGUEUR_MAX) {
            $errors[$champ] = "Le champ $champ est trop long (" . self::LONGUEUR_MAX . " caractères maximum).";
            return null;
        }

        return $valeur;
    }

    private static function lireIdentifiant(array $data, string $champ, array &$errors): ?string {
        $valeur = self::lireTexte($data, $champ, $errors);
        if ($valeur === null) {
            return null;
        }

        if (preg_match(self::ID_REGEX, $valeur) !== 1) {
            $errors[$champ] = "Le champ $champ ne peut contenir que des lettres, des chiffres, des tirets et des underscores.";
            return null;
        }

        return $valeur;
    }

    private static function lireMotif(array $data, string $champ, array &$errors): ?string {
        $valeur = self::lireTexte($data, $champ, $errors);
        if ($valeur === null) {
            return null;
        }

        if (preg_match(self::MOTIF_REGEX, $valeur) !== 1) {
            $errors[$champ] = "Le champ $champ doit contenir de 1 à 10 lettres ou chiffres.";
            return null;
        }

        return $valeur;
    }

    private static function lireDate(array $data, string $champ, array &$errors): ?DateTime {
        $valeur = self::lireTexte($data, $champ, $errors);
        if ($valeur === null) {
            return null;
        }

        foreach (self::FORMATS_DATE as $format) {
            $date = DateTime::createFromFormat('!' . $format, $valeur);
            $problemes = DateTime::getLastErrors();

            $valide = $date !== false
                && ($problemes === false || ($problemes['warning_count'] === 0 && $problemes['error_count'] === 0));

            if ($valide) {
                return $date;
            }
        }

        $errors[$champ] = "Le champ $champ doit être une date-heure valide (ex. 2030-01-07T10:00:00).";
        return null;
    }
}