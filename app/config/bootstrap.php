<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use toubilib\adapters\config\ContainerConfig;
use toubilib\adapters\controllers\middlewares\Cors;

// Actions 
use toubilib\adapters\controllers\actions\AnnulerRendezVousAction;
use toubilib\adapters\controllers\actions\GetPraticienAction;
use toubilib\adapters\controllers\actions\GetAllPraticienAction;

$dotenv = \Dotenv\Dotenv::createImmutable(
    __DIR__ . '/../../',
    'toubilib.env'
);
$dotenv->load();

$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);
$app->addBodyParsingMiddleware();
$app->add(Cors::class);
$app->addRoutingMiddleware();

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

$app->group('/rdv', function (\Slim\Routing\RouteCollectorProxy $group) {

    // Annuler un rendez-vous
    $group->post('/{id}/annuler[/]', AnnulerRendezVousAction::class )->setName('AnnulerRendezVous');

});

$app->group('/praticiens', function (\Slim\Routing\RouteCollectorProxy $group) {

    // Get
    $group->get('[/]', GetAllPraticienAction::class )->setName('GetAllPraticiens');
    $group->get('/{id}[/]', GetPraticienAction::class )->setName('GetPraticien');

});

$app->options(
    '/{routes:.+}',
    function (Request $request, Response $response): Response {
        return $response;
    }
);

$routeParser = $app->getRouteCollector()->getRouteParser();

return $app;
