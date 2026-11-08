# Toubilib - Auriane GUYOT

Backend d'une application de gestion de rendez-vous médicaux, exposé sous forme d'API RESTful.
Projet réalisé dans le cadre du BUT Informatique S5, IUT Nancy-Charlemagne.

Les patients prennent rendez-vous auprès de praticiens. Chaque rendez-vous peut être créé,
annulé, honoré ou ignoré, et n'est jamais supprimé.

- Dépôt : `https://github.com/auriaaaa/Toubilib.git`

---

## Lancer le projet

### Prérequis

- Docker et Docker Compose
- Git

### Installation

```bash
git clone https://github.com/auriaaaa/Toubilib.git
cd Toubilib

# 1. Créer le fichier d'environnement à partir du modèle
cp app/config/.env.dist app/config/.env

# 2. Construire et démarrer tous les services
docker compose up --build
```


| Service | Adresse |
|---|---|
| API | http://localhost:7080 |
| Adminer | http://localhost:8880 (système **PostgreSQL**, serveur `toubilib.db`, identifiants du `.env`) |
| PostgreSQL (depuis la machine hôte) | `localhost:5532` |

---

      
## Base de données

Tables : `specialite`, `praticien`, `patient`, `rdv`.
Les scripts de `sql/` créent le schéma et insèrent un jeu de données de test :
**50 patients, 20 praticiens, 200 rendez-vous** (de août 2026 à janvier 2027).

Les identifiants des patients, praticiens et rendez-vous sont générés avec `uniqid()` et ceux des spécialités sont des entiers.

### Réinitialiser la base

Les scripts du dossier `sql/` ne sont exécutés **qu'au premier démarrage**, quand le volume de données
est vide. Pour recharger le schéma et les données :

```bash
docker compose down -v
docker compose up --build
```

### Statuts d'un rendez-vous

| Valeur | État | Signification |
|---|---|---|
| 0 | `PREVU` | créé (par défaut) |
| 1 | `ANNULE` | annulé |
| 2 | `HONORE` | le patient s'est présenté |
| 3 | `IGNORE` | le patient ne s'est pas présenté |

### Motifs de visite

| Code | Motif | Durée |
|---|---|---|
| `CI` | Consultation initiale | 30 min |
| `CO` | Consultation | 20 min |
| `CS` | Consultation de suivi | 15 min |


---

## Tableau de bord

### Fonctionnalités

| N° | Fonctionnalité | État |
|---|---|---|
| 1 | Lister les praticiens | Fait |
| 2 | Consulter les détails d'un praticien | Fait |
| 3 | Consulter les détails d'un rendez-vous | Fait |
| 4 | Consulter l'agenda d'un praticien | Fait |
| 5 | Créer un rendez-vous | Fait |
| 6 | Annuler un rendez-vous | Fait |
| 7 | Lister les créneaux occupés d'un praticien | Fait |
| 8 | Rechercher un praticien (spécialité, ville) | Fait |
| 9 | Gérer le cycle de vie (honoré, ignoré) | Fait |
| 10 | Historique des consultations d'un patient | Fait |
| 11 | Disponibilités d'un praticien pour un motif sur une journée | Non faite |
| 12 | Indisponibilités temporaires d'un praticien | Non faite |


---

## API

URL de base : `http://localhost:7080`. Les données sont échangées en JSON.
Les réponses incluent des liens HATEOAS.

### Routes

| Méthode | URL | Description |
|---|---|---|
| GET | `/praticiens` | Lister les praticiens (filtres : `?specialite=<id>&ville=<ville>`) | 
| GET | `/praticiens/{id}` | Détail d'un praticien (spécialité, contacts, ville) | 
| GET | `/praticiens/{id}/agenda?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | Agenda sur une période | 
| GET | `/praticiens/{id}/creneaux-occupes?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | Créneaux occupés sur une période |
| GET | `/patients` | Lister les patients | 
| GET | `/patients/{id}` | Détail d'un patient | 
| GET | `/patients/{id}/rendezvous` | Historique des rendez-vous d'un patient |
| GET | `/rendezvous/{id}` | Détail d'un rendez-vous | 
| POST | `/rendezvous` | Créer un rendez-vous | 
| POST | `/rendezvous/{id}/annuler` | Annuler un rendez-vous | 
| POST | `/rendezvous/{id}/honorer` | Marquer un rendez-vous comme honoré | 
| POST | `/rendezvous/{id}/ignorer` | Marquer un rendez-vous comme ignoré | 

Pour l'agenda et les créneaux occupés, la date de fin est **inclusive** : `fin=2026-12-04` couvre toute la
journée du 4 décembre.

### Corps de `POST /rendezvous`

| Champ | Type | Obligatoire | Règle |
|---|---|---|---|
| `praticien_id` | chaîne | oui | lettres, chiffres, `_` et `-`, 64 caractères maximum |
| `patient_id` | chaîne | oui | idem |
| `date_heure_debut` | chaîne | oui | `AAAA-MM-JJTHH:MM[:SS]` ou `AAAA-MM-JJ HH:MM[:SS]` |
| `motif_visite` | chaîne | oui | `CI`, `CO` ou `CS` |

Les champs inattendus (par exemple `statut`) sont ignorés.

## Tests

Les tests utilisent **Pest** et sont dans `app/tests/pest/` :

| Dossier | Contenu |
|---|---|
| `domaine/` | entités `Praticien` et `RendezVous` (annulation, motifs, durées, disponibilité, chevauchements) |
| `services/` | services applicatifs, avec des repositories en mémoire |
| `validators/` | validation de la création d'un rendez-vous |
| `fakes/` | repositories en mémoire utilisés par les tests |

Le lancement des tests se fait à la racine du projet avec la commande suivante :

```bash
docker compose exec api.toubilib vendor/bin/pest
```
