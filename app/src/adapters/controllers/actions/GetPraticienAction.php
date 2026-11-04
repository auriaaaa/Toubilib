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

        $id = $args['id'];

        $praticien_dto = $this->servicePraticien->getPraticien($id);

        $data = [
            'praticien' => $praticien_dto,
            '_links' => [
                'self' => [
                    'href' => '/praticiens/' . $id,
                    'method' => 'GET'
                ],
                'all' => [
                    'href' => '/praticiens',
                    'method' => 'GET'
                ],
                'agenda' => [
                    'href' => '/praticiens/' . $id . '/agenda',
                    'method' => 'GET'
                ],
                'creneaux' => [
                    'href' => '/praticiens/' . $id . '/creneaux-occupes',
                    'method' => 'GET'
                ]
            ]
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

}
