<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServicePatientInterface;

class GetPatientAction {
    
    private ServicePatientInterface $servicePatient;

    public function __construct(ServicePatientInterface $servicePatient) {
        $this->servicePatient = $servicePatient;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        $id = $args['id'];

        $patient_dto = $this->servicePatient->getPatient($id);

        $data = [
            'patient' => $patient_dto,
            '_links' => [
                'self' => [
                    'href' => '/patients/' . $id,
                    'method' => 'GET'
                ],
                'all' => [
                    'href' => '/patients',
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
