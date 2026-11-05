<?php

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use toubilib\application\dto\CreerRendezVousDTO;

class CreerRendezVousValidationMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $donnees = $request->getParsedBody();

        if (!is_array($donnees)) {
            throw new \InvalidArgumentException('Le corps de la requête doit être un objet JSON valide.');
        }

        $dto = CreerRendezVousDTO::fromArray($donnees);

        return $handler->handle($request->withAttribute('creerRdvDto', $dto));
    }
}