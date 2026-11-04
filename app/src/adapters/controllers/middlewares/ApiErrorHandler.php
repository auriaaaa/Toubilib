<?php

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;
use toubilib\application\exceptions\ValidationException;
use toubilib\domain\exceptions\EntityNotFoundException;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\RendezVousPasPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

final class ApiErrorHandler
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $e,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {

        $body = [];

        if ($e instanceof HttpException) {
            $status = $e->getCode();
            $body['message'] = $e->getMessage();

        } elseif ($e instanceof ValidationException) {
            $status = 400;
            $body['message'] = 'Données invalides.';
            $body['errors'] = $e->getErrors();

        } elseif ($e instanceof EntityNotFoundException) {
            $status = 404;
            $body['message'] = $e->getMessage();

        } elseif ($e instanceof RendezVousDejaPasseException
            || $e instanceof RendezVousPasPasseException
            || $e instanceof StatutRendezVousInvalideException
            || $e instanceof PraticienIndisponibleException) {
            $status = 400;
            $body['message'] = $e->getMessage();

        } elseif ($e instanceof \InvalidArgumentException) {
            $status = 400;
            $body['message'] = $e->getMessage();

        } else {
            // Erreur imprévue en log
            error_log((string) $e);
            $status = 500;
            $body['message'] = 'Erreur interne du serveur.';
        }

        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withHeader('Content-Type', 'application/json');
    }
}