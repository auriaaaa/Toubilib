# Analyse et architecture pour le projet Toubilib

## Diagramme de classe

> Voir le fichier diagramme.png


**État d'un rdv :**
- 0 : créé (par défaut)
- 1 : annulé
- 2 : honoré
- 3 : ignoré


## Annulation d'un RDV

1. Comment peut-on l’implanter dans le domaine ?

On ajoute une méthode annuler() dans l'entité RendezVous (pour conserver la logique métier dans le domaine). 
La méthode vérifie que le rdv est annulable (gestion des erreurs), si oui elle change le status à 1, sinon elle lève une exception métier.


2. Quelles erreurs peuvent être détectées lors de l’exécution de cette fonctionnalité ?

- Le rendez-vous est inexistant (id invalide) -> erreur 404
- Le rendez-vous est déjà passé (date de début antérieur à la date du jour)
- Le rendez-vous a un status incompatible avec l'annulation (2 ou 3)


3. Quel composant retrouve l’entité correspondant au RDV à annuler ?

C'est l'adaptateur de persistance qui retrouve l'entité en : exécutant la requête SQL et reconstruit l'entité rdv du domaine à partir des données stockées.


4. Quel composant sauvegarde ce RDV dans la base de données après sa modification.

Après l'appel de la fonction annuler(), le service appelle la méthode de mise à jour de l'adaptateur de persitance pour sauvegarder l'entité en mémoire.


5. Proposer un port d’entrée correspondant à ce cas d’utilisation, et un port de sortie
permettant d’interagir avec la persistance lors de la réalisation de ce cas d’utilisation (accès
au RDV concerné, mise à jour après confirmation.)

**Port d'entrée**

Interface implémentée par le service applicatif

```php
interface AnnulerRendezVousUseCase {
    public function cancel(string $rdvId, string $auteurId): void;
}
```

**Port de sortie**

Interface implementée par l'adaptateur de persistance 

```php
interface RendezVousRepositoryInterface {
    public function findById(string $id): RendezVous;
    public function save(RendezVous $rdv): void;      
}
```
