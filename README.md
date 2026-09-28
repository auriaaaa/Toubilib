# Toubilib - Auriane GUYOT

Backend d'une application de gestion de rendez-vous médicaux, exposé sous forme d'API RESTful.
Projet réalisé dans le cadre du BUT Informatique S5, IUT Nancy-Charlemagne.

## Lancement

Prérequis : Docker et Docker Compose.

1. Cloner le dépôt git (https://github.com/auriaaaa/Toubilib.git)
2. Créer et compléter les fichiers `app/config/.env` et `toubilib.env`
3. Démarrer les services
   ```bash
   docker compose up -d
   ```

L'API est ensuite accessible sur `http://localhost:7080`.

## Fonctionnalités implémentées

| # | Fonctionnalité |
|---|----------------|
| 1 | Lister les praticiens |
| 2 | Consulter les détails d’un praticien | 
| 3 | Consulter les détails d’un RDV |
| 4 | Consulter l’agenda d’un praticien pour une période donnée | 
| 5 | Créer un rendez-vous auprès d’un praticien à une date/heure donnée en précisant le motif de
visite |
| 6 | Annuler un rendez-vous |
| 7 | Lister les créneaux de RDV déjà occupés pour un praticien sur une période donnée (date de
début, date de fin) |

## Utilisation de l'API

### Routes

```
POST /rdv/{id}/annuler
GET /rdv/{id}

GET /praticiens
GET /praticiens/{id}
```

Exemple d'annulation d'un rendez-vous avec curl (sous Windows, utiliser `curl.exe`) :

```bash
curl.exe -X POST http://localhost:7080/rdv/<id-rdv>/annuler -i
```

## Tests

Les tests sont écrits avec Pest et utilisent un repository en mémoire.

```bash
docker compose exec api.toubilib vendor/bin/pest <chemin-vers-le-fichier-de-test>
```
