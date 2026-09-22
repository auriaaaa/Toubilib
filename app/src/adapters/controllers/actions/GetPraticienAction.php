<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServicePraticienInterface;

class GetPraticienAction {
    
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien) {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        $id = $args['id'] ?? null;

        if ($id === null || $id === '') {
            throw new HttpBadRequestException($rq, "L'ID du praticien est requis.");
        }

        if (!is_string($id)) {
            throw new HttpBadRequestException($rq, "L'ID du praticien doit être une chaîne de caractères.");
        }

        try {
            $praticien_dto = $this->servicePraticien->getPraticien($id);
        } catch (\Exception $e) {
            throw new HttpNotFoundException($rq, "Praticien non trouvé.");
        }

        $rs->getBody()->write(json_encode($praticien_dto));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

}
