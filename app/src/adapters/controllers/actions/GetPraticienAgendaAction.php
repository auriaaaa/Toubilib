<?php

namespace toubilib\adapters\controllers\actions;

use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\ports\api\ServicePraticienInterface;

class GetPraticienAgendaAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien) {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        $params = $rq->getQueryParams();
        $id = $args['id'];

        if (!isset($params['debut'], $params['fin'])
            || !is_string($params['debut'])
            || !is_string($params['fin'])) {
            throw new \InvalidArgumentException(
                'Les paramètres debut et fin sont obligatoires (ex. ?debut=2026-10-01&fin=2026-10-31).'
            );
        }

        try {
            $debut = new DateTime($params['debut']);
            $fin = new DateTime($params['fin']);
            $fin->setTime(23, 59, 59);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Format de date invalide.');
        }

        $rendezVous = $this->servicePraticien->getRendezVousPourPeriode($id, $debut, $fin);

        $liste = [];

        foreach ($rendezVous as $rdv) {
            $liste[] = [
                'rendez_vous' => $rdv,
                '_links' => [
                    'self'    => ['href' => '/rendezvous/' . $rdv->id, 'method' => 'GET'],
                    'patient' => ['href' => '/patients/' . $rdv->patient['id'], 'method' => 'GET'],
                ],
            ];
        }

        $data = [
            'type' => 'collection',
            'praticien' => ['id' => $id],
            'periode' => [
                'debut' => $debut->format('d/m/Y'),
                'fin'   => $fin->format('d/m/Y'),
            ],
            'nombre' => count($liste),
            'rendez_vous' => $liste,
            '_links' => [
                'self' => [
                    'href' => $rq->getUri()->getPath()
                        . '?debut=' . urlencode($params['debut'])
                        . '&fin=' . urlencode($params['fin']),
                    'method' => 'GET',
                ],
                'praticien' => ['href' => '/praticiens/' . $id, 'method' => 'GET'],
                'creneaux-occupes'  => ['href' => '/praticiens/' . $id . '/creneaux-occupes', 'method' => 'GET'],
            ],
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}