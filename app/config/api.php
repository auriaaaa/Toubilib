<?php

use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetAllPraticienAction;
use toubilib\adapters\controllers\actions\GetCreneauxOccupesPraticienAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetPraticienAgendaAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\middlewares\Cors;
use toubilib\application\ports\api\ServicePraticienInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

return [

    // Middleware
    Cors::class => \DI\create(Cors::class),

    // Actions qui utilisent le service  rendez-vous
    CreerRendezVousAction::class => \DI\create(CreerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    GetRendezVousAction::class => \DI\create(GetRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    AnnulerRendezVousAction::class => \DI\create(AnnulerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    HonorerRendezVousAction::class => \DI\create(HonorerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    IgnorerRendezVousAction::class => \DI\create(IgnorerRendezVousAction::class)
        ->constructor(\DI\get(ServiceRendezVousInterface::class)),

    // Actions qui utilisent le service des praticiens
    GetAllPraticienAction::class => \DI\create(GetAllPraticienAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

    GetPraticienAction::class => \DI\create(GetPraticienAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

    GetPraticienAgendaAction::class => \DI\create(GetPraticienAgendaAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),

    GetCreneauxOccupesPraticienAction::class => \DI\create(GetCreneauxOccupesPraticienAction::class)
        ->constructor(\DI\get(ServicePraticienInterface::class)),
];