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

        try {
            $praticiens_dto = $this->servicePraticien
                ->rechercherPraticiens($specialite, $ville);

        } catch (\InvalidArgumentException $e) {
            throw new HttpBadRequestException($rq, $e->getMessage());

        } catch (\Exception $e) {
            throw new HttpNotFoundException($rq, "Impossible de récupérer les praticiens.");
        }

        $praticiens = [];

        foreach ($praticiens_dto as $praticien) {
            $id = $praticien->getId();

            $praticiens[] = [
                'praticien' => [
                    'id' => $praticien->getId(),
                    'nom' => $praticien->getNom(),
                    'prenom' => $praticien->getPrenom(),
                    'ville' => $praticien->getVille(),
                    'email' => $praticien->getEmail(),
                    'telephone' => $praticien->getTelephone(),
                    'specialite_id' => $praticien->getSpecialiteId(),
                ],
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
