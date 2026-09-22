<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServicePraticienInterface;

class GetAllPraticienAction {
    
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien) {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        try {
            $praticiens_dto = $this->servicePraticien->getAllPraticiens();
        } catch (\Exception $e) {
            throw new HttpNotFoundException($rq, "Aucun praticien trouvé.");
        }

        $rs->getBody()->write(json_encode($praticiens_dto));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

}
