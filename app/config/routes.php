<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use toubilib\adapters\controllers\middlewares\CreerRendezVousValidationMiddleware;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetAllPraticienAction;
use toubilib\adapters\controllers\actions\GetCreneauxOccupesPraticienAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetPraticienAgendaAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;

return function (App $app): void {

    // Rendez-vous
    $app->group('/rendezvous', function (RouteCollectorProxy $group) {
        $group->post('[/]', CreerRendezVousAction::class)
            ->add(CreerRendezVousValidationMiddleware::class);       
        $group->get('/{id}[/]', GetRendezVousAction::class);       
        $group->post('/{id}/annuler[/]', AnnulerRendezVousAction::class);
        $group->post('/{id}/honorer[/]', HonorerRendezVousAction::class);
        $group->post('/{id}/ignorer[/]', IgnorerRendezVousAction::class);
    });

    // Praticiens
    $app->group('/praticiens', function (RouteCollectorProxy $group) {
        $group->get('[/]', GetAllPraticienAction::class);        // recherche avec ?specialite=&ville=
        $group->get('/{id}[/]', GetPraticienAction::class);          
        $group->get('/{id}/agenda[/]', GetPraticienAgendaAction::class);                     // ?debut=&fin=
        $group->get('/{id}/creneaux-occupes[/]', GetCreneauxOccupesPraticienAction::class);  // ?debut=&fin=
    });

    // Patients
    $app->group('/patients', function (RouteCollectorProxy $group) {
        $group->get('[/]', \toubilib\adapters\controllers\actions\GetAllPatientAction::class);  
        $group->get('/{id}[/]', \toubilib\adapters\controllers\actions\GetPatientAction::class);   
        $group->get('/{id}/rendezvous[/]',\toubilib\adapters\controllers\actions\GetRendezVousPatientAction::class);
    });

    $app->options('/{routes:.+}', function (Request $request, Response $response): Response {
        return $response;
    });
};