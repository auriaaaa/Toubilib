<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServiceRendezVousInterface;

class GetRendezVousAction {
    
    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous) {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {

        $id = $args['id'] ?? null;

        if ($id === null || $id === '') {
            throw new HttpBadRequestException($rq, "L'ID du rendez-vous est requis.");
        }

        if (!is_string($id)) {
            throw new HttpBadRequestException($rq, "L'ID du rendez-vous doit être une chaîne de caractères.");
        }

        try {
            $rendezVous_dto = $this->serviceRendezVous->getRendezVous($id);
            $est_modifiable = $this->serviceRendezVous->statutModifiable($id);
        } catch (\Exception $e) {
            throw new HttpNotFoundException($rq, "Rendez-vous non trouvé.");
        }

        $data = [
            'rendezVous' => $rendezVous_dto,
            '_links' => [
                'self' => [
                    'href' => '/rendezvous/' . $id,
                    'method' => 'GET'
                ]
            ]
        ];

        if ($est_modifiable) {
            $data['_links']['annuler'] = [
                'href' => '/rendezvous/' . $id . '/annuler',
                'method' => 'POST'
            ];

            $data['_links']['honorer'] = [
                'href' => '/rendezvous/' . $id . '/honorer',
                'method' => 'POST'
            ];

            $data['_links']['ignorer'] = [
                'href' => '/rendezvous/' . $id . '/ignorer',
                'method' => 'POST'
            ];
        }

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

}
