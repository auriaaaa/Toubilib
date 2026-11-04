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
            'self'      => ['href' => '/rdvs/' . $rdv->id],
            'praticien' => ['href' => '/praticiens/' . $rdv->praticien_id],
            'patient'   => ['href' => '/patients/' . $rdv->patient_id],
        ];

        if ($modifiable) {
            $links['annuler'] = ['href' => '/rdvs/' . $rdv->id . '/annuler'];
            $links['honorer'] = ['href' => '/rdvs/' . $rdv->id . '/honorer'];
            $links['ignorer'] = ['href' => '/rdvs/' . $rdv->id . '/ignorer'];
        }

        $data = [
            'type'       => 'ressource',
            'rendez_vous' => $rdv,
            'links'      => $links,
        ];

        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}