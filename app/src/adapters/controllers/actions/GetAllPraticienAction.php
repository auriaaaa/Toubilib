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

        $params = $rq->getQueryParams();

        $specialite = $params['specialite'] ?? null;
        $ville = $params['ville'] ?? null;

        if ($specialite !== null && !is_string($specialite)) {
            throw new \InvalidArgumentException('Le paramètre specialite doit être une chaîne de caractères.');
        }

        if ($ville !== null && !is_string($ville)) {
            throw new \InvalidArgumentException('Le paramètre ville doit être une chaîne de caractères.');
        }

        $praticiens_dto = $this->servicePraticien->rechercherPraticiens($specialite, $ville);

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

        $query = [];

        if ($specialite !== null && $specialite !== '') {
            $query['specialite'] = $specialite;
        }

        if ($ville !== null && $ville !== '') {
            $query['ville'] = $ville;
        }

        $selfHref = '/praticiens';

        if (!empty($query)) {
            $selfHref .= '?' . http_build_query($query);
        }

        $data = [
            'nombre' => count($praticiens),
            'criteres' => [
                'specialite' => $specialite,
                'ville' => $ville
            ],
            'praticiens' => $praticiens,
            '_links' => [
                'self' => [
                    'href' => $selfHref,
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
