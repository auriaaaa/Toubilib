<?php

namespace toubilib\adapters\controllers\actions;

use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\usecases\ServicePraticien;

class GetCreneauxOccupesPraticienAction
{
    public function __construct(
        private ServicePraticien $servicePraticien
    ) {}

    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $params = $request->getQueryParams();

        if (!isset($params['debut'], $params['fin'])) {
            $response->getBody()->write(json_encode([
                'erreur' => 'Les paramètres debut et fin sont obligatoires.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $debut = new DateTime($params['debut']);
            $fin = new DateTime($params['fin']);

            $rendezVous = $this->servicePraticien->getCreneauxOccupes(
                $args['id'],
                $debut,
                $fin
            );

            $creneaux = [];

            foreach ($rendezVous as $rdv) {
                $creneaux[] = [
                    'debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'fin' => $rdv->getDateHeureFin()->format('Y-m-d H:i:s')
                ];
            }

            $data = [
                'praticien' => [
                    'id' => $args['id'],
                    '_links' => [
                        'self' => [
                            'href' => '/praticiens/' . $args['id']
                        ],
                        'agenda' => [
                            'href' => '/praticiens/' . $args['id'] . '/agenda'
                        ]
                    ]
                ],
                'periode' => [
                    'debut' => $debut->format('Y-m-d'),
                    'fin' => $fin->format('Y-m-d')
                ],
                'nombre' => count($creneaux),
                'creneaux' => $creneaux,
                '_links' => [
                    'self' => [
                        'href' => $request->getUri()->getPath()
                            . '?debut=' . $debut->format('Y-m-d')
                            . '&fin=' . $fin->format('Y-m-d')
                    ]
                ]
            ];

            $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return $response->withHeader('Content-Type', 'application/json; charset=utf-8');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'erreur' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}