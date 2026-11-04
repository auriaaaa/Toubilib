<?php
use Slim\Factory\AppFactory;
use toubilib\adapters\controllers\middlewares\ApiErrorHandler;
use toubilib\adapters\controllers\middlewares\Cors;

$container = require __DIR__ . '/container.php';
$app = AppFactory::createFromContainer($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$errorMiddleware->setDefaultErrorHandler(new ApiErrorHandler($app->getResponseFactory()));

$app->add(Cors::class);

(require __DIR__ . '/routes.php')($app);

return $app;