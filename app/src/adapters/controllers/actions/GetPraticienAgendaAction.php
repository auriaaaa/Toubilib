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

        if (!isset($params['debut'], $params['fin'])) {
            throw new \InvalidArgumentException(
                'Les paramètres debut et fin sont obligatoires (ex. ?debut=2025-12-01&fin=2025-12-05).'
            );
        }

        try {
            $debut = new DateTime($params['debut']);
            $fin = new DateTime($params['fin']);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Format de date invalide.');
        }

        $rendezVous = $this->servicePraticien->getRendezVousPourPeriode($id, $debut, $fin);

        $data = [
            'praticien' => [
                'id' => $id
            ],
            'periode' => [
                'debut' => $debut->format('d/m/Y'),
                'fin' => $fin->format('d/m/Y')
            ],
            'nombre' => count($rendezVous),
            'rendezVous' => $rendezVous,
            '_links' => [
                'self' => [
                    'href' => $rq->getUri()->getPath()
                        . '?debut=' . urlencode($params['debut'])
                        . '&fin=' . urlencode($params['fin']),
                    'method' => 'GET',
                ],
                'praticien' => [
                    'href' => '/praticiens/' . $id,
                    'method' => 'GET',
                ],
                'creneaux' => [
                    'href' => '/praticiens/' . $id . '/creneaux-occupes',
                    'method' => 'GET',
                ],
            ],
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}