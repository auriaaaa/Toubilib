<?php
namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use toubilib\application\ports\api\ServiceRendezVousInterface;
use toubilib\domain\exceptions\RendezVousDejaPasseException;
use toubilib\domain\exceptions\StatutRendezVousInvalideException;

class AnnulerRendezVousAction {
    
    private ServiceRendezVousInterface $serviceRendezVous;

    public function __construct(ServiceRendezVousInterface $serviceRendezVous) {
        $this->serviceRendezVous = $serviceRendezVous;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $dto = $this->serviceRendezVous->annuler($args['id']);

        $rs->getBody()->write(json_encode($dto));

        return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

}
