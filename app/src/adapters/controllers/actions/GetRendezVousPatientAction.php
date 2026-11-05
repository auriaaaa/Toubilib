<?php

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\ports\api\ServicePatientInterface;

class GetRendezVousPatientAction
{
    private ServicePatientInterface $servicePatient;

    public function __construct(ServicePatientInterface $servicePatient)
    {
        $this->servicePatient = $servicePatient;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $id = $args['id'];

        $rendezvous = $this->servicePatient->getHistoriqueRendezVous($id);

        $liste = [];

        foreach ($rendezvous as $rdv) {
            $liste[] = [
                'rendez_vous' => $rdv,
                '_links' => [
                    'self' => ['href' => '/rendezvous/' . $rdv->id],
                ],
            ];
        }

        $data = [
            'type'   => 'collection',
            'patient' => ['id' => $id],
            'nombre' => count($liste),
            'rendez_vous' => $liste,
            '_links'  => [
                'self'    => ['href' => '/patients/' . $id . '/rendezvous'],
                'patient' => ['href' => '/patients/' . $id],
                'all'     => ['href' => '/patients'],
            ],
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}