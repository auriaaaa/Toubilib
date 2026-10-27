<?php

namespace toubilib\adapters\controllers\actions;

use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

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

        if (!isset($params['debut'], $params['fin'])) {
            $response->getBody()->write(json_encode([
                'erreur' => 'Les paramètres debut et fin sont obligatoires. Format attendu : .../creneaux-occupes?debut=2025-10-13&fin=2026-10-20'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $debut = new DateTime($params['debut']);
            $fin = new DateTime($params['fin']);

            $rendezVous = $this->servicePraticien->getRendezVousPourPeriode($args['id'], $debut, $fin);

        } catch (\InvalidArgumentException $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());

        } catch (\Exception $e) {
            throw new HttpNotFoundException($rq, "Impossible de récupérer l'agenda de ce praticien.");
        }

        $agenda = [];

        foreach ($rendezVous as $rdv) {
            $agenda[] = [
                'id' => $rdv->getId(),
                'debut' => $rdv->getDateHeureDebut()->format('d/m/Y à H:i'),
                'fin' => $rdv->getDateHeureFin()->format('d/m/Y à H:i'),
                'patient' => [
                    'id' => $rdv->getPatientId(),
                    'nom' => $rdv->getPatientNom(),
                    'prenom' => $rdv->getPatientPrenom()
                ],
                'motif' => $rdv->getMotifVisite(),
                'statut' => $rdv->getStatut()
            ];
        }

        $id = $args['id'];

        $data = [
            'praticien' => [
                'id' => $id
            ],
            'periode' => [
                'debut' => $debut->format('d/m/Y'),
                'fin' => $fin->format('d/m/Y')
            ],
            'nombre' => count($agenda),
            'rendezVous' => $agenda,
            '_links' => [
                'self' => [
                    'href' => $rq->getUri()->getPath()
                        . '?debut=' . urlencode($params['debut'])
                        . '&fin=' . urlencode($params['fin']),
                    'method' => 'GET'
                ],
                'praticien' => [
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

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}