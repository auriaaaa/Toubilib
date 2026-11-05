# Analyse et architecture pour le projet Toubilib

## Diagramme de classe

> Voir le fichier diagramme.jpeg

**État d'un rdv :**
- 0 : prévu (par défaut)
- 1 : annulé
- 2 : honoré
- 3 : ignoré

## Annulation d'un RDV (fonctionnalité 6)

### 1. Comment peut-on l'implanter dans le domaine ?

On ajoute une méthode `annuler()` dans l'entité `RendezVous`, pour garder la logique métier dans le domaine.
La méthode vérifie que le rdv est annulable. Si c'est le cas, elle passe le statut à 1 ; sinon elle lève une exception métier.

Règles vérifiées, dans cet ordre :
1. la date de début ne doit pas être passée ;
2. le statut doit être 0 (prévu) : un rdv déjà annulé, honoré ou ignoré ne peut plus être annulé.

### 2. Quelles erreurs peuvent être détectées lors de l'exécution de cette fonctionnalité ?

| Erreur | Exception | Statut HTTP |
|---|---|---|
| Le rendez-vous n'existe pas (id invalide) | `RendezVousNotFoundException` | 404 |
| Le rendez-vous est déjà passé (date de début antérieure à maintenant) | `RendezVousDejaPasseException` | 400 |
| Le rendez-vous est déjà annulé (statut 1) | `StatutRendezVousInvalideException` | 400 |
| Le rendez-vous est déjà honoré ou ignoré (statut 2 ou 3) | `StatutRendezVousInvalideException` | 400 |
| Erreur technique (base de données indisponible...) | `PDOException` | 500 |

Ces exceptions ne sont pas attrapées dans le service ni dans l'action : elles remontent jusqu'au error handler `ApiErrorHandler` (adaptateur d'entrée) qui les convertit en réponse HTTP JSON.

### 3. Quel composant retrouve l'entité correspondant au RDV à annuler ?

C'est l'adaptateur de persistance `RendezVousRepository` (implémentation de `RendezVousRepositoryInterface`).
Sa méthode `findById()` exécute la requête SQL avec PDO, reconstruit l'entité `RendezVous` du domaine à partir des données stockées, et lève `RendezVousNotFoundException` si aucun rendez-vous ne correspond.

### 4. Quel composant sauvegarde ce RDV dans la base de données après sa modification ?

Après l'appel de `annuler()` sur l'entité, le service applicatif `ServiceRendezVous` appelle la méthode `save()` du repository (via `RendezVousRepositoryInterface`).
`RendezVousRepository::save()` écrit l'état de l'entité en base (insertion, ou mise à jour si l'identifiant existe déjà).

Le service ne supprime pas l'entité : il la charge, appelle `annuler()` pour modifier son statut, sauvegarde, puis retourne un `RendezVousDTO`.

### 5. Proposer un port d'entrée et un port de sortie

**Port d'entrée** (`application\ports\api`)

Interface implémentée par le service applicatif `ServiceRendezVous`. Elle retourne un `RendezVousDTO`.

```php
interface ServiceRendezVousInterface {
    public function annuler(string $id): RendezVousDTO;
    // getRendezVous(), creer(), honorer(), ignorer()...
}
```

**Port de sortie** (`application\ports\spi`)

Interface implémentée par l'adaptateur de persistance `RendezVousRepository` (`adapters\persistence`).

```php
interface RendezVousRepositoryInterface {
    public function findById(string $id): RendezVous;   // accès au RDV concerné
    public function save(RendezVous $rendezVous): void; // mise à jour après modification
}
```

Le noyau applicatif ne dépend que de cette interface. Le service reçoit le repository par injection de dépendances (PHP-DI) et ne connaît ni PDO ni la base de données.

### Déroulement complet

```
POST /rdvs/{id}/annuler
  -> AnnulerRendezVousAction                (adaptateur d'entrée)
  -> ServiceRendezVousInterface::annuler()  (port d'entrée)
  -> ServiceRendezVous                      (service applicatif)
       -> RendezVousRepositoryInterface::findById()   (port de sortie)
       -> RendezVous::annuler()                        (domaine)
       -> RendezVousRepositoryInterface::save()        (port de sortie)
  <- RendezVousDTO -> réponse JSON 200
```