<?php

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use toubilib\application\ports\api\ServiceRendezVousInterface;

class CreerRendezVousAction
{
    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous)
    {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $dto = $rq->getAttribute('creerRdvDto');

        $rdv = $this->serviceRendezVous->creer($dto);

        $rs->getBody()->write(json_encode($rdv));

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Location', '/rendezvous/' . $rdv->id)
            ->withStatus(201);
    }
}