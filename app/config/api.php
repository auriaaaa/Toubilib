<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\adapters\controllers\middlewares\Cors;

use toubilib\adapters\controllers\actions\CreerRendezVousAction;
use toubilib\adapters\controllers\actions\GetRendezVousAction;
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\HonorerRendezVousAction;
use toubilib\adapters\controllers\actions\IgnorerRendezVousAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetAllPraticienAction;
use toubilib\adapters\controllers\actions\GetCreneauxOccupesPraticienAction;
use toubilib\adapters\controllers\actions\GetPraticienAgendaAction;

$settings = require __DIR__ . '/settings.php';
$container = require __DIR__ . '/services.php';

// Initialisation de Slim
$app = AppFactory::createFromContainer($container);

// Middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Gestion des erreurs
$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$errorHandler->registerErrorRenderer(
    'application/json',
    function ($exception, $displayErrorDetails) {
        return json_encode([
            'message' => $exception->getMessage()
        ]);
    }
);

// CORS
$app->add(Cors::class);

// Routes des rendez-vous.
$app->group('/rendezvous', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->post('[/]', CreerRendezVousAction::class);
    $group->post('/{id}/annuler[/]', AnnulerRendezVousAction::class);
    $group->post('/{id}/honorer[/]', HonorerRendezVousAction::class);
    $group->post('/{id}/ignorer[/]', IgnorerRendezVousAction::class);
    $group->get('/{id}[/]', GetRendezVousAction::class);
});

// Routes des praticiens.
$app->group('/praticiens', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->get('[/]', GetAllPraticienAction::class);
    $group->get('/{id}[/]', GetPraticienAction::class);
    $group->get('/{id}/agenda[/]', GetPraticienAgendaAction::class);
    $group->get('/{id}/creneaux-occupes', GetCreneauxOccupesPraticienAction::class);
});

$app->options(
    '/{routes:.+}',
    function (Request $request, Response $response): Response {
        return $response;
    }
);

$routeParser = $app->getRouteCollector()->getRouteParser();

return $app;
