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

        $praticiens = [];

        foreach ($praticiens_dto as $praticien) {
            $id = $praticien->id;

            $praticiens[] = [
                'praticien' => $praticien,
                '_links' => [
                    'self' => [
                        'href' => '/praticiens/' . $id,
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
        }

        $data = [
            'nombre' => count($praticiens),
            'praticiens' => $praticiens,
            '_links' => [
                'self' => [
                    'href' => '/praticiens',
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
