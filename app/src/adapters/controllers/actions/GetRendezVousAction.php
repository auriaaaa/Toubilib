<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        $id = $args['id'];

        $rdv = $this->serviceRendezVous->getRendezVous($id);
        $modifiable = $this->serviceRendezVous->statutModifiable($id);

        $links = [
            'self'      => ['href' => '/rendezvous/' . $rdv->id, 'method' => 'GET'],
            'praticien' => ['href' => '/praticiens/' . $rdv->praticien_id, 'method' => 'GET'],
            'patient'   => ['href' => '/patients/' . $rdv->patient_id, 'method' => 'GET'],
        ];

        if ($modifiable) {
            $links['annuler'] = ['href' =>  '/rendezvous/' . $rdv->id . '/annuler', 'method' => 'POST'];
            $links['honorer'] = ['href' => '/rendezvous/' . $rdv->id . '/honorer', 'method' => 'POST'];
            $links['ignorer'] = ['href' => '/rendezvous/' . $rdv->id . '/ignorer', 'method' => 'POST'];
        }

        $data = [
            'type'       => 'ressource',
            'rendez_vous' => $rdv,
            '_links'      => $links,
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}